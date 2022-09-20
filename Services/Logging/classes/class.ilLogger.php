<?php

declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once __DIR__ . '/../../../libs/composer/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Processor\MemoryPeakUsageProcessor;

/**
 * Component logger with individual log levels by component id
 * @author Stefan Meyer
 */
abstract class ilLogger
{
    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Check whether current logger is handling a log level
     */
    public function isHandling(int $a_level): bool
    {
        return $this->getLogger()->isHandling($a_level);
    }

    public function log(string $a_message, int $a_level = ilLogLevel::INFO): void
    {
        $this->getLogger()->log($a_level, $a_message);
    }

    /**
     * @param  mixed $a_variable
     * @param int    $a_level
     * @return void
     */
    public function dump($a_variable, int $a_level = ilLogLevel::INFO): void
    {
        $this->log((string) print_r($a_variable, true), $a_level);
    }

    public function debug(string $a_message, array $a_context = array()): void
    {
        $this->getLogger()->debug($a_message, $a_context);
    }

    public function info(string $a_message): void
    {
        $this->getLogger()->info($a_message);
    }

    public function notice(string $a_message): void
    {
        $this->getLogger()->notice($a_message);
    }

    public function warning(string $a_message): void
    {
        $this->getLogger()->warning($a_message);
    }

    public function error(string $a_message): void
    {
        $this->getLogger()->error($a_message);
    }

    public function critical(string $a_message): void
    {
        $this->getLogger()->critical($a_message);
    }

    public function alert(string $a_message): void
    {
        $this->getLogger()->alert($a_message);
    }


    public function emergency(string $a_message): void
    {
        $this->getLogger()->emergency($a_message);
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * write log message
     * @deprecated since version 5.1
     * @see ilLogger->info(), ilLogger()->debug(), ...
     *
     * @param int $_level
     */
    public function write(string $a_message, $a_level = ilLogLevel::INFO): void
    {
        if (!in_array($a_level, ilLogLevel::getLevels())) {
            $a_level = ilLogLevel::INFO;
        }
        $this->getLogger()->log((int) $a_level, $a_message);
    }

    /**
     * Write language log
     * @deprecated since version 5.1
     */
    public function writeLanguageLog(string $a_topic, string $a_lang_key): void
    {
        $this->getLogger()->debug("Language (" . $a_lang_key . "): topic -" . $a_topic . "- not present");
    }

    public function logStack(?int $a_level = null, string $a_message = ''): void
    {
        if (is_null($a_level)) {
            $a_level = ilLogLevel::INFO;
        }

        if (!in_array($a_level, ilLogLevel::getLevels())) {
            $a_level = ilLogLevel::INFO;
        }


        try {
            throw new \Exception($a_message);
        } catch (Exception $ex) {
            $this->getLogger()->log($a_level, $a_message . "\n" . $ex->getTraceAsString());
        }
    }

    /**
     * Write memory peak usage
     * Automatically called at end of script
     */
    public function writeMemoryPeakUsage(int $a_level): void
    {
        $this->getLogger()->pushProcessor(new MemoryPeakUsageProcessor());
        $this->getLogger()->log($a_level, 'Memory usage: ');
        $this->getLogger()->popProcessor();
    }
}
