<?php

namespace SimpleBus\Message\Subscriber;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;
use SimpleBus\Message\Subscriber\Resolver\MessageSubscribersResolver;

class NotifiesMessageSubscribersMiddleware implements MessageBusMiddleware
{
    /**
     * @var MessageSubscribersResolver
     */
    private $messageSubscribersResolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $level;

    public function __construct(
        MessageSubscribersResolver $messageSubscribersResolver,
        LoggerInterface $logger = null,
        $level = LogLevel::DEBUG
    ) {
        $this->messageSubscribersResolver = $messageSubscribersResolver;

        if (null === $logger) {
            $logger = new NullLogger;
        }

        $this->logger = $logger;
        $this->level = $level;
    }

    public function handle($message, callable $next)
    {
        $messageSubscribers = $this->messageSubscribersResolver->resolve($message);

        foreach ($messageSubscribers as $messageSubscriber) {
            $this->logger->log($this->level, 'Started notifying a subscriber', ['subscriber' => $messageSubscriber]);

            call_user_func($messageSubscriber, $message);

            $this->logger->log($this->level, 'Finished notifying a subscriber', ['subscriber' => $messageSubscriber]);
        }

        $next($message);
    }
}
