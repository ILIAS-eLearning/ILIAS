<?php

declare(strict_types=1);

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

namespace ILIAS\Wiki;

use ILIAS\Wiki\Page\Page;
use ILIAS\Wiki\Page\PageInfo;
use ILIAS\Wiki\Navigation\ImportantPage;

/**
 * Wiki internal data service
 */
class InternalDataService
{
    public function __construct()
    {
    }

    public function page(
        int $id,
        int $wiki_id,
        string $title,
        string $lang = "-",
        bool $blocked = false,
        bool $rating = false,
        bool $hide_adv_md = false
    ): Page {
        return new Page(
            $id,
            $wiki_id,
            $title,
            $lang,
            $blocked,
            $rating,
            $hide_adv_md
        );
    }

    public function pageInfo(
        int $id,
        string $lang,
        string $title,
        int $last_change_user,
        string $last_change,
        int $create_user = 0,
        string $created = "",
        int $view_cnt = 0,
        int $old_nr = 0
    ): PageInfo {
        return new PageInfo(
            $id,
            $lang,
            $title,
            $last_change_user,
            $last_change,
            $create_user,
            $created,
            $view_cnt,
            $old_nr
        );
    }

    public function importantPage(
        int $id,
        int $order,
        int $indent
    ): ImportantPage {
        return new ImportantPage(
            $id,
            $order,
            $indent
        );
    }
}
