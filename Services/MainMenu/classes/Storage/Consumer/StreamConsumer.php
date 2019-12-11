<?php

namespace ILIAS\MainMenu\Storage\Consumer;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;

/**
 * Interface StreamConsumer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface StreamConsumer
{

    /**
     * @return FileStream
     */
    public function getStream() : FileStream;
}