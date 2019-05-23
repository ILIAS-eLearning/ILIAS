<?php

namespace SimpleBus\Message\Logging;

use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;

class LoggingMiddleware implements MessageBusMiddleware
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $level;

    public function __construct(LoggerInterface $logger, $level)
    {
        $this->logger = $logger;
        $this->level = $level;
    }

    public function handle($message, callable $next)
    {
        $this->logger->log($this->level, 'Started handling a message', ['message' => $message]);

        $next($message);

        $this->logger->log($this->level, 'Finished handling a message', ['message' => $message]);
    }
}
