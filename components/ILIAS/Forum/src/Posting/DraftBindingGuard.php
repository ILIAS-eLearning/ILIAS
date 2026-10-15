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

namespace ILIAS\Forum\Posting;

/**
 * Binds a draft addressed by a publish or save action to the forum object
 * of the current request and to its author.
 *
 * A reply draft must also belong to the routed thread and posting.
 * A draft for a new thread must not belong to a thread.
 */
final class DraftBindingGuard
{
    public function __construct(
        private readonly DraftPlacementRepository $drafts,
        private readonly int $acting_usr_id
    ) {
    }

    public function decideReply(
        int $routed_obj_id,
        int $thread_id,
        int $posting_id,
        int $draft_id
    ): BindingDecision {
        if ($thread_id <= 0 || $posting_id <= 0) {
            return BindingDecision::denied(BindingDenial::INCOMPLETE_SELECTORS);
        }

        $draft = $this->authoredDraft($routed_obj_id, $draft_id);
        if ($draft instanceof BindingDecision) {
            return $draft;
        }

        if (!$draft->belongsToThread($thread_id)) {
            return BindingDecision::denied(BindingDenial::FOREIGN_THREAD);
        }

        if (!$draft->repliesTo($posting_id)) {
            return BindingDecision::denied(BindingDenial::FOREIGN_POSTING);
        }

        return BindingDecision::granted();
    }

    public function decideUnboundThread(int $routed_obj_id, int $draft_id): BindingDecision
    {
        $draft = $this->authoredDraft($routed_obj_id, $draft_id);
        if ($draft instanceof BindingDecision) {
            return $draft;
        }

        if ($draft->isBoundToThread()) {
            return BindingDecision::denied(BindingDenial::BOUND_TO_THREAD);
        }

        return BindingDecision::granted();
    }

    private function authoredDraft(int $routed_obj_id, int $draft_id): DraftPlacement|BindingDecision
    {
        if ($routed_obj_id <= 0 || $draft_id <= 0) {
            return BindingDecision::denied(BindingDenial::INCOMPLETE_SELECTORS);
        }

        $draft = $this->drafts->findDraft($draft_id);
        if ($draft === null) {
            return BindingDecision::denied(BindingDenial::UNKNOWN_DRAFT);
        }

        if (!$draft->belongsToForumObject($routed_obj_id)) {
            return BindingDecision::denied(BindingDenial::FOREIGN_FORUM);
        }

        if (!$draft->isAuthoredBy($this->acting_usr_id)) {
            return BindingDecision::denied(BindingDenial::FOREIGN_AUTHOR);
        }

        return $draft;
    }
}
