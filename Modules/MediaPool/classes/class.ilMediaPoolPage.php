<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilMediaPoolPage
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaPoolPage extends ilPageObject
{
    public function getParentType() : string
    {
        return "mep";
    }

    public static function deleteAllPagesOfMediaPool($a_media_pool_id) : void// TODO PHP8-REVIEW Type hint missing
    {
        // @todo deletion process of snippets
    }
    
    /**
     * Checks whether a page with given title exists
     */
    public static function exists($a_media_pool_id, $a_title) : void// TODO PHP8-REVIEW Type hints missing
    {
        // @todo: check if we need this
    }
    
    public static function lookupTitle(int $a_page_id) : string
    {
        return ilMediaPoolItem::lookupTitle($a_page_id);
    }

    /**
     * get all usages of current media object
     */
    public function getUsages(bool $a_incl_hist = true) : array
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
    ) : array {
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
}
