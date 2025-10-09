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

namespace ILIAS\News\Data;

use DateTimeImmutable;

/**
 * Factory for creating News DTOs from database results (arrays)
 */
class Factory
{
    protected readonly \DateTimeZone $db_timezone;

    public function __construct()
    {
        $this->db_timezone = new \DateTimeZone('UTC');
    }

    public function newsItem(array $row): NewsItem
    {
        return new NewsItem(
            id: (int) $row['id'],
            title: (string) $row['title'],
            content: (string) $row['content'],
            context_obj_id: (int) $row['context_obj_id'],
            context_obj_type: (string) $row['context_obj_type'],
            context_sub_obj_id: (int) $row['context_sub_obj_id'],
            context_sub_obj_type: $row['context_sub_obj_type'] ?? null,
            content_type: (string) $row['content_type'],
            creation_date: new DateTimeImmutable($row['creation_date']), // currently date is stored in server tz, not UTC
            update_date: new DateTimeImmutable($row['update_date']),
            user_id: (int) $row['user_id'],
            update_user_id: (int) $row['update_user_id'],
            visibility: (string) $row['visibility'],
            content_long: (string) $row['content_long'],
            priority: (int) $row['priority'],
            content_is_lang_var: (bool) $row['content_is_lang_var'],
            content_text_is_lang_var: (bool) $row['content_text_is_lang_var'],
            mob_id: (int) $row['mob_id'],
            playtime: (string) $row['playtime'],
            mob_cnt_play: (int) $row['mob_cnt_play'],
            mob_cnt_download: (int) $row['mob_cnt_download'],
            content_html: (bool) $row['content_html'],
            context_ref_id: (int) ($row['ref_id'] ?? 0)
        );
    }
}
