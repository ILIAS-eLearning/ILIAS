<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Resource\InfoResolver;

use DateTimeImmutable;
use ILIAS\ResourceStorage\Revision\FileRevision;

/**
 * Class ClonedRevisionInfoResolver
 * @package ILIAS\ResourceStorage\Resource\InfoResolver
 * @internal
 */
class ClonedRevisionInfoResolver implements InfoResolver
{
    /**
     * @var int
     */
    protected $next_version_number;
    /**
     * @var FileRevision
     */
    protected $existing_revision;
    /**
     * @var \ILIAS\ResourceStorage\Information\Information
     */
    protected $info;

    /**
     * ClonedRevisionInfoResolver constructor.
     * @param int          $next_version_number
     * @param FileRevision $existing_revision
     */
    public function __construct(int $next_version_number, FileRevision $existing_revision)
    {
        $this->next_version_number = $next_version_number;
        $this->existing_revision = $existing_revision;
        $this->info = $existing_revision->getInformation();
    }

    public function getNextVersionNumber() : int
    {
        return $this->next_version_number;
    }

    public function getOwnerId() : int
    {
        return $this->existing_revision->getOwnerId() ?? 6;
    }

    public function getRevisionTitle() : string
    {
        return $this->existing_revision->getTitle();
    }

    public function getFileName() : string
    {
        return $this->info->getTitle();
    }

    public function getMimeType() : string
    {
        return $this->info->getMimeType();
    }

    public function getSuffix() : string
    {
        return $this->info->getSuffix();
    }

    public function getCreationDate() : DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public function getSize() : int
    {
        return $this->info->getSize();
    }

}
