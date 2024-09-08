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

/**
 * Portfolio
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjPortfolio extends ilObjPortfolioBase implements ilAdvancedMetaDataSubItems
{
    protected function initType(): void
    {
        $this->type = "prtf";
    }


    protected function deleteAllPages(): void
    {
        // delete pages
        $pages = ilPortfolioPage::getAllPortfolioPages($this->id);
        foreach ($pages as $page) {
            $page_obj = new ilPortfolioPage($page["id"]);
            $page_obj->setPortfolioId($this->id);
            $page_obj->delete();
        }
    }


    //
    // HELPER
    //


    /**
     * Get portfolios of user
     * @return array[]
     */
    public static function getPortfoliosOfUser(
        int $a_user_id
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT up.*,od.title,od.description" .
            " FROM usr_portfolio up" .
            " JOIN object_data od ON (up.id = od.obj_id)" .
            " WHERE od.owner = " . $ilDB->quote($a_user_id, "integer") .
            " AND od.type = " . $ilDB->quote("prtf", "text") .
            " ORDER BY od.title");
        $res = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $res[] = $rec;
        }
        return $res;
    }

    /**
     * @deprecated
     */
    public static function getDefaultPortfolio(int $a_user_id): ?int
    {
        return null;
    }

    /**
     * Delete all portfolio data for user
     */
    public static function deleteUserPortfolios(int $a_user_id): void
    {
        $all = self::getPortfoliosOfUser($a_user_id);
        if ($all) {
            $access_handler = new ilPortfolioAccessHandler();

            foreach ($all as $item) {
                $access_handler->removePermission($item["id"]);

                $portfolio = new self($item["id"], false);
                $portfolio->delete();
            }
        }
    }

    public function deleteImage(): void
    {
        if ($this->id) {
            parent::deleteImage();
            $this->handleQuotaUpdate();
        }
    }

    public function uploadImage(array $a_upload): bool
    {
        if (parent::uploadImage($a_upload)) {
            $this->handleQuotaUpdate();
            return true;
        }
        return false;
    }

    protected function handleQuotaUpdate(): void
    {
    }

    public static function getAvailablePortfolioLinksForUserIds(
        array $a_owner_ids,
        ?string $a_back_url = null
    ): array {
        $res = array();

        $access_handler = new ilPortfolioAccessHandler();

        $params = null;
        if ($a_back_url) {
            $params = array("back_url" => rawurlencode($a_back_url));
        }

        foreach ($access_handler->getShardObjectsDataForUserIds($a_owner_ids) as $owner_id => $items) {
            foreach ($items as $id => $title) {
                $url = ilLink::_getLink($id, 'prtf', $params);
                $res[$owner_id][$url] = $title;
            }
        }

        return $res;
    }

    /**
     * Is export possible
     */
    public function isCommentsExportPossible(): bool
    {
        $setting = $this->setting;
        $privacy = ilPrivacySettings::getInstance();
        if ($setting->get("disable_comments")) {
            return false;
        }
        if (!$this->notes->domain()->commentsActive($this->id)) {
            return false;
        }
        if (!$privacy->enabledCommentsExport()) {
            return false;
        }
        return true;
    }

    public static function getAdvMDSubItemTitle(int $a_obj_id, string $a_sub_type, int $a_sub_id): string
    {
        return \ilPortfolioPage::lookupTitle($a_sub_id);
    }
}
