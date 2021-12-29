<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObject.php");

/**
* Class ilMediaPoolPage
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesMediaPool
*/
class ilMediaPoolPage extends ilPageObject
{
    /**
    * @var ilObjMediaPool
    */
    protected $pool;

    /**
     * Get parent type
     *
     * @return string parent type
     */
    public function getParentType()
    {
        return "mep";
    }

    /**
     * Set pool
     * @param ilObjMediaPool $pool
     */
    public function setPool(ilObjMediaPool $pool)
    {
        $this->pool = $pool;
    }

    /**
    * update object data
    *
    * @access	public
    * @return	boolean
    */
    public function update($a_validate = true, $a_no_history = false)
    {
        $ilDB = $this->db;
        parent::update($a_validate, $a_no_history);

        return true;
    }

    /**
    * Read media_pool data
    */
    public function read()
    {
        $ilDB = $this->db;

        // get co page
        parent::read();
    }


    /**
    * delete media_pool page and al related data
    *
    * @access	public
    */
    public function delete()
    {
        $ilDB = $this->db;


        // delete internal links information to this page
        //		include_once("./Services/Link/classes/class.ilInternalLink.php");
        //		ilInternalLink::_deleteAllLinksToTarget("mep", $this->getId());


        // delete co page
        parent::delete();

        return true;
    }

    /**
    * delete media pool page and al related data
    *
    * @access	public
    */
    public static function deleteAllPagesOfMediaPool($a_media_pool_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        // todo
/*
        $query = "SELECT * FROM il_media_pool_page".
            " WHERE media_pool_id = ".$ilDB->quote($a_media_pool_id, "integer");
        $set = $ilDB->query($query);

        while($rec = $ilDB->fetchAssoc($set))
        {
            $mp_page = new ilMediaPoolPage($rec["id"]);
            $mp_page->delete();
        }
*/
    }

    /**
    * Checks whether a page with given title exists
    */
    public static function exists($a_media_pool_id, $a_title)
    {
        global $DIC;

        $ilDB = $DIC->database();

        // todo
        /*

                $query = "SELECT * FROM il_media_pool_page".
                    " WHERE media_pool_id = ".$ilDB->quote($a_media_pool_id, "integer").
                    " AND title = ".$ilDB->quote($a_title, "text");
                $set = $ilDB->query($query);
                if($rec = $ilDB->fetchAssoc($set))
                {
                    return true;
                }
        */
        return false;
    }

    /**
    * Lookup title
    */
    public static function lookupTitle($a_page_id)
    {
        global $DIC;

        include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
        return ilMediaPoolItem::lookupTitle($a_page_id);
    }


    /**
    * Check whether page exists in media pool or not
    *
    * @param	int		media pool id
    * @param	string	page name
    * @return	boolean	page exists true/false
    */
    public static function _mediaPoolPageExists($a_media_pool_id, $a_title)
    {
        global $DIC;

        $ilDB = $DIC->database();
        // todo
        /*
                $query = "SELECT id FROM il_media_pool_page".
                    " WHERE media_pool_id = ".$ilDB->quote($a_media_pool_id, "integer").
                    " AND title = ".$ilDB->quote($a_title, "text");
                $set = $ilDB->query($query);

                $pages = array();
                if ($rec = $ilDB->fetchAssoc($set))
                {
                    return true;
                }
        */
        return false;
    }

    /**
    * get all usages of current media object
    */
    public function getUsages($a_incl_hist = true)
    {
        return self::lookupUsages($this->getId(), $a_incl_hist);
    }

    /**
    * Lookup usages of media object
    *
    * @todo: This should be all in one context -> mob id table
    */
    public static function lookupUsages($a_id, $a_incl_hist = true)
    {
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
        while ($us_rec = $ilDB->fetchAssoc($us_set)) {
            $ut = "";
            if (is_int(strpos($us_rec["usage_type"], ":"))) {
                $us_arr = explode(":", $us_rec["usage_type"]);
                $ut = $us_arr[1];
                $ct = $us_arr[0];
            }

            // check whether page exists
            $skip = false;
            if ($ut == "pg") {
                include_once("./Services/COPage/classes/class.ilPageObject.php");
                if (!ilPageObject::_exists($ct, $us_rec["usage_id"])) {
                    $skip = true;
                }
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
            $ret[] = array("type" => "mep",
                "id" => $us_rec["mep_id"]);
        }

        return $ret;
    }

    /**
     * Get metadata type
     * @param
     * @return
     */
    protected function getMetadataType()
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
     *
     * @param string $a_element md element
     * @return boolean success
     */
    public function MDUpdateListener($a_element)
    {
        include_once 'Services/MetaData/classes/class.ilMD.php';

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
    public function createMetaData($pool_id)
    {
        //include_once 'Services/MetaData/classes/class.ilMDCreator.php';

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

    /**
     * update meta data entry
     */
    public function updateMetaData()
    {
        //include_once("Services/MetaData/classes/class.ilMD.php");
        //include_once("Services/MetaData/classes/class.ilMDGeneral.php");
        //include_once("Services/MetaData/classes/class.ilMDDescription.php");

        $md = new ilMD($this->pool->getId(), $this->getId(), $this->getMetadataType());
        $md_gen = $md->getGeneral();
        $md_gen->setTitle(self::lookupTitle($this->getId()));

        $md_gen->update();
    }


    /**
     * delete meta data entry
     */
    public function deleteMetaData()
    {
        // Delete meta data
        include_once('Services/MetaData/classes/class.ilMD.php');
        $md = new ilMD($this->pool->getId(), $this->getId(), $this->getMetadataType());
        $md->deleteAll();
    }
}
