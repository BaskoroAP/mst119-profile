<?php

namespace BMI\Plugin\Database;

require_once BMI_INCLUDES . '/check/class-domain-parser.php';
require_once BMI_INCLUDES . '/database/search-replace-v2.php';

use BMI\Plugin\Checker\DomainParser;

/**
 * Class BMISearchReplaceProcessor
 *
 * Handles the search and replace operations during database restoration.
 * It orchestrates the process across multiple tables and handles logging and progress tracking.
 */
class BMISearchReplaceProcessor
{
    const DEFAULT_TABLE_PREFIX = 'wp_';
    const PROGRESS_BASE = 90;
    const PROGRESS_RANGE = 8;
    const MIN_URL_LENGTH = 8;

    /**
     * @var array List of tables to process.
     */
    private $tables;

    /**
     * @var object Logger instance for logging messages.
     */
    private $logger;

    /**
     * @var object Manifest object containing backup configuration.
     */
    private $manifest;

    /**
     * @var array List of tables to exclude from search and replace.
     */
    private $excludeTables;

    /**
     * @var DomainParser Domain parser instance.
     */
    private $domainParser;

    /**
     * @var callable|null Factory for creating engine instances.
     */
    private $engineFactory;

    /**
     * BMISearchReplaceProcessor constructor.
     *
     * @param array $tables List of tables to process.
     * @param object $logger Logger instance.
     * @param object $manifest Manifest object.
     * @param array $excludeTables Optional. List of tables to exclude.
     * @param DomainParser|null $domainParser Optional. Domain parser instance.
     * @param callable|null $engineFactory Optional. Factory for creating engine instances.
     */
    public function __construct(
        $tables,
        $logger,
        $manifest,
        $excludeTables = [],
        $domainParser = null,
        $engineFactory = null
    ) {
        $this->tables = $tables;
        $this->logger = $logger;
        $this->manifest = $manifest;
        $this->excludeTables = $excludeTables;
        $this->domainParser = $domainParser !== null ? $domainParser : new DomainParser();
        $this->engineFactory = $engineFactory;
    }

    /**
     * Main entry point for the search and replace process.
     *
     * Determines if search and replace is necessary based on domain and path changes.
     * Prepares search and replace arrays and delegates table processing.
     *
     * @param int $step Current step of the process.
     * @param int $tableIndex Index of the current table being processed.
     * @param string $newPrefix New table prefix to filter tables.
     * @return array Status array containing step, tableIndex, finished status, etc.
     */
    public function process($step, $tableIndex, $newPrefix = 'wp_')
    {
        $migrationContext = $this->buildMigrationContext();

        // Step 0: Initial Logging and Check
        if ($step === 0) {
            $this->logMigrationContext($migrationContext);

            if (!$this->isSearchReplaceNeeded($migrationContext)) {
                $this->logger->log(
                    __('This backup was made on the same site, omitting search & replace.', 'backup-backup'),
                    'INFO'
                );
                return $this->buildFinishedResult($step, $tableIndex);
            }

            $this->logSearchReplaceStart();
            $step++;
        }

        $searchReplacePairs = $this->domainParser->buildSearchReplacePairs(
            $migrationContext['backupDomain'],
            $migrationContext['currentDomain'],
            $migrationContext['backupRootDir'],
            $migrationContext['currentRootDir'],
            $migrationContext['useSSL']
        );

        return $this->processTable(
            $step,
            $tableIndex,
            $searchReplacePairs['search'],
            $searchReplacePairs['replace'],
            $newPrefix
        );
    }

    /**
     * Builds the migration context with all necessary information.
     *
     * @return array The migration context.
     */
    private function buildMigrationContext()
    {
        $backupRootDir = $this->manifest->config->ABSPATH;
        $currentRootDir = ABSPATH;
        $homeURL = $this->determineHomeURL();
        $useSSL = is_ssl();

        return [
            'backupRootDir' => $backupRootDir,
            'currentRootDir' => $currentRootDir,
            'backupDomain' => $this->domainParser->parse($this->manifest->dbdomain),
            'currentDomain' => $this->domainParser->parse($homeURL, false),
            'useSSL' => $useSSL,
            'sslPrefix' => $useSSL ? 'https://' : 'http://'
        ];
    }

