<?php

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
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Consumer\StreamAccess\StreamAccess;
use ILIAS\ResourceStorage\Resource\StorableResource;

/**
 * Class FileStreamConsumer
 * @package ILIAS\ResourceStorage\Consumer
 */
class FileStreamConsumer implements StreamConsumer
{
    use GetRevisionTrait;

    protected ?int $revision_number = null;
    private StorableResource $resource;
    private StreamAccess $stream_access;

    /**
     * DownloadConsumer constructor.
     */
    public function __construct(StorableResource $resource, StreamAccess $stream_access)
    {
        $this->resource = $resource;
        $this->stream_access = $stream_access;
    }

    public function getStream(): FileStream
    {
        $revision = $this->stream_access->populateRevision($this->getRevision());

        return $revision->maybeGetToken()->resolveStream();
    }

    /**
     * @inheritDoc
     */
    public function setRevisionNumber(int $revision_number): self
    {
        $this->revision_number = $revision_number;
        return $this;
    }
}
