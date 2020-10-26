<?php

namespace SAML2\Compat\Ssp;

use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface
{
    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array())
    {
        \SimpleSAML\Logger::emergency($message . var_export($context, true));
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array())
    {
        \SimpleSAML\Logger::alert($message . var_export($context, true));
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array())
    {
        \SimpleSAML\Logger::critical($message . var_export($context, true));
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array())
    {
        \SimpleSAML\Logger::error($message . var_export($context, true));
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array())
    {
        \SimpleSAML\Logger::warning($message . var_export($context, true));
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array())
    {
        \SimpleSAML\Logger::notice($message . var_export($context, true));
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array())
    {
        \SimpleSAML\Logger::info($message . var_export($context, true));
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array())
    {
        \SimpleSAML\Logger::debug($message . var_export($context, true));
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        switch ($level) {
            case \SimpleSAML\Logger::ALERT:
                \SimpleSAML\Logger::alert($message);
                break;
            case \SimpleSAML\Logger::CRIT:
                \SimpleSAML\Logger::critical($message);
                break;
            case \SimpleSAML\Logger::DEBUG:
                \SimpleSAML\Logger::debug($message);
                break;
            case \SimpleSAML\Logger::EMERG:
                \SimpleSAML\Logger::emergency($message);
                break;
            case \SimpleSAML\Logger::ERR:
                \SimpleSAML\Logger::error($message);
                break;
            case \SimpleSAML\Logger::INFO:
                \SimpleSAML\Logger::info($message);
                break;
            case \SimpleSAML\Logger::NOTICE:
                \SimpleSAML\Logger::notice($message);
                break;
            case \SimpleSAML\Logger::WARNING:
                \SimpleSAML\Logger::warning($message);
        }
    }
}
