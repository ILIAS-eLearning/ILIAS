<?php

namespace SimpleBus\Message\Subscriber\Resolver;

use SimpleBus\Message\CallableResolver\CallableCollection;
use SimpleBus\Message\Name\MessageNameResolver;

class NameBasedMessageSubscriberResolver implements MessageSubscribersResolver
{
    /**
     * @var MessageNameResolver
     */
    private $messageNameResolver;

    /**
     * @var CallableCollection
     */
    private $messageSubscribers;

    public function __construct(MessageNameResolver $messageNameResolver, CallableCollection $messageSubscribers)
    {
        $this->messageNameResolver = $messageNameResolver;
        $this->messageSubscribers = $messageSubscribers;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($message)
    {
        $name = $this->messageNameResolver->resolve($message);

        return $this->messageSubscribers->filter($name);
    }
}
