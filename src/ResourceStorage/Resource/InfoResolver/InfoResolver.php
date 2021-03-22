<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Resource\InfoResolver;

use DateTimeImmutable;

/**
 * Interface InfoResolver
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
interface InfoResolver
{
    public function getNextVersionNumber() : int;

    public function getOwnerId() : int;

    public function getRevisionTitle() : string;

    public function getFileName() : string;

    public function getMimeType() : string;

    public function getSuffix() : string;

    public function getCreationDate() : DateTimeImmutable;

    public function getSize() : int;
}
