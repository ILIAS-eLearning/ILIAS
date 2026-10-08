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

namespace ILIAS\Forum\Files\Access;

use ilFileDataForumInterface;

/**
 * Adapts the forum file storage to the narrow delivery port.
 */
final class ForumFileStorageDelivery implements AddressedAttachmentDelivery
{
    public function __construct(private readonly ilFileDataForumInterface $storage)
    {
    }

    public function forumObjId(): int
    {
        return $this->storage->getObjId();
    }

    public function containerId(): int
    {
        return $this->storage->getPosId();
    }

    public function deliverFile(string $revision_token): void
    {
        $this->storage->deliverFile($revision_token);
    }

    public function deliverZipFile(): bool
    {
        return $this->storage->deliverZipFile();
    }
}
