<?php

namespace SimpleBus\Message\Recorder;

interface RecordsMessages extends ContainsRecordedMessages
{
    /**
     * Record a message.
     *
     * @param object $message
     */
    public function record($message);
}
