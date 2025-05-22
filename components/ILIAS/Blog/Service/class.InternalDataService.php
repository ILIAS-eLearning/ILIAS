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

namespace ILIAS\Blog;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDataService
{
    // protected ...\DataFactory ..._factory;

    public function __construct()
    {
        //$this->..._factory = new ...\DataFactory();
    }

    public function settings(
        int $id,
        bool $profile_picture,
        string $bg_color,
        string $font_color,
        bool $rss_active,
        bool $approval,
        bool $abs_shorten,
        int $abs_shorten_len,
        bool $abs_image,
        int $abs_img_width,
        int $abs_img_height,
        bool $keywords,
        bool $authors,
        int $nav_mode,
        int $nav_list_mon_with_post,
        int $nav_list_mon,
        int $ov_post,
        array $nav_order = []
    ): Settings\Settings {
        return new Settings\Settings(
            $id,
            $profile_picture,
            $bg_color,
            $font_color,
            $rss_active,
            $approval,
            $abs_shorten,
            $abs_shorten_len,
            $abs_image,
            $abs_img_width,
            $abs_img_height,
            $keywords,
            $authors,
            $nav_mode,
            $nav_list_mon_with_post,
            $nav_list_mon,
            $ov_post,
            $nav_order
        );
    }
}
