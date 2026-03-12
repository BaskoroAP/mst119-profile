<?php

namespace BMI\Plugin\Database;

/**
 * Interface SearchReplaceRepositoryInterface
 *
 * Defines the contract for database interactions used in search and replace operations.
 * Implementations can use MySQLi, PDO, or WordPress $wpdb.
 */
interface SearchReplaceRepositoryInterface
{
    /**
     * Escapes a string for safe use in SQL queries.
     *
     * @param string $string The string to escape.
     * @return string The escaped string.
     */
    public function escape($string);

    /**
     * Retrieves the last error message from the database.
     *
     * @return string|null The last error message, or null if no error.
     */
    public function getLastError();

    /**
     * Retrieves the total row count of a table.
     *
     * @param string $table The table name.
     * @param string|false $primaryKey The primary key column name, or false if none.
     * @param string $whereClause Optional WHERE clause to filter rows.
     * @return int The total number of rows.
     */
    public function getTableRowCount($table, $primaryKey, $whereClause = '');

    /**
     * Retrieves column information for a table.
     *
     * @param string $table The table name.
     * @return array An associative array containing:
     *               - 'primary_key': The primary key column name (or false).
     *               - 'auto_increment_primary_key': The auto-increment primary key column name (or false).
     *               - 'text_columns': An array of column names that are of type char or text.
     */
    public function getTableColumnsInfo($table);

    /**
     * Fetches rows from the database based on a SQL query.
     *
     * @param string $sql The SQL query to execute.
     * @return array The result rows as associative arrays.
     */
    public function fetchRows($sql);

    /**
     * Executes a list of queries within a database transaction.
     *
     * @param array $queries An array of SQL queries to execute.
     * @return array An associative array containing:
     *               - 'updates': The number of successful updates.
     *               - 'errors': An array of error messages.
     */
    public function executeTransaction($queries);
}
