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
 * Binds a posting to the forum object (and optionally to the thread) the request was routed to.
 */
final class PostingAttachmentGuard implements AttachmentAccessGuard
{
    public function __construct(
        private readonly OwnershipRepository $repository,
        private readonly ?int $routed_thread_id = null
    ) {
    }

    public function decide(int $routed_obj_id, int $container_id): AccessDecision
    {
        if ($routed_obj_id <= 0 || $container_id <= 0) {
            return AccessDecision::denied(DenialReason::INCOMPLETE_SELECTORS);
        }

        $ownership = $this->repository->findPostingOwnership($container_id);
        if ($ownership === null) {
            return AccessDecision::denied(DenialReason::UNKNOWN_CONTAINER);
        }

        if (!$ownership->isOwnedByForumObject($routed_obj_id)) {
            return AccessDecision::denied(DenialReason::FOREIGN_FORUM);
        }

        if ($this->routed_thread_id !== null && !$ownership->isPartOfThread($this->routed_thread_id)) {
            return AccessDecision::denied(DenialReason::FOREIGN_THREAD);
        }

        return AccessDecision::granted();
    }
}
