<?php

namespace SimpleBus\Message\Recorder;

interface ContainsRecordedMessages
{
    /**
     * Fetch recorded messages.
     *
     * @return object[]
     */
    public function recordedMessages();

    /**
     * Erase messages that were recorded since the last call to eraseMessages().
     *
     * @return void
     */
    public function eraseMessages();
}
