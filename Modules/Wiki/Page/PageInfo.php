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

namespace ILIAS\Wiki\Page;

/**
 * Wiki page info
 */
class PageInfo
{
    protected int $old_nr;
    protected int $id;
    protected string $title;
    protected string $lang = "-";
    protected int $last_change_user;
    protected string $last_change;
    protected int $create_user;
    protected int $view_cnt;
    protected string $created;

    public function __construct(
        int $id,
        string $lang,
        string $title,
        int $last_change_user,
        string $last_change,
        int $create_user,
        string $created,
        int $view_cnt,
        int $old_nr
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->lang = $lang;
        $this->last_change_user = $last_change_user;
        $this->last_change = $last_change;
        $this->create_user = $create_user;
        $this->created = $created;
        $this->view_cnt = $view_cnt;
        $this->old_nr = $old_nr;
    }

    public function getId(): int
    {
        return $this->id;
    }
    public function getTitle(): string
    {
        return $this->title;
    }
    public function getLanguage(): string
    {
        return $this->lang;
    }
    public function getLastChangedUser(): int
    {
        return $this->last_change_user;
    }
    public function getLastChange(): string
    {
        return $this->last_change;
    }
    public function getCreateUser(): int
    {
        return $this->create_user;
    }
    public function getCreated(): string
    {
        return $this->created;
    }
    public function getViewCnt(): int
    {
        return $this->view_cnt;
    }

    public function getOldNr(): int
    {
        return $this->old_nr;
    }
}
