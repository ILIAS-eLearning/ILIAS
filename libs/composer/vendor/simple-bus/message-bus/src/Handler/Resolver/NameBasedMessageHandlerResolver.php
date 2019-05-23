<?php

namespace SimpleBus\Message\Handler\Resolver;

use SimpleBus\Message\CallableResolver\CallableCollection;
use SimpleBus\Message\CallableResolver\CallableMap;
use SimpleBus\Message\Name\MessageNameResolver;

class NameBasedMessageHandlerResolver implements MessageHandlerResolver
{
    /**
     * @var MessageNameResolver
     */
    private $messageNameResolver;

    /**
     * @var CallableCollection
     */
    private $messageHandlers;

    public function __construct(MessageNameResolver $messageNameResolver, CallableMap $messageHandlers)
    {
        $this->messageNameResolver = $messageNameResolver;
        $this->messageHandlers = $messageHandlers;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($message)
    {
        $name = $this->messageNameResolver->resolve($message);

        return $this->messageHandlers->get($name);
    }
}
