<?php

namespace SimpleBus\Message\Name;

interface NamedMessage
{
    /**
     * The name of this particular type of message.
     *
     * @return string
     */
    public static function messageName();
}
