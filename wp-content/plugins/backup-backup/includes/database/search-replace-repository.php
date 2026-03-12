<?php

namespace BMI\Plugin\Database;

require_once BMI_INCLUDES . '/database/interface-search-replace-repository.php';

/**
 * Class SearchReplaceRepository
 *
 * Handles database interactions for the search and replace functionality.
 * Supports both direct MySQLi connection and fallback to global $wpdb.
 */
class SearchReplaceRepository implements SearchReplaceRepositoryInterface
{
    /**
     * @var \mysqli|null The MySQLi connection instance.
     */
    private $mysqli = null;

    /**
     * @var bool Whether to use the MySQLi connection.
     */
    private $useMysqli = false;

    /**
     * @var string|null The last error message.
     */
    private $lastError = null;

    /**
     * Constructor.
     *
     * Initializes the database connection.
     */
    public function __construct()
    {
        $this->connect();
    }

    /**
     * Establishes a connection to the database.
     *
     * Tries to connect using MySQLi with credentials from wp-config.php.
     * Falls back to $wpdb if MySQLi connection fails or extension is missing.
     */
    private function connect()
    {
        if (!$this->canUseMysqli()) {
            return;
        }

        $connectionParams = $this->parseHostString(DB_HOST);

        try {
            $this->mysqli = new \mysqli(
                $connectionParams['host'],
                DB_USER,
                DB_PASSWORD,
                DB_NAME,
                $connectionParams['port'],
                $connectionParams['socket']
            );

            if ($this->mysqli->connect_errno) {
                $this->useMysqli = false;
                $this->mysqli = null;
                return;
            }

            $this->useMysqli = true;
            $this->setCharset();
        } catch (\Exception $e) {
            $this->useMysqli = false;
            $this->mysqli = null;
        }
    }

    /**
     * Checks if MySQLi extension can be used.
     *
     * @return bool True if MySQLi is available and required constants are defined.
     */
    private function canUseMysqli()
    {
        return extension_loaded('mysqli')
            && defined('DB_HOST')
            && defined('DB_USER')
            && defined('DB_PASSWORD')
            && defined('DB_NAME');
    }

    /**
     * Parses the host string to extract host, port, and socket.
     *
     * @param string $hostString The host string from DB_HOST.
     * @return array{host: string, port: int|null, socket: string|null}
     */
    private function parseHostString($hostString)
    {
        $host = $hostString;
        $port = null;
        $socket = null;

        if (strpos($hostString, ':') !== false) {
            $parts = explode(':', $hostString, 2);
            $host = $parts[0];
            $portOrSocket = $parts[1];

            if (is_numeric($portOrSocket)) {
                $port = (int) $portOrSocket;
            } else {
                $socket = $portOrSocket;
            }
        }

        return [
            'host' => $host,
            'port' => $port,
            'socket' => $socket
        ];
    }