    /**
     * Determines the home URL to use.
     *
     * @return string The home URL.
     */
    private function determineHomeURL()
    {
        $homeURL = site_url();

        if (strlen($homeURL) <= self::MIN_URL_LENGTH) {
            $homeURL = home_url();
        }

        if (defined('WP_SITEURL') && strlen(WP_SITEURL) > self::MIN_URL_LENGTH) {
            $homeURL = WP_SITEURL;
        }

        return $homeURL;
    }

    /**
     * Checks if search and replace is needed.
     *
     * @param array $context The migration context.
     * @return bool True if search and replace is needed.
     */
    private function isSearchReplaceNeeded($context)
    {
        $pathChanged = $context['backupRootDir'] !== $context['currentRootDir'];
        $domainChanged = !$this->domainParser->areEqual(
            $context['backupDomain'],
            $context['currentDomain'],
            false
        );

        return $pathChanged || $domainChanged;
    }

    /**
     * Logs the migration context for debugging.
     *
     * @param array $context The migration context.
     */
    private function logMigrationContext($context)
    {
        $this->logger->log('Previous detected domain: ' . $context['backupDomain'], 'VERBOSE');
        $this->logger->log(
            'Previous detected domain parsed: ' . $this->domainParser->parse($context['backupDomain'], false),
            'VERBOSE'
        );
        $this->logger->log('New detected domain: ' . $context['currentDomain'], 'VERBOSE');
        $this->logger->log('SSL: ' . $context['sslPrefix'], 'VERBOSE');
        $this->logger->log('Previous ABSPATH: ' . $context['backupRootDir'], 'VERBOSE');
        $this->logger->log('New detected ABSPATH: ' . $context['currentRootDir'], 'VERBOSE');
    }

    /**
     * Logs the start of search and replace process.
     */
    private function logSearchReplaceStart()
    {
        $this->logger->log(__('Performing Search & Replace', 'backup-backup'), 'STEP');

        $pagesize = defined('BMI_MAX_SEARCH_REPLACE_PAGE') ? BMI_MAX_SEARCH_REPLACE_PAGE : '?';
        $this->logger->log(
            __('Page size for that restoration: ', 'backup-backup') . $pagesize,
            'INFO'
        );
    }

    /**
     * Builds a finished result array.
     *
     * @param int $step The current step.
     * @param int $tableIndex The current table index.
     * @return array The result array.
     */
    private function buildFinishedResult($step, $tableIndex)
    {
        return [
            'step' => $step,
            'tableIndex' => $tableIndex,
            'finished' => true,
            // Deprecated, but kept for backward compatibility, we use reporting via files instead
            'currentPage' => 0,
            'totalPages' => 0,
            'fieldAdjustments' => 0
        ];
    }

    /**
     * Builds an in-progress result array.
     *
     * @param int $step The current step.
     * @param int $tableIndex The current table index.
     * @return array The result array.
     */
    private function buildInProgressResult($step, $tableIndex)
    {
        return [
            'step' => $step,
            'tableIndex' => $tableIndex,
            // Deprecated, but kept for backward compatibility, we use reporting via files instead
            'finished' => false,
            'currentPage' => 0,
            'totalPages' => 0,
            'fieldAdjustments' => 0
        ];
    }

