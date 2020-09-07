<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObject.php");
include_once("./Modules/Portfolio/classes/class.ilObjPortfolio.php");

/**
 * Page for user portfolio
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesPortfolio
 */
class ilPortfolioPage extends ilPageObject
{
    protected $portfolio_id;
    protected $type = 1;
    protected $title;
    protected $order_nr;
    
    const TYPE_PAGE = 1;
    const TYPE_BLOG = 2;
    
    /**
     * Get parent type
     *
     * @return string parent type
     */
    public function getParentType()
    {
        return "prtf";
    }
    
    /**
     * Set portfolio id
     *
     * @param int $a_val portfolio id
     */
    public function setPortfolioId($a_val)
    {
        $this->portfolio_id = $a_val;
    }
    
    /**
     * Get portfolio id
     *
     * @return int portfolio id
     */
    public function getPortfolioId()
    {
        return $this->portfolio_id;
    }
    
    /**
     * Set type
     *
     * @param	int	type
     */
    public function setType($a_val)
    {
        $this->type = $a_val;
    }

    /**
     * Get type
     *
     * @return	int	type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set Title
     *
     * @param	string	$a_title	Title
     */
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }

    /**
     * Get Title.
     *
     * @return	string	Title
     */
    public function getTitle()
    {
        $lng = $this->lng;
        
        // because of migration of extended user profiles
        if ($this->title == "###-") {
            return $lng->txt("profile");
        }
        
        return $this->title;
    }

    /**
     * Set order nr
     *
     * @param	int	order nr
     */
    public function setOrderNr($a_val)
    {
        $this->order_nr = (int) $a_val;
    }

    /**
     * Get order nr
     *
     * @return	int	order nr
     */
    public function getOrderNr()
    {
        return $this->order_nr;
    }

    /**
     * Lookup max order nr for portfolio
     *
     * @param int $a_portfolio_id
     * @return int
     */
    public static function lookupMaxOrderNr($a_portfolio_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT MAX(order_nr) m FROM usr_portfolio_page" .
            " WHERE portfolio_id = " . $ilDB->quote($a_portfolio_id, "integer"));
        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["m"];
    }

    /**
     * Get properties for insert/update statements
     *
     * @return array
     */
    protected function getPropertiesForDB()
    {
        $fields = array("portfolio_id" => array("integer", $this->portfolio_id),
            "type" => array("integer", $this->getType()),
            "title" => array("text", $this->getTitle()),
            "order_nr" => array("integer", $this->getOrderNr()));

        return $fields;
    }

    /**
     * Create new portfolio page
     */
    public function create($a_import = false)
    {
        $ilDB = $this->db;

        if (!$a_import) {
            $this->setOrderNr(self::lookupMaxOrderNr($this->portfolio_id) + 10);
        }

        $id = $ilDB->nextId("usr_portfolio_page");
        $this->setId($id);

        $fields = $this->getPropertiesForDB();
        $fields["id"] = array("integer", $id);

        $ilDB->insert("usr_portfolio_page", $fields);

        if (!$a_import) {
            parent::create();
            // $this->saveInternalLinks($this->getDomDoc());
        }
    }

    /**
     * Update page
     *
     * @return	boolean
     */
    public function update($a_validate = true, $a_no_history = false)
    {
        $ilDB = $this->db;
        
        $id = $this->getId();
        if ($id) {
            $fields = $this->getPropertiesForDB();
            $ilDB->update(
                "usr_portfolio_page",
                $fields,
                array("id" => array("integer", $id))
            );

            parent::update($a_validate, $a_no_history);
            return true;
        }
        return false;
    }
    
    /**
     * Read page data
     */
    public function read()
    {
        $ilDB = $this->db;
        
        $query = "SELECT * FROM usr_portfolio_page" .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $set = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($set);

        $this->setPortfolioId($rec["portfolio_id"]);
        $this->setType($rec["type"]);
        $this->setTitle($rec["title"]);
        $this->setOrderNr($rec["order_nr"]);
        
        // get co page
        parent::read();
    }

    /**
     * delete portfolio page and all related data
     */
    public function delete()
    {
        $ilDB = $this->db;

        $id = $this->getId();
        if ($id) {
            // delete internal links information to this page
            include_once("./Services/Link/classes/class.ilInternalLink.php");
            ilInternalLink::_deleteAllLinksToTarget("user", $this->getId());

            // delete record of table usr_portfolio_page
            $query = "DELETE FROM usr_portfolio_page" .
                " WHERE id = " . $ilDB->quote($this->getId(), "integer");
            $ilDB->manipulate($query);
        
            // delete co page
            parent::delete();
        }
    }

    /**
     * Lookup portfolio page property
     *
     * @param int $a_id
     * @param string $a_prop
     * @return mixed
     */
    protected static function lookupProperty($a_id, $a_prop)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT " . $a_prop .
            " FROM usr_portfolio_page" .
            " WHERE id = " . $ilDB->quote($a_id, "integer"));
        $rec = $ilDB->fetchAssoc($set);
        return $rec[$a_prop];
    }

    /**
     * Lookup title
     *
     * @param int $a_page_id
     */
    public static function lookupTitle($a_page_id)
    {
        return self::lookupProperty($a_page_id, "title");
    }

    /**
     * Get pages of portfolio
     *
     * @param int $a_portfolio_id
     * @return array
     */
    public static function getAllPortfolioPages($a_portfolio_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $lng = $DIC->language();

        $set = $ilDB->query("SELECT * FROM usr_portfolio_page" .
            " WHERE portfolio_id = " . $ilDB->quote($a_portfolio_id, "integer") .
            " ORDER BY order_nr");
        $pages = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            // because of migration of extended user profiles
            if ($rec["title"] == "###-") {
                $rec["title"] = $lng->txt("profile");
            }
            
            $pages[] = $rec;
        }
        return $pages;
    }

    /**
     * Fix ordering
     *
     * @param int $a_portfolio_id
     */
    public static function fixOrdering($a_portfolio_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $pages = self::getAllPortfolioPages($a_portfolio_id);
        $cnt = 10;
        foreach ($pages as $p) {
            $ilDB->manipulate(
                "UPDATE usr_portfolio_page SET " .
                " order_nr = " . $ilDB->quote($cnt, "integer") .
                " WHERE id = " . $ilDB->quote($p["id"], "integer")
            );
            $cnt += 10;
        }
    }
    
    /**
     * Get portfolio id of page id
     *
     * @param int $a_page_id
     * @return int
     */
    public static function findPortfolioForPage($a_page_id)
    {
        return self::lookupProperty($a_page_id, "portfolio_id");
    }

    /**
     * Get goto href for internal wiki page link target
     *
     * @param
     * @return string
     */
    public static function getGotoForPortfolioPageTarget($a_target, $a_offline = false)
    {
        global $DIC;

        $pid = self::findPortfolioForPage((int) $a_target);
        $type = ilObject::_lookupType($pid);
        if ($type == "prtt") {
            $ctrl = $DIC->ctrl();
            $ctrl->setParameterByClass("ilobjportfoliotemplategui", "user_page", $a_target);
            $href = $ctrl->getLinkTargetByClass(array("ilRepositoryGUI", "ilObjPortfolioTemplateGUI", "ilobjportfoliotemplategui"), "preview");
        } else {
            if (!$a_offline) {
                $href = "./goto.php?client_id=" . CLIENT_ID . "&amp;target=prtf_" . $pid . "_" . $a_target;
            } else {
                $href = "prtf_" . $a_target . ".html";
            }
        }
        return $href;
    }

    /**
     * Update internal links, after multiple pages have been copied
     */
    public static function updateInternalLinks($a_copied_nodes, ilObjPortfolioBase $a_target_obj)
    {
        //		var_dump($a_copied_nodes);
        $all_fixes = array();
        foreach ($a_copied_nodes as $original_id => $copied_id) {
            $pid = self::findPortfolioForPage((int) $copied_id);

            //
            // 1. Outgoing links from the copied page.
            //
            //$targets = ilInternalLink::_getTargetsOfSource($a_parent_type.":pg", $copied_id);
            if ($a_target_obj->getType() == "prtf") {
                $tpg = new ilPortfolioPage($copied_id);
            }
            if ($a_target_obj->getType() == "prtt") {
                $tpg = new ilPortfolioTemplatePage($copied_id);
            }
            $tpg->buildDom();
            $il = $tpg->getInternalLinks();
            //			var_dump($il);
            $targets = array();
            foreach ($il as $l) {
                $targets[] = array("type" => ilInternalLink::_extractTypeOfTarget($l["Target"]),
                    "id" => (int) ilInternalLink::_extractObjIdOfTarget($l["Target"]),
                    "inst" => (int) ilInternalLink::_extractInstOfTarget($l["Target"]));
            }
            $fix = array();
            foreach ($targets as $target) {
                if (($target["inst"] == 0 || $target["inst"] = IL_INST_ID) &&
                    ($target["type"] == "ppage")) {
                    // first check, whether target is also within the copied set
                    if ($a_copied_nodes[$target["id"]] > 0) {
                        $fix[$target["id"]] = $a_copied_nodes[$target["id"]];
                    }
                }
            }
            //			var_dump($fix);
            // outgoing links to be fixed
            if (count($fix) > 0) {
                $t = ilObject::_lookupType($pid);
                if (is_array($all_fixes[$t . ":" . $copied_id])) {
                    $all_fixes[$t . ":" . $copied_id] += $fix;
                } else {
                    $all_fixes[$t . ":" . $copied_id] = $fix;
                }
            }
        }
        //		var_dump($all_fixes);
        foreach ($all_fixes as $pg => $fixes) {
            $pg = explode(":", $pg);
            include_once("./Services/COPage/classes/class.ilPageObjectFactory.php");
            $page = ilPageObjectFactory::getInstance($pg[0], $pg[1]);
            if ($page->moveIntLinks($fixes)) {
                $page->update(true, true);
            }
        }
    }


    /**
     * @param $a_title_changes
     */
    public function renameLinksOnTitleChange($a_title_changes)
    {
        $this->buildDom();

        $changed = false;

        // resolve normal internal links
        $xpc = xpath_new_context($this->dom);
        $path = "//IntLink";
        $res = xpath_eval($xpc, $path);
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $target = $res->nodeset[$i]->get_attribute("Target");
            $type = $res->nodeset[$i]->get_attribute("Type");
            $obj_id = ilInternalLink::_extractObjIdOfTarget($target);
            if (isset($a_title_changes[$obj_id]) && is_int(strpos($target, "__"))) {
                if ($type == "PortfolioPage") {
                    if ($res->nodeset[$i]->get_content() == $a_title_changes[$obj_id]["old"]) {
                        $res->nodeset[$i]->set_content($a_title_changes[$obj_id]["new"]);
                        $changed = true;
                    }
                }
            }
        }
        unset($xpc);

        return $changed;
    }

    /**
     * Get portfolio pages for blog
     *
     * @param int $a_blog_id
     * @return ilPortfolioPage[]
     */
    public static function getPagesForBlog($a_blog_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT * FROM usr_portfolio_page" .
            " WHERE title = " . $ilDB->quote($a_blog_id, "text") .
            " AND type = " . $ilDB->quote(self::TYPE_BLOG, "integer"));
        $pages = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $pages[] = new ilPortfolioPage($rec["id"]);
        }
        return $pages;
    }
}
