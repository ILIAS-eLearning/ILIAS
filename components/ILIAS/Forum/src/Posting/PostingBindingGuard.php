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
 * Binds a posting addressed by a reply or a draft action to the forum object
 * and the thread of the current request.
 */
final class PostingBindingGuard
{
    public function __construct(private readonly PostingPlacementRepository $postings)
    {
    }

    public function decide(int $routed_obj_id, int $thread_id, int $posting_id): BindingDecision
    {
        if ($routed_obj_id <= 0 || $thread_id <= 0 || $posting_id <= 0) {
            return BindingDecision::denied(BindingDenial::INCOMPLETE_SELECTORS);
        }

        $posting = $this->postings->findPosting($posting_id);
        if ($posting === null) {
            return BindingDecision::denied(BindingDenial::UNKNOWN_POSTING);
        }

        if (!$posting->belongsToForumObject($routed_obj_id)) {
            return BindingDecision::denied(BindingDenial::FOREIGN_FORUM);
        }

        if (!$posting->belongsToThread($thread_id)) {
            return BindingDecision::denied(BindingDenial::FOREIGN_THREAD);
        }

        return BindingDecision::granted();
    }
}
