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

use ilDBInterface;
use ILIAS\Blog\InternalDataService;

class SettingsDBRepository
{
    public function __construct(
        protected ilDBInterface $db,
        protected InternalDataService $data
    ) {
    }

    protected function getSettingsFromRecord(array $rec): Settings
    {
        $order = explode(';', $rec['nav_order'] ?? "");
        if (count($order) === 1 && current($order) === "") {
            $order = [];
        }
        return $this->data->settings(
            (int) $rec['id'],
            (bool) $rec['ppic'],
            (string) $rec['bg_color'],
            (string) $rec['font_color'],
            (bool) $rec['rss_active'],
            (bool) $rec['approval'],
            (bool) $rec['abs_shorten'],
            (int) $rec['abs_shorten_len'],
            (bool) $rec['abs_image'],
            (int) $rec['abs_img_width'],
            (int) $rec['abs_img_height'],
            (bool) $rec['keywords'],
            (bool) $rec['authors'],
            (int) $rec['nav_mode'],
            (int) $rec['nav_list_mon_with_post'],
            (int) $rec['nav_list_mon'],
            (int) $rec['ov_post'],
            $order
        );
    }

    public function create(Settings $setting): void
    {
        $this->db->insert("il_blog", [
            "id" => ["integer", $setting->getId()],
            "ppic" => ["integer", $setting->getProfilePicture()],
            "bg_color" => ["text", $setting->getBackgroundColor()],
            "font_color" => ["text", $setting->getFontColor()],
            "rss_active" => ["integer", $setting->getRSS()],
            "approval" => ["integer", $setting->getApproval()],
            "abs_shorten" => ["integer", $setting->getAbstractShorten()],
            "abs_shorten_len" => ["integer", $setting->getAbstractShortenLength()],
            "abs_image" => ["integer", $setting->getAbstractImage()],
            "abs_img_width" => ["integer", $setting->getAbstractImageWidth()],
            "abs_img_height" => ["integer", $setting->getAbstractImageHeight()],
            "keywords" => ["integer", $setting->getKeywords()],
            "authors" => ["integer", $setting->getAuthors()],
            "nav_mode" => ["integer", $setting->getNavMode()],
            "nav_list_mon_with_post" => ["integer", $setting->getNavModeListMonthsWithPostings()],
            "nav_list_mon" => ["integer", $setting->getNavModeListMonths()],
            "ov_post" => ["integer", $setting->getOverviewPostings()],
            "nav_order" => ["text", implode(';', $setting->getOrder())]
        ]);
    }

    public function update(Settings $setting): void
    {
        $this->db->update("il_blog", [
            "ppic" => ["integer", $setting->getProfilePicture()],
            "bg_color" => ["text", $setting->getBackgroundColor()],
            "font_color" => ["text", $setting->getFontColor()],
            "rss_active" => ["integer", $setting->getRSS()],
            "approval" => ["integer", $setting->getApproval()],
            "abs_shorten" => ["integer", $setting->getAbstractShorten()],
            "abs_shorten_len" => ["integer", $setting->getAbstractShortenLength()],
            "abs_image" => ["integer", $setting->getAbstractImage()],
            "abs_img_width" => ["integer", $setting->getAbstractImageWidth()],
            "abs_img_height" => ["integer", $setting->getAbstractImageHeight()],
            "keywords" => ["integer", $setting->getKeywords()],
            "authors" => ["integer", $setting->getAuthors()],
            "nav_mode" => ["integer", $setting->getNavMode()],
            "nav_list_mon_with_post" => ["integer", $setting->getNavModeListMonthsWithPostings()],
            "nav_list_mon" => ["integer", $setting->getNavModeListMonths()],
            "ov_post" => ["integer", $setting->getOverviewPostings()],
            "nav_order" => ["text", implode(';', $setting->getOrder())]
        ], [
            "id" => ["integer", $setting->getId() ?? 0]
        ]);
    }

    public function getByObjId(int $id): ?Settings
    {
        $set = $this->db->queryF(
            "SELECT * FROM il_blog WHERE id = %s",
            ["integer"],
            [$id]
        );

        $rec = $this->db->fetchAssoc($set);
        if ($rec !== false) {
            return $this->getSettingsFromRecord($rec);
        }
        return null;
    }

    public function saveOrder(int $id, array $order): void
    {
        $this->db->update(
            "il_blog",
            [
            "nav_order" => ["", implode(';', $order)]
        ],
            [
                "id" => ["integer", $id]
            ]
        );
    }

}
