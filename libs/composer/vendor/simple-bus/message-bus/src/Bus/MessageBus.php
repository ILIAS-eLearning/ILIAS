<?php

namespace SimpleBus\Message\Bus;

interface MessageBus
{
    /**
     * @param object $message
     * @return void
     */
    public function handle($message);
}
