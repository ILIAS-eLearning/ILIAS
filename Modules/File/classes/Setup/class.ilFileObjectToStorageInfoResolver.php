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
    protected ?DateTimeImmutable $creation_date;

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
