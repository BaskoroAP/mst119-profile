<?php

namespace BMI\Plugin\Database;

require_once BMI_INCLUDES . '/database/search-replace-repository.php';
require_once BMI_INCLUDES . '/database/interface-search-replace-repository.php';
require_once BMI_INCLUDES . '/progress/class-file-progress-storage.php';
require_once BMI_INCLUDES . '/database/search-replace-stack-based.php';

use BMI\Plugin\Progress\ProgressStorageInterface;
use BMI\Plugin\Progress\FileProgressStorage;

/**
 * Class BMISearchReplaceV2Engine
 *
 * Handles search and replace operations on a single database table.
 * Supports resumable batch processing with progress tracking.
 */
class BMISearchReplaceV2Engine
{
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const PAGINATION_KEY_BASED = 'key_based';
    const PAGINATION_OFFSET_BASED = 'offset_based';
    const MEMORY_THRESHOLD_DIVISOR = 8;

    /**
     * @var string The table name.
     */
    public $table;

    /**
     * @var SearchReplaceRepositoryInterface The database repository.
     */
    private $repository;

    /**
     * @var ProgressStorageInterface The progress storage handler.
     */
    private $progressStorage;

    /**
     * @var int The page size for batch processing.
     */
    private $pageSize;

    /**
     * @var BMIStackSearchReplace The search and replace algorithm instance.
     */
    private $searchReplaceAlgorithm;

    /**
     * Constructor for the BMISearchReplaceV2Engine class.
     *
     * @param string $table The name of the table to perform search and replace on.
     * @param SearchReplaceRepositoryInterface|null $repository Optional repository instance.
     * @param int|null $pageSize Optional page size. Defaults to BMI_MAX_SEARCH_REPLACE_PAGE.
     * @param ProgressStorageInterface|null $progressStorage Optional progress storage instance.
     */
    public function __construct(
        $table,
        $repository = null,
        $pageSize = null,
        $progressStorage = null,
        $searchReplaceAlgorithm = null
    ) {
        $this->table = $table;
        $this->repository = $repository !== null ? $repository : new SearchReplaceRepository();
        $this->pageSize = $pageSize !== null ? $pageSize : (defined('BMI_MAX_SEARCH_REPLACE_PAGE') ? BMI_MAX_SEARCH_REPLACE_PAGE : 5000);
        $this->progressStorage = $progressStorage !== null ? $progressStorage : new FileProgressStorage();
        $this->searchReplaceAlgorithm = $searchReplaceAlgorithm !== null ? $searchReplaceAlgorithm : new BMIStackSearchReplace();
    }

    /**
     * Returns the default structure for the progress report.
     *
     * @return array The default report array structure.
     */
    private function getDefaultReport()
    {
        return [
            'status' => self::STATUS_IN_PROGRESS,
            'time' => [
                'start' => microtime(true),
                'end' => 0
            ],
            'errors' => [],
            'info' => [
                'name' => '',
                'text_columns' => [],
                'primary_key' => '',
                'auto_increment_primary_key' => '',
                'rows_count' => 0,
                'paginationType' => '',
                'key_based_pagination' => [
                    'last_processed_key' => 0,
                    'batch_size' => 0,
                ],
                'offset_based_pagination' => [
                    'offset' => 0,
                    'limit_value' => 0,
                ],
                'total_batches' => 0,
            ],
            'current_batch' => 1,
            'page_size' => $this->pageSize,
        ];
    }

    /**
     * Retrieves the latest progress report or initializes a new one.
     *
     * @param array|string $searchArray The string(s) to search for.
     * @param array|string $replaceArray The string(s) to replace with.
     * @return array The progress report array.
     */
    private function getLatestReport($searchArray, $replaceArray)
    {
        $storedReport = $this->progressStorage->load();

        if ($storedReport !== null) {
            return $storedReport;
        }

        return $this->initializeReport($searchArray, $replaceArray);
    }