    /**
     * Processes a single table for search and replace.
     *
     * @param int $step Current step.
     * @param int $tableIndex Index of the table to process.
     * @param array $searchArray Array of strings to search for.
     * @param array $replaceArray Array of replacement strings.
     * @param string $newPrefix Table prefix.
     * @return array Status array.
     */
    private function processTable(
        $step,
        $tableIndex,
        $searchArray,
        $replaceArray,
        $newPrefix
    ) {
        $allTables = array_keys($this->tables);

        if ($tableIndex >= count($allTables)) {
            return $this->buildFinishedResult($step, $tableIndex);
        }

        $currentTable = $allTables[$tableIndex];

        if (!$this->shouldProcessTable($currentTable, $newPrefix)) {
            $this->logSkippedTable($currentTable);
            return $this->processTable($step, $tableIndex + 1, $searchArray, $replaceArray, $newPrefix);
        }

        $this->logProgress($currentTable, $tableIndex, count($allTables));

        $searchReplace = $this->createEngine($currentTable);
        $report = $searchReplace->search_replace($searchArray, $replaceArray);

        $this->logBatchInfo(
            $report['batch'],
            $report['total_batches'],
            $report['stats']['updates'],
            $report['errors']
        );

        if ($report['status'] === 'completed') {
            if ($report['stats']['updates'] === 0) {
                return $this->processTable($step, $tableIndex + 1, $searchArray, $replaceArray, $newPrefix);
            }
            return $this->buildInProgressResult($step, $tableIndex + 1);
        }

        return $this->buildInProgressResult($step, $tableIndex);
    }

    /**
     * Checks if a table should be processed.
     *
     * @param string $table Table name.
     * @param string $newPrefix Table prefix.
     * @return bool True if the table should be processed.
     */
    private function shouldProcessTable($table, $newPrefix)
    {
        if (strpos($table, $newPrefix) === false) {
            return false;
        }

        foreach ($this->excludeTables as $exclude) {
            if (strpos($table, $exclude) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Logs a message indicating that a table was skipped.
     *
     * @param string $table Table name.
     */
    private function logSkippedTable($table)
    {
        $this->logger->log(
            __('Adjustments are not required for this table.', 'backup-backup') .
            '(' . sanitize_text_field(strval($table)) . ')',
            'INFO'
        );
    }

    /**
     * Logs the progress of the search and replace operation.
     *
     * @param string $table Table name.
     * @param int $index Current table index.
     * @param int $total Total number of tables.
     */
    private function logProgress($table, $index, $total)
    {
        $progressLogT = __(
            'Performing database adjustments for table %progress%: %table_name% (%progress_percentage%)',
            'backup-backup'
        );

        $replaceProgress = ($index + 1) . '/' . $total;
        $replaceProgressPercentage = number_format((($index + 1) / $total * 100), 2);

        $progressLogT = str_replace('%progress%', $replaceProgress, $progressLogT);
        $progressLogT = str_replace('%table_name%', $table, $progressLogT);
        $progressLogT = str_replace('%progress_percentage%', $replaceProgressPercentage . '%', $progressLogT);

        $this->logger->log($progressLogT, 'STEP');

        $percentageProgress = (float) number_format($replaceProgressPercentage, 0);
        $overallProgress = self::PROGRESS_BASE + ($percentageProgress / 100) * self::PROGRESS_RANGE;
        $this->logger->progress((int) number_format($overallProgress, 0));
    }

    /**
     * Logs information about the current batch of updates.
     *
     * @param int $batchNumber Current batch number.
     * @param int $totalBatches Total number of batches.
     * @param int $updates Number of updates performed.
     * @param array $errors List of errors encountered.
     */
    private function logBatchInfo($batchNumber, $totalBatches, $updates, $errors)
    {
        if ($updates > 0) {
            $info = __('Batch for adjustments (%page%/%allPages%) updated: %updates% fields.', 'backup-backup');
            $info = str_replace('%page%', (string) $batchNumber, $info);
            $info = str_replace('%allPages%', (string) $totalBatches, $info);
            $info = str_replace('%updates%', (string) $updates, $info);

            $this->logger->log($info, 'INFO');

            foreach ($errors as $error) {
                $this->logger->log(
                    __('Error during adjustments: ', 'backup-backup') . sanitize_text_field(strval($error)),
                    'ERROR'
                );
            }
        } else {
            $this->logger->log(__('No adjustments were necessary for this batch.', 'backup-backup'), 'INFO');
        }
    }

    /**
     * Creates an instance of the search and replace engine.
     *
     * @param string $table Table name.
     * @return BMISearchReplaceV2Engine Engine instance.
     */
    protected function createEngine($table)
    {
        if ($this->engineFactory !== null) {
            return call_user_func($this->engineFactory, $table);
        }

        return new BMISearchReplaceV2Engine($table);
    }
}
