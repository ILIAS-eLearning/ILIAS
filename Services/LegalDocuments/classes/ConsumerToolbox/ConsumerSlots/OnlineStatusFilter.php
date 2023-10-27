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

namespace ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots;

use ilDBInterface;
use ilDBConstants;
use ilRbacReview;
use ilObjUser;
use Closure;

final class OnlineStatusFilter
{
    /**
     * @param Closure(list<int>): list<int> $select_didnt_agree
     */
    public function __construct(
        private readonly Closure $select_didnt_agree,
        private readonly ilRbacReview $review
    ) {
    }

    /**
     * @param list<int> $users
     * @return list<int>
     */
    public function __invoke(array $users): array
    {
        $did_not_agreed = array_filter(
            ($this->select_didnt_agree)($users),
            fn(int $user) => $user !== SYSTEM_USER_ID && !$this->review->isAssigned($user, SYSTEM_ROLE_ID)
        );

        return array_values(array_diff($users, $did_not_agreed));
    }
}