    /**
     * Initializes a new progress report with table information and pagination settings.
     *
     * @param array|string $searchArray The string(s) to search for.
     * @param array|string $replaceArray The string(s) to replace with.
     * @return array The initialized report array.
     */
    private function initializeReport($searchArray, $replaceArray)
    {
        $report = $this->getDefaultReport();
        $tableInfo = $this->repository->getTableColumnsInfo($this->table);

        $report['info']['name'] = $this->table;
        $report['info']['text_columns'] = $tableInfo['text_columns'];
        $report['info']['primary_key'] = $tableInfo['primary_key'];
        $report['info']['auto_increment_primary_key'] = $tableInfo['auto_increment_primary_key'];

        $whereClause = $this->buildFilterWhere($tableInfo['text_columns'], $searchArray, $replaceArray);
        $rowsCount = $this->repository->getTableRowCount(
            $this->table,
            $tableInfo['primary_key'],
            $whereClause
        );

        $report['info']['rows_count'] = $rowsCount;
        $report = $this->configurePagination($report, $tableInfo);
        $report['info']['total_batches'] = (int) ceil($rowsCount / $this->pageSize);

        return $report;
    }

    /**
     * Configures pagination settings based on table info.
     *
     * @param array $report The report array.
     * @param array $tableInfo The table column info.
     * @return array The updated report array.
     */
    private function configurePagination($report, $tableInfo)
    {
        if ($tableInfo['auto_increment_primary_key'] !== false) {
            $report['info']['paginationType'] = self::PAGINATION_KEY_BASED;
            $report['info']['key_based_pagination']['batch_size'] = $this->pageSize;
        } else {
            $report['info']['paginationType'] = self::PAGINATION_OFFSET_BASED;
            $report['info']['offset_based_pagination']['limit_value'] = $this->pageSize;
        }

        return $report;
    }

    /**
     * Builds the WHERE clause for filtering rows based on search terms.
     *
     * @param array $textColumns List of columns to search in.
     * @param array|string $searchArray The string(s) to search for.
     * @param array|string $replaceArray The string(s) to replace with.
     * @return string The WHERE clause condition.
     */
    private function buildFilterWhere($textColumns, $searchArray, $replaceArray)
    {
        if (empty($textColumns)) {
            return '';
        }

        $searches = $this->normalizeToArray($searchArray);
        $replaces = $this->normalizeToArray($replaceArray);

        if (count($searches) !== count($replaces)) {
            return '';
        }

        $conditions = $this->buildFilterConditions($textColumns, $searches, $replaces);

        return !empty($conditions) ? '(' . implode(' OR ', $conditions) . ')' : '';
    }

    /**
     * Normalizes a value to an array.
     *
     * @param array|string $value The value to normalize.
     * @return array The normalized array.
     */
    private function normalizeToArray($value)
    {
        return is_array($value) ? $value : [$value];
    }

    /**
     * Builds filter conditions for the WHERE clause.
     *
     * @param array $textColumns The text columns to search.
     * @param array $searches The search strings.
     * @param array $replaces The replace strings.
     * @return array The conditions array.
     */
    private function buildFilterConditions($textColumns, $searches, $replaces)
    {
        $conditions = [];

        foreach ($textColumns as $column) {
            foreach ($searches as $index => $search) {
                $replace = $replaces[$index];
                $escapedSearch = $this->repository->escape($search);
                $escapedReplace = $this->repository->escape($replace);

                // Avoid infinite loop when replace string contains search string
                $conditions[] = sprintf(
                    '(`%s` LIKE "%%%s%%" AND `%s` NOT LIKE "%%%s%%")',
                    $column,
                    $escapedSearch,
                    $column,
                    $escapedReplace
                );
            }
        }

        return $conditions;
    }

