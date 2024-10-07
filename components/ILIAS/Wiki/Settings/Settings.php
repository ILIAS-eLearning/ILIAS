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

namespace ILIAS\Wiki\Settings;

class Settings
{
    public function __construct(
        protected int $id,
        protected string $startpage,
        protected string $short_title,
        protected bool $rating_overall,
        protected bool $rating,
        protected bool $rating_as_block,
        protected bool $rating_for_new_pages,
        protected bool $rating_categories,
        protected bool $public_notes,
        protected string $introduction,
        protected bool $page_toc,
        protected bool $link_metadata_values,
        protected bool $empty_page_template
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStartPage(): string
    {
        return $this->startpage;
    }

    public function getShortTitle(): string
    {
        return $this->short_title;
    }

    public function getRatingOverall(): bool
    {
        return $this->rating_overall;
    }

    public function getRating(): bool
    {
        return $this->rating;
    }

    public function getRatingAsBlock(): bool
    {
        return $this->rating_as_block;
    }

    public function getRatingForNewPages(): bool
    {
        return $this->rating_for_new_pages;
    }

    public function getRatingCategories(): bool
    {
        return $this->rating_categories;
    }

    public function getPublicNotes(): bool
    {
        return $this->public_notes;
    }

    public function getIntroduction(): string
    {
        return $this->introduction;
    }

    public function getPageToc(): bool
    {
        return $this->page_toc;
    }

    public function getLinkMetadataValues(): bool
    {
        return $this->link_metadata_values;
    }

    public function getEmptyPageTemplate(): bool
    {
        return $this->empty_page_template;
    }
}
