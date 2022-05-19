<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/
 
namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\StorageHandler\StorageHandler;

/**
 * Class FileStreamConsumer
 * @package ILIAS\ResourceStorage\Consumer
 */
class FileStreamConsumer implements StreamConsumer
{
    use GetRevisionTrait;

    private \ILIAS\ResourceStorage\StorageHandler\StorageHandler $storage_handler;
    private \ILIAS\ResourceStorage\Resource\StorableResource $resource;
    protected ?int $revision_number = null;

    /**
     * DownloadConsumer constructor.
     */
    public function __construct(StorableResource $resource, StorageHandler $storage_handler)
    {
        $this->resource = $resource;
        $this->storage_handler = $storage_handler;
    }

    public function getStream() : FileStream
    {
        $revision = $this->getRevision();

        return $this->storage_handler->getStream($revision);
    }

    /**
     * @inheritDoc
     */
    public function setRevisionNumber(int $revision_number) : self
    {
        $this->revision_number = $revision_number;
        return $this;
    }
}