    /**
     * Performs the search and replace operation on the table.
     *
     * Handles pagination (key-based or offset-based), fetches data in batches,
     * applies the search/replace logic, and updates the database.
     *
     * @param array|string $searchArray The string(s) to search for.
     * @param array|string $replaceArray The string(s) to replace with.
     * @return array The progress report array.
     */
    public function search_replace($searchArray = '', $replaceArray = '')
    {
        $report = $this->getLatestReport($searchArray, $replaceArray);
        $batchStats = $this->createEmptyBatchStats();

        // Early return if no rows to process
        if ($report['info']['rows_count'] === 0) {
            return $this->completeProcessing($report, $batchStats);
        }

        // Fetch batch data
        $fetchResult = $this->fetchBatch($report, $searchArray, $replaceArray);

        if ($fetchResult['error'] !== null) {
            $report['errors'][] = $fetchResult['error'];
            return $this->buildResultArray(self::STATUS_IN_PROGRESS, $report, $batchStats);
        }

        if (empty($fetchResult['data'])) {
            return $this->completeProcessing($report, $batchStats);
        }

        // Update pagination state
        $report = $this->updatePaginationState($report, $fetchResult['data']);

        // Process rows and build update queries
        $processingResult = $this->processBatchRows(
            $fetchResult['data'],
            $report['info'],
            $searchArray,
            $replaceArray,
            $batchStats
        );

        // Execute remaining update queries
        $this->executeTransaction($processingResult['queries'], $batchStats);


        // Determine if processing is complete
        if ($batchStats['rows'] < $report['page_size']) {
            return $this->completeProcessing($report, $batchStats);
        }

        // Update batch counter
        $report['current_batch']++;


        $this->progressStorage->save($report);
        return $this->buildResultArray(self::STATUS_IN_PROGRESS, $report, $batchStats, -1);
    }

    /**
     * Creates an empty batch stats array.
     *
     * @return array The empty stats array.
     */
    private function createEmptyBatchStats()
    {
        return [
            'rows' => 0,
            'changes' => 0,
            'updates' => 0,
            'updates_queries_size' => 0,
        ];
    }

    /**
     * Fetches a batch of rows based on current pagination state.
     *
     * @param array $report The current report.
     * @param array|string $searchArray The search strings.
     * @param array|string $replaceArray The replace strings.
     * @return array{data: array, error: string|null}
     */
    private function fetchBatch($report, $searchArray, $replaceArray)
    {
        $sql = $this->buildBatchQuery($report, $searchArray, $replaceArray);
        $data = $this->repository->fetchRows($sql);
        $error = $this->repository->getLastError();

        return [
            'data' => $data,
            'error' => $error
        ];
    }

    /**
     * Builds the SQL query for fetching a batch of rows.
     *
     * @param array $report The current report.
     * @param array|string $searchArray The search strings.
     * @param array|string $replaceArray The replace strings.
     * @return string The SQL query.
     */
    private function buildBatchQuery($report, $searchArray, $replaceArray)
    {
        $table = $report['info']['name'];
        $sql = sprintf('SELECT * FROM `%s`', str_replace('`', '``', $table));

        if ($report['info']['paginationType'] === self::PAGINATION_KEY_BASED) {
            $sql .= $this->buildKeyBasedPaginationClause($report, $searchArray, $replaceArray);
        } else {
            $sql .= $this->buildOffsetBasedPaginationClause($report);
        }

        return $sql;
    }

    /**
     * Builds pagination clause for key-based pagination.
     *
     * @param array $report The current report.
     * @param array|string $searchArray The search strings.
     * @param array|string $replaceArray The replace strings.
     * @return string The SQL clause.
     */
    private function buildKeyBasedPaginationClause($report, $searchArray, $replaceArray)
    {
        $autoIncrementKey = $report['info']['auto_increment_primary_key'];
        $lastProcessedKey = $report['info']['key_based_pagination']['last_processed_key'];
        $batchSize = $report['info']['key_based_pagination']['batch_size'];

        $filterWhere = $this->buildFilterWhere(
            $report['info']['text_columns'],
            $searchArray,
            $replaceArray
        );

        $whereClause = sprintf(' WHERE `%s` > %d', $autoIncrementKey, (int) $lastProcessedKey);

        if (!empty($filterWhere)) {
            $whereClause .= ' AND ' . $filterWhere;
        }

        return sprintf('%s ORDER BY `%s` ASC LIMIT %d', $whereClause, $autoIncrementKey, (int) $batchSize);
    }

