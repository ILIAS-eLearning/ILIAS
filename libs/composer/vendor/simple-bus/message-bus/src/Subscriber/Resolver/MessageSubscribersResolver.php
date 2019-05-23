<?php

namespace SimpleBus\Message\Subscriber\Resolver;

interface MessageSubscribersResolver
{
    /**
     * Resolve the message subscriber callables that should be notified
     *
     * @param object $message
     * @return callable[]
     */
    public function resolve($message);
}
