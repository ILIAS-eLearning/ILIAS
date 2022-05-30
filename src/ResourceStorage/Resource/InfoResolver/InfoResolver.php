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