    /**
     * Builds pagination clause for offset-based pagination.
     *
     * @param array $report The current report.
     * @return string The SQL clause.
     */
    private function buildOffsetBasedPaginationClause($report)
    {
        $offset = $report['info']['offset_based_pagination']['offset'];
        $limitValue = $report['info']['offset_based_pagination']['limit_value'];

        // SAFETY: For offset-based pagination, don't use SQL filter to avoid skipping rows
        return sprintf(' LIMIT %d, %d', (int) $offset, (int) $limitValue);
    }

    /**
     * Updates pagination state after fetching a batch.
     *
     * @param array $report The current report.
     * @param array $data The fetched data.
     * @return array The updated report.
     */
    private function updatePaginationState($report, $data)
    {
        if ($report['info']['paginationType'] === self::PAGINATION_KEY_BASED) {
            $lastRow = end($data);
            $autoIncrementKey = $report['info']['auto_increment_primary_key'];
            $lastKey = $this->getRowValue($lastRow, $autoIncrementKey);
            $report['info']['key_based_pagination']['last_processed_key'] = $lastKey;
        } else {
            $report['info']['offset_based_pagination']['offset'] += count($data);
        }

        return $report;
    }

    /**
     * Processes batch rows and builds update queries.
     *
     * @param array $data The rows to process.
     * @param array $info The table info.
     * @param array|string $searchArray The search strings.
     * @param array|string $replaceArray The replace strings.
     * @param array &$batchStats Reference to batch stats.
     * @return array{queries: array}
     */
    private function processBatchRows(
        $data,
        $info,
        $searchArray,
        $replaceArray,
        &$batchStats
    ) {
        $updateQueries = [];
        $textColumns = $info['text_columns'];
        $primaryKey = $info['primary_key'];
        $tableName = $info['name'];

        foreach ($data as $row) {
            $batchStats['rows']++;
            $rowResult = $this->processRow($row, $textColumns, $primaryKey, $searchArray, $replaceArray, $batchStats);

            if ($rowResult['hasUpdates']) {
                $sql = $this->buildUpdateQuery($tableName, $rowResult['setClauses'], $rowResult['whereClauses']);
                $batchStats['updates_queries_size'] += strlen($sql);
                $updateQueries[] = $sql;

                $this->maybeExecuteTransaction($updateQueries, $batchStats);
            }
        }

        return ['queries' => $updateQueries];
    }

    /**
     * Processes a single row for search and replace.
     *
     * @param array|object $row The row data.
     * @param array $textColumns The text columns to process.
     * @param string|false $primaryKey The primary key column.
     * @param array|string $searchArray The search strings.
     * @param array|string $replaceArray The replace strings.
     * @param array &$batchStats Reference to batch stats.
     * @return array{hasUpdates: bool, setClauses: array, whereClauses: array}
     */
    private function processRow(
        $row,
        $textColumns,
        $primaryKey,
        $searchArray,
        $replaceArray,
        &$batchStats
    ) {
        $setClauses = [];
        $whereClauses = [];
        $hasUpdates = false;

        foreach ($textColumns as $column) {
            $originalData = $this->getRowValue($row, $column);
            $editedData = $this->searchReplaceAlgorithm->replace($searchArray, $replaceArray, $originalData);

            if ($editedData !== $originalData) {
                $batchStats['changes']++;
                $setClauses[] = sprintf('`%s` = "%s"', $column, $this->repository->escape($editedData));
                $hasUpdates = true;

                if ($primaryKey !== false) {
                    $pkValue = $this->getRowValue($row, $primaryKey);
                    $whereClauses[] = sprintf('`%s` = "%s"', $primaryKey, $this->repository->escape($pkValue));
                } else {
                    $whereClauses[] = sprintf('`%s` = "%s"', $column, $this->repository->escape($originalData));
                }
            }
        }

        return [
            'hasUpdates' => $hasUpdates,
            'setClauses' => $setClauses,
            'whereClauses' => $whereClauses
        ];
    }

