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
 * Wiki page
 */
class Page
{
    protected int $id;
    protected int $wiki_id;
    protected string $title;
    protected string $lang = "-";
    protected bool $blocked = false;
    protected bool $rating = false;
    protected bool $hide_adv_md = false;

    public function __construct(
        int $id,
        int $wiki_id,
        string $title,
        string $lang = "-",
        bool $blocked = false,
        bool $rating = false,
        bool $hide_adv_md = false
    ) {
        $this->id = $id;
        $this->wiki_id = $wiki_id;
        $this->title = $title;
        $this->lang = $lang;
        $this->blocked = $blocked;
        $this->rating = $rating;
        $this->hide_adv_md = $hide_adv_md;
    }

    public function getId(): int
    {
        return $this->id;
    }
    public function getWikiId(): int
    {
        return $this->wiki_id;
    }
    public function getTitle(): string
    {
        return $this->title;
    }
    public function getLanguage(): string
    {
        return $this->lang;
    }
    public function getBlocked(): bool
    {
        return $this->blocked;
    }
    public function getRating(): bool
    {
        return $this->rating;
    }
    public function getHideAdvMetadata(): bool
    {
        return $this->hide_adv_md;
    }

}
