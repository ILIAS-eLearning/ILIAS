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

namespace ILIAS\MediaCast\Settings;

class Settings
{
    public function __construct(
        protected int $id,
        protected bool $public_files,
        protected bool $downloadable,
        protected int $default_access,
        protected int $sort_mode,
        protected string $view_mode,
        protected bool $autoplay_mode,
        protected int $nr_initial_videos,
        protected bool $new_items_in_lp
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPublicFiles(): bool
    {
        return $this->public_files;
    }

    public function getDownloadable(): bool
    {
        return $this->downloadable;
    }

    public function getDefaultAccess(): int
    {
        return $this->default_access;
    }

    public function getSortMode(): int
    {
        return $this->sort_mode;
    }

    public function getViewMode(): string
    {
        return $this->view_mode;
    }

    public function getAutoplayMode(): bool
    {
        return $this->autoplay_mode;
    }

    public function getNumberInitialVideos(): int
    {
        return $this->nr_initial_videos;
    }

    public function getNewItemsInLearningProgress(): bool
    {
        return $this->new_items_in_lp;
    }
}