    /**
     * Gets a value from a row (handles both array and object).
     *
     * @param array|object $row The row data.
     * @param string $column The column name.
     * @return mixed The value.
     */
    private function getRowValue($row, $column)
    {
        return is_object($row) ? $row->$column : $row[$column];
    }

    /**
     * Builds an UPDATE SQL query.
     *
     * @param string $table The table name.
     * @param array $setClauses The SET clauses.
     * @param array $whereClauses The WHERE clauses.
     * @return string The SQL query.
     */
    private function buildUpdateQuery($table, $setClauses, $whereClauses)
    {
        return sprintf(
            'UPDATE `%s` SET %s WHERE %s',
            str_replace('`', '``', $table),
            implode(', ', $setClauses),
            implode(' AND ', array_filter($whereClauses))
        );
    }

    /**
     * Completes processing and clears progress storage.
     *
     * @param array $report The current report.
     * @param array $batchStats The batch statistics.
     * @return array The result array.
     */
    private function completeProcessing($report, $batchStats)
    {
        $report['time']['end'] = microtime(true);
        $report['status'] = self::STATUS_COMPLETED;
        $this->progressStorage->clear();

        return $this->buildResultArray(self::STATUS_COMPLETED, $report, $batchStats);
    }

    /**
     * Builds the result array.
     *
     * @param string $status The status.
     * @param array $report The report.
     * @param array $batchStats The batch stats.
     * @param int $batchOffset Offset to apply to current_batch (default 0).
     * @return array The result array.
     */
    private function buildResultArray($status, $report, $batchStats, $batchOffset = 0)
    {
        return [
            'status' => $status,
            'batch' => $report['current_batch'] + $batchOffset,
            'total_batches' => $report['info']['total_batches'],
            'stats' => $batchStats,
            'errors' => $report['errors']
        ];
    }

    /**
     * Executes update queries if memory threshold is reached.
     *
     * @param array &$updateQueries The array of update queries.
     * @param array &$batchStats Reference to batch stats.
     */
    private function maybeExecuteTransaction(&$updateQueries, &$batchStats)
    {
        $threshold = $this->getMemoryThreshold();

        if ($batchStats['updates_queries_size'] >= $threshold) {
            $result = $this->repository->executeTransaction($updateQueries);
            $batchStats['updates'] += $result['updates'];

            $updateQueries = [];
            $batchStats['updates_queries_size'] = 0;
        }
    }

    /**
     * Gets the memory threshold for batch execution.
     *
     * @return int The threshold in bytes.
     */
    private function getMemoryThreshold()
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->convertToBytes($memoryLimit);
        return (int) ($memoryLimitBytes / self::MEMORY_THRESHOLD_DIVISOR);
    }

    /**
     * Executes a batch of SQL update queries in a transaction.
     *
     * @param array $updateQueries The array of update queries.
     * @param array &$batchStats Reference to batch stats.
     */
    private function executeTransaction($updateQueries, &$batchStats)
    {
        if (!empty($updateQueries)) {
            $result = $this->repository->executeTransaction($updateQueries);
            $batchStats['updates'] += $result['updates'];
        }
    }

    /**
     * Converts a memory size string (e.g., "128M", "1G") to bytes.
     *
     * @param string $size The memory size string.
     * @return int The size in bytes.
     */
    private function convertToBytes($size)
    {
        $unit = strtolower(substr($size, -1));
        $bytes = (int) $size;

        switch ($unit) {
            case 'g':
                $bytes *= 1024;
            // fall through
            case 'm':
                $bytes *= 1024;
            // fall through
            case 'k':
                $bytes *= 1024;
        }

        return $bytes;
    }

}
