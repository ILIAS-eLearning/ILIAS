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

/**
 * Binds a draft to the forum object the request was routed to and to its author,
 * since drafts are never visible to anyone but their author.
 */
final class DraftAttachmentGuard implements AttachmentAccessGuard
{
    public function __construct(
        private readonly OwnershipRepository $repository,
        private readonly int $acting_usr_id
    ) {
    }

    public function decide(int $routed_obj_id, int $container_id): AccessDecision
    {
        if ($routed_obj_id <= 0 || $container_id <= 0) {
            return AccessDecision::denied(DenialReason::INCOMPLETE_SELECTORS);
        }

        $ownership = $this->repository->findDraftOwnership($container_id);
        if ($ownership === null) {
            return AccessDecision::denied(DenialReason::UNKNOWN_CONTAINER);
        }

        if (!$ownership->isOwnedByForumObject($routed_obj_id)) {
            return AccessDecision::denied(DenialReason::FOREIGN_FORUM);
        }

        if (!$ownership->isAuthoredBy($this->acting_usr_id)) {
            return AccessDecision::denied(DenialReason::FOREIGN_AUTHOR);
        }

        return AccessDecision::granted();
    }
}
