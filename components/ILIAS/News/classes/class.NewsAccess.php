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

namespace ILIAS\News\Access;

class NewsAccess
{
    protected int $current_user_id;
    protected \ilAccessHandler $access;
    protected int $ref_id;

    public function __construct(int $ref_id)
    {
        global $DIC;

        $this->current_user_id = $DIC->user()->getId();
        $this->ref_id = $ref_id;
        $this->access = $DIC->access();
    }

    protected function getUserId(int $user_id = 0): int
    {
        return ($user_id > 0)
            ? $user_id
            : $this->current_user_id;
    }

    public function canAdd($user_id = 0): bool
    {
        return $this->access->checkAccessOfUser(
            $this->getUserId($user_id),
            "news_add_news",
            "",
            $this->ref_id
        )
            || $this->canEditAll($user_id);
    }

    /**
     * List of news of the news block
     */
    public function canAccessManageOverview($user_id = 0): bool
    {
        return $this->canAdd($user_id);
    }

    public function canEditSettings($user_id = 0): bool
    {
        return $this->access->checkAccessOfUser(
            $this->getUserId($user_id),
            'write',
            '',
            $this->ref_id
        );
    }

    public function canEditAll($user_id = 0): bool
    {
        return $this->access->checkAccessOfUser(
            $this->getUserId($user_id),
            'write',
            '',
            $this->ref_id
        );
    }

    public function canEdit(\ilNewsItem $i, $user_id = 0): bool
    {
        return (
            $i->getPriority() === 1 &&
            ($i->getUserId() === $this->getUserId($user_id) || $this->canEditAll($user_id))
        );
    }

    public function canDelete(\ilNewsItem $i, $user_id = 0): bool
    {
        return $this->canEdit($i, $user_id);
    }
}
