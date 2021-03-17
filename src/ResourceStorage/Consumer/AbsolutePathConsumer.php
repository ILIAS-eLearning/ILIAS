<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Consumer;

/**
 * Class AbsolutePathConsumer
 * @package ILIAS\ResourceStorage\Consumer
 */
class AbsolutePathConsumer extends BaseConsumer
{
    protected $absolute_path = '';

    public function getAbsolutePath() : string
    {
        $this->run();
        return $this->absolute_path;
    }

    public function run() : void
    {
        $revision = $this->getRevision();

        $stream = $this->storage_handler->getStream($revision);

        $this->absolute_path = (string) $stream->getMetadata('uri');
    }

}
