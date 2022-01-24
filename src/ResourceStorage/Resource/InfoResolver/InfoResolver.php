<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Resource\InfoResolver;

use DateTimeImmutable;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
