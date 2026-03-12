<?php

namespace BMI\Plugin\Progress;

/**
 * Interface ProgressStorageInterface
 *
 * Defines the contract for storing and retrieving progress state
 * during search and replace operations.
 */
interface ProgressStorageInterface
{
    /**
     * Loads the stored progress report.
     *
     * @return array|null The progress report array, or null if not found.
     */
    public function load();

    /**
     * Saves the progress report.
     *
     * @param array $report The report array to save.
     * @return bool True on success, false on failure.
     */
    public function save($report);

    /**
     * Clears/deletes the stored progress.
     *
     * @return bool True on success, false on failure.
     */
    public function clear();

    /**
     * Checks if a stored progress report exists.
     *
     * @return bool True if exists, false otherwise.
     */
    public function exists();
}
