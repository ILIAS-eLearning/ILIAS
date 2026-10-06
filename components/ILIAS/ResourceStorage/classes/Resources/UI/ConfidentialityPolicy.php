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

namespace ILIAS\components\ResourceStorage\Resources\UI;

use ILIAS\ResourceStorage\Resource\StorableResource;

/**
 * Decides whether a confidential resource has to be redacted for the acting user:
 * only the owner of the current revision gets to see file names and download it.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ConfidentialityPolicy
{
    public function __construct(
        private readonly int $acting_user_id
    ) {
    }

    public function isRedacted(StorableResource $resource): bool
    {
        if (!$resource->isConfidential()) {
            return false;
        }

        return $resource->getCurrentRevisionIncludingDraft()->getOwnerId() !== $this->acting_user_id;
    }

    public function getActingUserId(): int
    {
        return $this->acting_user_id;
    }
}
