<?php

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\Filesystem\Stream\FileStream;

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