    /**
     * Sets the character set for the MySQLi connection.
     */
    private function setCharset()
    {
        global $wpdb;
        if (!empty($wpdb->charset) && $this->mysqli !== null) {
            $this->mysqli->set_charset($wpdb->charset);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function escape($string)
    {
        if ($this->useMysqli && $this->mysqli !== null) {
            return $this->mysqli->real_escape_string($string);
        }

        global $wpdb;
        return $wpdb->_real_escape($string);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Retrieves the total row count of a table.
     *
     * {@inheritdoc}
     */
    public function getTableRowCount($table, $primaryKey, $whereClause = '')
    {
        $escapedTable = $this->escapeIdentifier($table);

        // Try with PRIMARY index hint first
        if ($primaryKey !== false) {
            $result = $this->tryCountWithIndex($escapedTable, 'PRIMARY', $whereClause);
            if ($result !== null) {
                return $result;
            }

            // Try with primary key column name as index
            $result = $this->tryCountWithIndex($escapedTable, $primaryKey, $whereClause);
            if ($result !== null) {
                return $result;
            }
        }

        // Fallback: count without index hint
        return $this->executeCount($escapedTable, '', $whereClause);
    }

    /**
     * Tries to count rows using a specific index hint.
     *
     * @param string $table The escaped table name.
     * @param string $indexName The index name to use.
     * @param string $whereClause Optional WHERE clause.
     * @return int|null The count, or null if query failed.
     */
    private function tryCountWithIndex($table, $indexName, $whereClause)
    {
        $indexHint = ' USE INDEX(`' . $indexName . '`)';
        $count = $this->executeCount($table, $indexHint, $whereClause);

        if ($this->lastError === null) {
            return $count;
        }

        return null;
    }

    /**
     * Executes a COUNT query.
     *
     * @param string $table The escaped table name.
     * @param string $indexHint Optional index hint.
     * @param string $whereClause Optional WHERE clause.
     * @return int The count result.
     */
    private function executeCount($table, $indexHint, $whereClause)
    {
        $sql = 'SELECT COUNT(*) AS num FROM ' . $table . $indexHint;

        if (!empty($whereClause)) {
            $sql .= ' WHERE ' . $whereClause;
        }

        $result = $this->query($sql);

        if (!empty($result) && isset($result[0]['num'])) {
            return (int) $result[0]['num'];
        }

        return 0;
    }

    /**
     * Escapes a SQL identifier (table/column name).
     *
     * @param string $identifier The identifier to escape.
     * @return string The escaped identifier.
     */
    private function escapeIdentifier($identifier)
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    /**
     * {@inheritdoc}
     */
    public function getTableColumnsInfo($table)
    {
        $fields = $this->query('DESCRIBE `' . str_replace('`', '``', $table) . '`');

        $primaryKey = false;
        $autoIncrementPrimaryKey = false;
        $textColumns = [];

        if (!is_array($fields)) {
            return $this->buildColumnsInfoResult($primaryKey, $autoIncrementPrimaryKey, $textColumns);
        }

        foreach ($fields as $fieldData) {
            $columnInfo = $this->parseColumnInfo($fieldData, $table);

            if ($columnInfo['skip']) {
                continue;
            }

            if ($columnInfo['is_primary'] && $primaryKey === false) {
                $primaryKey = $columnInfo['name'];
            }

            if ($columnInfo['is_auto_increment_primary'] && $autoIncrementPrimaryKey === false) {
                $autoIncrementPrimaryKey = $columnInfo['name'];
            }

            if ($columnInfo['is_text']) {
                $textColumns[] = $columnInfo['name'];
            }
        }

        return $this->buildColumnsInfoResult($primaryKey, $autoIncrementPrimaryKey, $textColumns);
    }

    /**
     * Parses column information from DESCRIBE result.
     *
     * @param array $fieldData The field data from DESCRIBE.
     * @param string $table The table name.
     * @return array Parsed column information.
     */
    private function parseColumnInfo($fieldData, $table)
    {
        $field = $fieldData['Field'];
        $key = $fieldData['Key'];
        $extra = $fieldData['Extra'];
        $type = strtolower($fieldData['Type']);

        // Skip guid field in posts table
        $skip = (strpos($table, 'posts') !== false && $field === 'guid');

        $isPrimary = ($key === 'PRI');
        $isAutoIncrementPrimary = ($isPrimary && strpos($extra, 'auto_increment') !== false);
        $isText = (strpos($type, 'char') !== false || strpos($type, 'text') !== false);

        return [
            'name' => $field,
            'skip' => $skip,
            'is_primary' => $isPrimary,
            'is_auto_increment_primary' => $isAutoIncrementPrimary,
            'is_text' => $isText
        ];
    }

    /**
     * Builds the columns info result array.
     *
     * @param string|false $primaryKey The primary key.
     * @param string|false $autoIncrementPrimaryKey The auto-increment primary key.
     * @param array $textColumns The text columns.
     * @return array The result array.
     */
    private function buildColumnsInfoResult($primaryKey, $autoIncrementPrimaryKey, $textColumns)
    {
        return [
            'primary_key' => $primaryKey,
            'auto_increment_primary_key' => $autoIncrementPrimaryKey,
            'text_columns' => $textColumns
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fetchRows($sql)
    {
        $result = $this->query($sql);
        return is_array($result) ? $result : [];
    }

    /**
     * {@inheritdoc}
     */
    public function executeTransaction($queries)
    {
        if (empty($queries)) {
            return ['updates' => 0, 'errors' => []];
        }

        if ($this->useMysqli && $this->mysqli !== null) {
            return $this->executeMysqliTransaction($queries);
        }

        return $this->executeWpdbTransaction($queries);
    }

    /**
     * Executes transaction using MySQLi.
     *
     * @param array $queries The queries to execute.
     * @return array The result with updates count and errors.
     */
    private function executeMysqliTransaction($queries)
    {
        $errors = [];
        $updates = 0;

        $this->mysqli->begin_transaction();

        foreach ($queries as $sql) {
            if ($this->mysqli->query($sql) === true) {
                $updates++;
            } else {
                $errors[] = $this->mysqli->error;
            }
        }

        $this->mysqli->commit();

        return ['updates' => $updates, 'errors' => $errors];
    }

    /**
     * Executes transaction using $wpdb.
     *
     * @param array $queries The queries to execute.
     * @return array The result with updates count and errors.
     */
    private function executeWpdbTransaction($queries)
    {
        global $wpdb;
        $errors = [];
        $updates = 0;

        $wpdb->query('START TRANSACTION');

        foreach ($queries as $sql) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared –– $sql is assumed to be safe here
            $wpdb->query($sql);
            if ($wpdb->last_error === '') {
                $updates++;
            } else {
                $errors[] = $wpdb->last_error;
            }
        }

        $wpdb->query('COMMIT');

        return ['updates' => $updates, 'errors' => $errors];
    }

    /**
     * Executes a SQL query using the active connection.
     *
     * @param string $sql The SQL query to execute.
     * @return array|bool The query result (array of rows, true for success, or empty array on failure).
     */
    private function query($sql)
    {
        $this->lastError = null;

        if ($this->useMysqli && $this->mysqli !== null) {
            return $this->executeMysqliQuery($sql);
        }

        return $this->executeWpdbQuery($sql);
    }

    /**
     * Executes query using MySQLi.
     *
     * @param string $sql The SQL query.
     * @return array|bool The result.
     */
    private function executeMysqliQuery($sql)
    {
        $result = $this->mysqli->query($sql);

        if ($result === false) {
            $this->lastError = $this->mysqli->error;
            return [];
        }

        if ($result === true) {
            return true;
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $result->free();

        return $data;
    }

    /**
     * Executes query using $wpdb.
     *
     * @param string $sql The SQL query.
     * @return array|null The result.
     */
    private function executeWpdbQuery($sql)
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared –– $sql is assumed to be safe here
        $result = $wpdb->get_results($sql, ARRAY_A);

        if ($wpdb->last_error !== '') {
            $this->lastError = $wpdb->last_error;
        }

        return $result;
    }

    /**
     * Closes the MySQLi connection if open.
     */
    public function __destruct()
    {
        if ($this->mysqli !== null && $this->useMysqli) {
            $this->mysqli->close();
        }
    }

}
