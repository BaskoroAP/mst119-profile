<?php

namespace BMI\Plugin\Progress;

require_once BMI_INCLUDES . '/progress/interface-progress-storage.php';

/**
 * Class FileProgressStorage
 *
 * File-based implementation of ProgressStorageInterface.
 * Stores progress as JSON in a temporary file.
 */
class FileProgressStorage implements ProgressStorageInterface
{
    /**
     * @var string The file path for storing progress.
     */
    private $filePath;

    /**
     * Constructor.
     *
     * @param string|null $storagePath Optional custom storage path. Defaults to BMI_TMP directory.
     * @param string $fileName The file name for the progress file.
     */
    public function __construct($storagePath = null, $fileName = 'search-replace-report.json')
    {
        $basePath = $storagePath !== null ? $storagePath : (defined('BMI_TMP') ? BMI_TMP : sys_get_temp_dir());
        $this->filePath = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        if (!$this->exists()) {
            return null;
        }

        $content = file_get_contents($this->filePath);
        if ($content === false) {
            return null;
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function save($report)
    {
        $json = json_encode($report, JSON_PRETTY_PRINT);
        if ($json === false) {
            return false;
        }

        $result = file_put_contents($this->filePath, $json, LOCK_EX);
        return $result !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        if (!$this->exists()) {
            return true;
        }

        return unlink($this->filePath);
    }

    /**
     * {@inheritdoc}
     */
    public function exists()
    {
        return file_exists($this->filePath) && is_readable($this->filePath);
    }

    /**
     * Gets the storage file path.
     *
     * @return string The file path.
     */
    public function getFilePath()
    {
        return $this->filePath;
    }
}
