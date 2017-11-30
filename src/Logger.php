<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\logging;

use felicity\config\Config;
use felicity\logging\models\LogModel;
use felicity\datamodel\ModelCollection;
use felicity\logging\services\FileLoggingService;

/**
 * Class Logging
 */
class Logger
{
    /** @var string LEVEL_ERROR */
    const LEVEL_ERROR = 1;

    /** @var string LEVEL_ERROR */
    const LEVEL_WARNING = 2;

    /** @var string LEVEL_INFO */
    const LEVEL_INFO = 3;

    /** @var string LEVEL_INFO */
    const LEVEL_TRACE = 4;

    public static $levelStringMap = [
        1 => 'error',
        2 => 'warning',
        3 => 'info',
        4 => 'trace',
    ];

    /** @var Logger $instance */
    public static $instance;

    /** @var ModelCollection $instanceLogs */
    private $instanceLogs;

    /** @var float $startTime */
    private $startTime;

    /** @var FileLoggingService $fileLoggingService */
    private $fileLoggingService;

    /** @var bool $enableFileLogging */
    private $enableFileLogging;

    /** @var int $fileLogLevel */
    private $fileLogLevel;

    /**
     * Private constructor prevents the creation of this class outside of itself
     */
    private function __construct()
    {
        $this->instanceLogs = new ModelCollection();

        $this->startTime = microtime(true);

        $this->fileLoggingService = new FileLoggingService(
            Config::getInstance()
        );

        $this->enableFileLogging = Config::get(
            'felicity.logging.enableFileLogging',
            false
        );

        $this->fileLogLevel = Config::get(
            'felicity.logging.fileLoggingLevel',
            self::LEVEL_WARNING
        );

        if ($this->enableFileLogging) {
            $this->fileLoggingService->cleanUpLogs();
        }
    }

    /**
     * Gets the Logger class instance
     * @return Logger Singleton
     */
    public static function getInstance(): Logger
    {
        if (! self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Gets a FileLoggingService instance
     * @return FileLoggingService
     */
    public static function fileLoggingService() : FileLoggingService
    {
        return self::getInstance()->getFileLoggingService();
    }

    /**
     * Gets a FileLoggingService instance
     * @return FileLoggingService
     */
    public function getFileLoggingService() : FileLoggingService
    {
        return $this->fileLoggingService;
    }

    /**
     * Adds a log
     * @param string|mixed $msg
     * @param int $level
     * @param string $category
     */
    public static function log(
        $msg,
        int $level,
        string $category = 'application'
    ) {
        self::getInstance()->addLog($msg, $level, $category);
    }

    /**
     * Adds a log
     * @param string|mixed $msg
     * @param int $level
     * @param string $category
     */
    public function addLog(
        $msg,
        int $level,
        string $category = 'application'
    ) {
        $this->instanceLogs->addModel(new LogModel([
            'message' => $msg,
            'level' => $level,
            'category' => $category,
        ]));

        if (! $this->enableFileLogging) {
            return;
        }

        $levelString = self::$levelStringMap[$level] ?? $level;

        if (! $this->enableFileLogging || $level > $this->fileLogLevel) {
            return;
        }

        $this->fileLoggingService->writeToFileLog($msg, $levelString, $category);
    }

    /**
     * Gets the logs
     * @return ModelCollection
     */
    public static function logs() : ModelCollection
    {
        return self::getInstance()->getLogs();
    }

    /**
     * Gets the logs
     * @return ModelCollection
     */
    public function getLogs() : ModelCollection
    {
        return $this->instanceLogs;
    }

    /**
     * Gets the float time
     * @return float
     */
    public static function startTime() : float
    {
        return self::getInstance()->startTime;
    }

    /**
     * Gets the float time
     * @return float
     */
    public function getStartTime() : float
    {
        return $this->startTime;
    }
}
