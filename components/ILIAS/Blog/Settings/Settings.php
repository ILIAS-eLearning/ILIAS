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

namespace ILIAS\Blog\Settings;

class Settings
{
    public function __construct(
        protected int $id,
        protected bool $profile_picture,
        protected string $bg_color,
        protected string $font_color,
        protected bool $rss_active,
        protected bool $approval,
        protected bool $abs_shorten,
        protected int $abs_shorten_len,
        protected bool $abs_image,
        protected int $abs_img_width,
        protected int $abs_img_height,
        protected bool $keywords,
        protected bool $authors,
        protected int $nav_mode,
        protected int $nav_list_mon_with_post,
        protected int $nav_list_mon,
        protected int $ov_post,
        protected array $nav_order
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function withId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function getProfilePicture(): bool
    {
        return $this->profile_picture;
    }

    public function getBackgroundColor(): string
    {
        return $this->bg_color;
    }

    public function getFontColor(): string
    {
        return $this->font_color;
    }

    public function getRSS(): bool
    {
        return $this->rss_active;
    }

    public function getApproval(): bool
    {
        return $this->approval;
    }

    public function getAbstractShorten(): bool
    {
        return $this->abs_shorten;
    }

    public function getAbstractShortenLength(): int
    {
        return $this->abs_shorten_len;
    }

    public function getAbstractImage(): bool
    {
        return $this->abs_image;
    }

    public function getAbstractImageWidth(): int
    {
        return $this->abs_img_width;
    }

    public function getAbstractImageHeight(): int
    {
        return $this->abs_img_height;
    }

    public function getKeywords(): bool
    {
        return $this->keywords;
    }

    public function getAuthors(): bool
    {
        return $this->authors;
    }

    public function getNavMode(): int
    {
        return $this->nav_mode;
    }

    public function getNavModeListMonthsWithPostings(): int
    {
        return $this->nav_list_mon_with_post;
    }

    public function getNavModeListMonths(): int
    {
        return $this->nav_list_mon;
    }

    public function getOverviewPostings(): int
    {
        return $this->ov_post;
    }

    public function getOrder(): array
    {
        return $this->nav_order;
    }
}
