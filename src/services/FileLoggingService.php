<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\logging\services;

use DateTime;
use felicity\config\Config;

/**
 * Class FileLoggingService
 */
class FileLoggingService
{
    /** @var string $filePath */
    private $filePath;

    /** @var string $indexFilePath */
    private $indexFilePath;

    /** @var string $completeFilePath */
    private $completeFilePath;

    /** @var int $maxArchives */
    private $maxArchives;

    /** @var int $fileSizeBytesArchiveTrigger */
    private $fileSizeBytesArchiveTrigger;

    /**
     * FileLoggingService constructor
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->filePath = rtrim(
            $config->getItem('felicity.logging.filePath'),
            '/'
        );

        $this->completeFilePath = "{$this->filePath}/felicityLog.log";

        $this->indexFilePath = "{$this->filePath}/felicityLogIndex.json";

        $this->maxArchives = $config->getItem(
            'felicity.logging.maxArchives',
            10
        );

        $this->fileSizeBytesArchiveTrigger = $config->getItem(
            'felicity.logging.fileSizeBytesArchiveTrigger',
            102400
        );
    }

    /**
     * Prepares log files
     * @return self
     */
    public function cleanUpLogs() : self
    {
        if (! $this->filePath || ! is_dir($this->filePath)) {
            return $this;
        }

        if (! file_exists($this->completeFilePath)) {
            file_put_contents($this->completeFilePath, '');
            chmod($this->completeFilePath, 0777);
        }

        if (! file_exists($this->indexFilePath)) {
            file_put_contents($this->indexFilePath, json_encode([]));
            chmod($this->indexFilePath, 0777);
        }

        if (filesize($this->completeFilePath) < $this->fileSizeBytesArchiveTrigger) {
            return $this;
        }

        $fileArray = json_decode(file_get_contents($this->indexFilePath), true);

        ksort($fileArray);

        $fileArray = array_reverse($fileArray, true);

        $counter = 0;

        $timestamp = time();

        foreach ($fileArray as $key => $filename) {
            $counter++;

            if ($counter <= $this->maxArchives) {
                continue;
            }

            unset($fileArray[$key]);

            if (! file_exists("{$this->filePath}/{$filename}")) {
                continue;
            }

            unlink("{$this->filePath}/{$filename}");
        }

        rename(
            $this->completeFilePath,
            "{$this->filePath}/felicityLog.{$timestamp}.log"
        );

        file_put_contents($this->completeFilePath, '');
        chmod($this->completeFilePath, 0777);

        $fileArray[$timestamp] = "felicityLog.{$timestamp}.log";

        file_put_contents(
            $this->indexFilePath,
            json_encode($fileArray)
        );
        chmod($this->indexFilePath, 0777);

        return $this;
    }

    /**
     * Logs session start to file
     * @param mixed $msg
     * @param string $level
     * @param string $category
     */
    public function writeToFileLog(
        $msg,
        $level,
        string $category = 'application'
    ) {
        $line = (new DateTime())->format('Y-m-d H:i:s') . ' ';

        $line .= "[{$this->getUserIpAddress()}] ";

        $line .= "[{$level}] ";

        $line .= "[{$category}] ";

        $line .= "{$this->stringifyVar($msg)}\n";

        file_put_contents($this->completeFilePath, $line, FILE_APPEND);
    }

    /**
     * Gets user's IP address
     * @return string
     */
    public function getUserIpAddress() : string
    {
        if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        if (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        if (! empty($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }

        return '';
    }

    /**
     * Runs a var
     * @param $item
     * @return string
     */
    private function stringifyVar($item) : string
    {
        if (\is_string($item)) {
            return $item;
        }

        try {
            return var_export($item, true);
        } catch (\Exception $e) {
            return print_r($item, true);
        }
    }
}
