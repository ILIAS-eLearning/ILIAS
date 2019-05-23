<?php

namespace SimpleBus\Message\Name;

interface MessageNameResolver
{
    /**
     * Resolve the unique name of a message, e.g. the full class name
     *
     * @param object $message
     * @return string
     */
    public function resolve($message);
}
