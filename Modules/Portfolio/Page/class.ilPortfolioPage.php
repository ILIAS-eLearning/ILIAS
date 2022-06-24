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
 * Page for user portfolio
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPortfolioPage extends ilPageObject
{
    public const TYPE_PAGE = 1;
    public const TYPE_BLOG = 2;

    protected int $portfolio_id;
    protected int $type = 1;
    protected string $title;
    protected int $order_nr;

    public function getParentType() : string
    {
        return "prtf";
    }

    public function setPortfolioId(int $a_val) : void
    {
        $this->portfolio_id = $a_val;
    }

    public function getPortfolioId() : int
    {
        return $this->portfolio_id;
    }

    /**
     * @param int $a_val self::TYPE_PAGE|self::TYPE_BLOG
     */
    public function setType(int $a_val) : void
    {
        $this->type = $a_val;
    }

    public function getType() : int
    {
        return $this->type;
    }

    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }

    public function getTitle() : string
    {
        $lng = $this->lng;

        // because of migration of extended user profiles
        if ($this->title === "###-") {
            return $lng->txt("profile");
        }

        return $this->title;
    }

    public function setOrderNr(int $a_val) : void
    {
        $this->order_nr = $a_val;
    }

    public function getOrderNr() : int
    {
        return $this->order_nr;
    }

    public static function lookupMaxOrderNr(
        int $a_portfolio_id
    ) : int {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT MAX(order_nr) m FROM usr_portfolio_page" .
            " WHERE portfolio_id = " . $ilDB->quote($a_portfolio_id, "integer"));
        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["m"];
    }

    protected function getPropertiesForDB() : array
    {
        $fields = array(
            "portfolio_id" => array("integer", $this->portfolio_id),
            "type" => array("integer", $this->getType()),
            "title" => array("text", $this->getTitle()),
            "order_nr" => array("integer", $this->getOrderNr())
        );

        return $fields;
    }

    public function create(bool $a_import = false) : void
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
            parent::create($a_import);
            // $this->saveInternalLinks($this->getDomDoc());
        }
    }

    public function update(
        bool $a_validate = true,
        bool $a_no_history = false
    ) : bool {
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

    public function read() : void
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

    public function delete() : void
    {
        $ilDB = $this->db;

        $id = $this->getId();
        if ($id) {
            // delete internal links information to this page
            ilInternalLink::_deleteAllLinksToTarget("user", $this->getId());

            // delete record of table usr_portfolio_page
            $query = "DELETE FROM usr_portfolio_page" .
                " WHERE id = " . $ilDB->quote($this->getId(), "integer");
            $ilDB->manipulate($query);

            // delete co page
            parent::delete();
        }
    }

    protected static function lookupProperty(
        int $a_id,
        string $a_prop
    ) : string {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT " . $a_prop .
            " FROM usr_portfolio_page" .
            " WHERE id = " . $ilDB->quote($a_id, "integer"));
        $rec = $ilDB->fetchAssoc($set);
        return (string) $rec[$a_prop];
    }

    public static function lookupTitle(int $a_page_id) : string
    {
        return self::lookupProperty($a_page_id, "title");
    }

    public static function lookupType($a_page_id) : int
    {
        return (int) self::lookupProperty($a_page_id, "type");
    }

    /**
     * Get pages of portfolio
     */
    public static function getAllPortfolioPages(
        int $a_portfolio_id
    ) : array {
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

    public static function fixOrdering(
        int $a_portfolio_id
    ) : void {
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
     */
    public static function findPortfolioForPage(int $a_page_id) : int
    {
        return (int) self::lookupProperty($a_page_id, "portfolio_id");
    }

    /**
     * Get goto href for portfolio page id
     */
    public static function getGotoForPortfolioPageTarget(
        int $a_target,
        bool $a_offline = false
    ) : string {
        global $DIC;

        $pid = self::findPortfolioForPage($a_target);
        $type = ilObject::_lookupType($pid);
        if ($type === "prtt") {
            $ctrl = $DIC->ctrl();
            $ctrl->setParameterByClass("ilobjportfoliotemplategui", "user_page", $a_target);
            $href = $ctrl->getLinkTargetByClass(array(
                "ilRepositoryGUI",
                "ilObjPortfolioTemplateGUI",
                "ilobjportfoliotemplategui"
            ), "preview", "", false, true);
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
    public static function updateInternalLinks(
        array $a_copied_nodes,
        ilObjPortfolioBase $a_target_obj
    ) : void {
        $all_fixes = array();
        $tpg = null;

        foreach ($a_copied_nodes as $original_id => $copied_id) {
            $pid = self::findPortfolioForPage((int) $copied_id);

            //
            // 1. Outgoing links from the copied page.
            //
            //$targets = ilInternalLink::_getTargetsOfSource($a_parent_type.":pg", $copied_id);
            if ($a_target_obj->getType() === "prtf") {
                $tpg = new ilPortfolioPage($copied_id);
            }
            if ($a_target_obj->getType() === "prtt") {
                $tpg = new ilPortfolioTemplatePage($copied_id);
            }
            $tpg->buildDom();
            $il = $tpg->getInternalLinks();
            //			var_dump($il);
            $targets = array();
            foreach ($il as $l) {
                $targets[] = array(
                    "type" => ilInternalLink::_extractTypeOfTarget($l["Target"]),
                    "id" => ilInternalLink::_extractObjIdOfTarget($l["Target"]),
                    "inst" => (int) ilInternalLink::_extractInstOfTarget($l["Target"])
                );
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
            $page = ilPageObjectFactory::getInstance($pg[0], $pg[1]);
            if ($page->moveIntLinks($fixes)) {
                $page->update(true, true);
            }
        }
    }


    public function renameLinksOnTitleChange(
        array $a_title_changes
    ) : bool {
        $this->buildDom();

        $changed = false;

        // resolve normal internal links
        $xpc = xpath_new_context($this->dom);
        $path = "//IntLink";
        $res = xpath_eval($xpc, $path);
        for ($i = 0, $iMax = count($res->nodeset); $i < $iMax; $i++) {
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
    public static function getPagesForBlog(
        int $a_blog_id
    ) : array {
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
