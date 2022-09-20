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
 * Class ilMediaPoolPage
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaPoolPage extends ilPageObject
{
    protected ilObjMediaPool $pool;

    public function getParentType(): string
    {
        return "mep";
    }

    public function setPool(ilObjMediaPool $pool): void
    {
        $this->pool = $pool;
    }

    public static function deleteAllPagesOfMediaPool(int $a_media_pool_id): void
    {
        // @todo deletion process of snippets
    }

    /**
     * Checks whether a page with given title exists
     */
    public static function exists(int $a_media_pool_id, string $a_title): void
    {
        // @todo: check if we need this
    }

    public static function lookupTitle(int $a_page_id): string
    {
        return ilMediaPoolItem::lookupTitle($a_page_id);
    }

    /**
     * get all usages of current media object
     */
    public function getUsages(bool $a_incl_hist = true): array
    {
        return self::lookupUsages($this->getId(), $a_incl_hist);
    }

    /**
     * Lookup usages of media object
     * @todo: This should be all in one context -> mob id table
     */
    public static function lookupUsages(
        int $a_id,
        bool $a_incl_hist = true
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        // get usages in pages
        $q = "SELECT * FROM page_pc_usage WHERE pc_id = " .
            $ilDB->quote($a_id, "integer") .
            " AND pc_type = " . $ilDB->quote("incl", "text");

        if (!$a_incl_hist) {
            $q .= " AND usage_hist_nr = " . $ilDB->quote(0, "integer");
        }

        $us_set = $ilDB->query($q);
        $ret = array();
        $ct = "";
        while ($us_rec = $ilDB->fetchAssoc($us_set)) {
            $ut = "";
            if (is_int(strpos($us_rec["usage_type"], ":"))) {
                $us_arr = explode(":", $us_rec["usage_type"]);
                $ut = $us_arr[1];
                $ct = $us_arr[0];
            }

            // check whether page exists
            $skip = false;
            if ($ut === "pg" && !ilPageObject::_exists($ct, $us_rec["usage_id"])) {
                $skip = true;
            }

            if (!$skip) {
                $ret[] = array("type" => $us_rec["usage_type"],
                    "id" => $us_rec["usage_id"],
                    "hist_nr" => $us_rec["usage_hist_nr"],
                    "lang" => $us_rec["usage_lang"]);
            }
        }

        // get usages in media pools
        $q = "SELECT DISTINCT mep_id FROM mep_tree JOIN mep_item ON (child = obj_id) WHERE mep_item.obj_id = " .
            $ilDB->quote($a_id, "integer") . " AND mep_item.type = " . $ilDB->quote("pg", "text");
        $us_set = $ilDB->query($q);
        while ($us_rec = $ilDB->fetchAssoc($us_set)) {
            $ret[] = [
                "type" => "mep",
                "id" => (int) $us_rec["mep_id"]
            ];
        }

        return $ret;
    }

    protected function getMetadataType(): string
    {
        return "mpg";
    }

    /**
     * Meta data update listener
     *
     * Important note: Do never call create() or update()
     * method of ilObject here. It would result in an
     * endless loop: update object -> update meta -> update
     * object -> ...
     * Use static _writeTitle() ... methods instead.
     */
    public function MDUpdateListener(string $a_element): bool
    {
        switch ($a_element) {
            case 'General':

                // Update Title and description
                $md = new ilMD($this->pool->getId(), $this->getId(), $this->getMetadataType());
                $md_gen = $md->getGeneral();

                $item = new ilMediaPoolItem($this->getId());
                $item->setTitle($md_gen->getTitle());
                $item->update();

                break;

            default:
        }
        return true;
    }

    /**
     * create meta data entry
     */
    public function createMetaData(int $pool_id): bool
    {
        $ilUser = $this->user;

        $md_creator = new ilMDCreator($pool_id, $this->getId(), $this->getMetadataType());
        $md_creator->setTitle(self::lookupTitle($this->getId()));
        $md_creator->setTitleLanguage($ilUser->getPref('language'));
        $md_creator->setDescription("");
        $md_creator->setDescriptionLanguage($ilUser->getPref('language'));
        $md_creator->setKeywordLanguage($ilUser->getPref('language'));
        $md_creator->setLanguage($ilUser->getPref('language'));
        $md_creator->create();

        return true;
    }

    public function updateMetaData(): void
    {
        $md = new ilMD($this->pool->getId(), $this->getId(), $this->getMetadataType());
        $md_gen = $md->getGeneral();
        $md_gen->setTitle(self::lookupTitle($this->getId()));
        $md_gen->update();
    }


    public function deleteMetaData(): void
    {
        // Delete meta data
        $md = new ilMD($this->pool->getId(), $this->getId(), $this->getMetadataType());
        $md->deleteAll();
    }
}
