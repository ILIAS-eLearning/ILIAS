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

namespace ILIAS\MediaCast;

use ILIAS\MediaCast\Settings\Settings;

class InternalDataService
{
    public function __construct()
    {
    }

    public function settings(
        int $id,
        bool $public_files,
        bool $downloadable,
        int $default_access,
        int $sort_mode,
        string $view_mode,
        bool $autoplay_mode,
        int $nr_initial_videos,
        bool $new_items_in_lp
    ): Settings {
        return new Settings(
            $id,
            $public_files,
            $downloadable,
            $default_access,
            $sort_mode,
            $view_mode,
            $autoplay_mode,
            $nr_initial_videos,
            $new_items_in_lp
        );
    }
}
