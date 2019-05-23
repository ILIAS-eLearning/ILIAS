<?php

namespace SimpleBus\Message\Recorder;

class PublicMessageRecorder implements RecordsMessages
{
    use PrivateMessageRecorderCapabilities { record as public; }
}
