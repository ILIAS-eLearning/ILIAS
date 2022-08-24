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
class ilObjPortfolio extends ilObjPortfolioBase
{
    protected bool $default = false;

    protected function initType(): void
    {
        $this->type = "prtf";
    }

    //
    // PROPERTIES
    //

    public function setDefault(bool $a_value): void
    {
        $this->default = $a_value;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }


    //
    // CRUD
    //

    protected function doReadCustom(array $a_row): void
    {
        $this->setDefault((bool) $a_row["is_default"]);
    }

    protected function doUpdate(): void
    {
        // must be online to be default
        if (!$this->isOnline() && $this->isDefault()) {
            $this->setDefault(false);
        }

        parent::doUpdate();
    }

    protected function doUpdateCustom(array &$a_fields): void
    {
        $a_fields["is_default"] = array("integer", $this->isDefault());
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
     * Set the user default portfolio
     */
    public static function setUserDefault(
        int $a_user_id,
        ?int $a_portfolio_id = null
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $all = array();
        foreach (self::getPortfoliosOfUser($a_user_id) as $item) {
            $all[] = $item["id"];
        }
        if ($all) {
            $ilDB->manipulate("UPDATE usr_portfolio" .
                " SET is_default = " . $ilDB->quote(false, "integer") .
                " WHERE " . $ilDB->in("id", $all, "", "integer"));
        }

        if ($a_portfolio_id) {
            $ilDB->manipulate("UPDATE usr_portfolio" .
                " SET is_default = " . $ilDB->quote(true, "integer") .
                " WHERE id = " . $ilDB->quote($a_portfolio_id, "integer"));
        }
    }

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
     * Get default portfolio of user
     */
    public static function getDefaultPortfolio(int $a_user_id): ?int
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilSetting = $DIC->settings();

        if (!$ilSetting->get('user_portfolios')) {
            return null;
        }

        $set = $ilDB->query("SELECT up.id FROM usr_portfolio up" .
            " JOIN object_data od ON (up.id = od.obj_id)" .
            " WHERE od.owner = " . $ilDB->quote($a_user_id, "integer") .
            " AND up.is_default = " . $ilDB->quote(1, "integer"));
        $res = $ilDB->fetchAssoc($set);
        if ($res && $res["id"]) {
            return (int) $res["id"];
        }
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
}
