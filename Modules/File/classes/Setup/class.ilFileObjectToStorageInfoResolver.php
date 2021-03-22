<?php

use ILIAS\ResourceStorage\Resource\InfoResolver\StreamInfoResolver;
use ILIAS\Filesystem\Stream\FileStream;

/**
 * Class ilFileObjectToStorageInfoResolver
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilFileObjectToStorageInfoResolver extends StreamInfoResolver
{
    /**
     * @var DateTimeImmutable
     */
    protected $creation_date;

    public function __construct(
        FileStream $stream,
        int $next_version_number,
        int $revision_owner_id,
        string $revision_title,
        DateTimeImmutable $creation_date
    ) {
        parent::__construct($stream, $next_version_number, $revision_owner_id, $revision_title);
        $this->creation_date = $creation_date;
    }

}
