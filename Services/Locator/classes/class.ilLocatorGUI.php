<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* locator handling class
*
* This class supplies an implementation for the locator.
* The locator will send its output to ist own frame, enabling more flexibility in
* the design of the desktop.
*
* @author Arjan Ammerlaan <a.l.ammerlaan@web.de>
* @version $Id$
*
*/
class ilLocatorGUI
{
    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilSetting
     */
    protected $settings;

    protected $lng;
    protected $entries;
    
    /**
    * Constructor
    *
    */
    public function __construct()
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->ctrl = $DIC->ctrl();
        $this->obj_definition = $DIC["objDefinition"];
        $this->access = $DIC->access();
        $this->settings = $DIC->settings();
        $lng = $DIC->language();

        $this->lng = $lng;
        $this->entries = array();
        $this->setTextOnly(false);
        $this->offline = false;
    }

    /**
    * Set Only text, no HTML.
    *
    * @param	boolean	$a_textonly	Only text, no HTML
    */
    public function setTextOnly($a_textonly)
    {
        $this->textonly = $a_textonly;
    }
    
    public function setOffline($a_offline)
    {
        $this->offline = $a_offline;
    }

    public function getOffline()
    {
        return $this->offline;
    }

    /**
    * Get Only text, no HTML.
    *
    * @return	boolean	Only text, no HTML
    */
    public function getTextOnly()
    {
        return $this->textonly;
    }

    /**
    * add repository item
    *
    * @param	int		$a_ref_id	current ref id (optional);
    *								if empty $_GET["ref_id"] is used
    */
    public function addRepositoryItems($a_ref_id = 0)
    {
        $setting = $this->settings;
        $tree = $this->tree;
        $ilCtrl = $this->ctrl;

        if ($a_ref_id == 0) {
            $a_ref_id = $_GET["ref_id"];
        }

        $a_start = ROOT_FOLDER_ID;
        if ($a_ref_id > 0) {
            $path = $tree->getPathFull($a_ref_id, $a_start);

            // check if path contains crs
            $crs_ref_id = 0;
            foreach ($path as $k => $v) {
                if ($v["type"] == "crs") {
                    $crs_ref_id = $v["child"];
                }
            }
            if (!$setting->get("rep_breadcr_crs")) { // no overwrite
                $crs_ref_id = 0;
            } elseif ($setting->get("rep_breadcr_crs_overwrite")) { // overwrite
                // course wants full path
                if (ilContainer::_lookupContainerSetting(ilObject::_lookupObjId($crs_ref_id), "rep_breacrumb") == ilObjCourseGUI::BREADCRUMB_FULL_PATH) {
                    $crs_ref_id = 0;
                }
                // course wants default and default wants full path
                if (ilContainer::_lookupContainerSetting(ilObject::_lookupObjId($crs_ref_id), "rep_breacrumb") == ilObjCourseGUI::BREADCRUMB_DEFAULT && !$setting->get("rep_breadcr_crs_default")) {
                    $crs_ref_id = 0;
                }
            }

            // add item for each node on path
            foreach ((array) $path as $key => $row) {
                if (!in_array($row["type"], array("root", "cat", "crs", "fold", "grp", "prg", "lso"))) {
                    continue;
                }
                if ($crs_ref_id > 0 && $row["child"] == $crs_ref_id) {
                    $crs_ref_id = 0;
                }
                if ($crs_ref_id > 0) {
                    continue;
                }

                if ($row["title"] == "ILIAS" && $row["type"] == "root") {
                    $row["title"] = $this->lng->txt("repository");
                }
                
                $this->addItem(
                    $row["title"],
                    ilLink::_getLink($row["child"]),
                    ilFrameTargetInfo::_getFrame("MainContent"),
                    $row["child"]
                );
            }
        }
    }
    
    /**
    * add administration tree items
    *
    * @param	int		$a_ref_id	current ref id (optional);
    *								if empty $_GET["ref_id"] is used
    */
    public function addAdministrationItems($a_ref_id = 0)
    {
        $tree = $this->tree;
        $ilCtrl = $this->ctrl;
        $objDefinition = $this->obj_definition;
        $lng = $this->lng;

        if ($a_ref_id == 0) {
            $a_ref_id = $_GET["ref_id"];
        }

        if ($a_ref_id > 0) {
            $path = $tree->getPathFull($a_ref_id);
            
            // add item for each node on path
            foreach ($path as $key => $row) {
                if (!in_array($row["type"], array("root", "cat", "crs", "fold", "grp"))) {
                    continue;
                }
                
                if ($row["child"] == ROOT_FOLDER_ID) {
                    $row["title"] = $lng->txt("repository");
                }
                
                $class_name = $objDefinition->getClassName($row["type"]);
                $class = strtolower("ilObj" . $class_name . "GUI");
                $ilCtrl->setParameterByClass($class, "ref_id", $row["child"]);
                $this->addItem(
                    $row["title"],
                    $ilCtrl->getLinkTargetbyClass($class, "view"),
                    "",
                    $row["child"]
                );
            }
        }
    }
    
    public function addContextItems($a_ref_id, $a_omit_node = false, $a_stop = 0)
    {
        $tree = $this->tree;
        
        if ($a_ref_id > 0) {
            $path = $tree->getPathFull($a_ref_id);
            
            // we want to show the full path, from the major container to the item
            // (folders are not! treated as containers here), at least one parent item
            $r_path = array_reverse($path);
            $first = "";
            $omit = array();
            $do_omit = false;
            foreach ($r_path as $key => $row) {
                if ($first == "") {
                    if (in_array($row["type"], array("root", "cat", "grp", "crs")) &&
                        $row["child"] != $a_ref_id) {
                        $first = $row["child"];
                    }
                }
                if ($a_stop == $row["child"]) {
                    $do_omit = true;
                }
                $omit[$row["child"]] = $do_omit;
            }

            $add_it = false;
            foreach ($path as $key => $row) {
                if ($first == $row["child"]) {
                    $add_it = true;
                }
                
                
                if ($add_it && !$omit[$row["child"]] &&
                    (!$a_omit_node || ($row["child"] != $a_ref_id))) {
                    //echo "-".ilObject::_lookupTitle($row["obj_id"])."-";
                    if ($row["title"] == "ILIAS" && $row["type"] == "root") {
                        $row["title"] = $this->lng->txt("repository");
                    }
                    $this->addItem(
                        $row["title"],
                        "./goto.php?client_id=" . rawurlencode(CLIENT_ID) . "&target=" . $row["type"] . "_" . $row["child"],
                        "_top",
                        $row["child"],
                        $row["type"]
                    );
                }
            }
        }
    }
    
    /**
    * add locator item
    *
    * @param	string	$a_title		item title
    * @param	string	$a_link			item link
    * @param	string	$a_frame		frame target
    */
    public function addItem($a_title, $a_link, $a_frame = "", $a_ref_id = 0, $type = null)
    {
        // LTI
        global $DIC;
        $ltiview = $DIC['lti'];
        
        $ilAccess = $this->access;

        if ($a_ref_id > 0 && !$ilAccess->checkAccess("visible", "", $a_ref_id)) {
            return;
        }
        // LTI
        if ($ltiview->isActive()) {
            $a_frame = "_self";
        }
        $this->entries[] = array("title" => $a_title,
            "link" => $a_link, "frame" => $a_frame, "ref_id" => $a_ref_id, "type" => $type);
    }
    
    /**
    * Clear all Items
    */
    public function clearItems()
    {
        $this->entries = array();
    }
    
    /**
    * Get all locator entries.
    */
    public function getItems()
    {
        return $this->entries;
    }
    
    /**
    * Get locator HTML
    */
    public function getHTML()
    {
        $lng = $this->lng;
        $ilSetting = $this->settings;
        
        if ($this->getTextOnly()) {
            $loc_tpl = new ilTemplate("tpl.locator_text_only.html", true, true, "Services/Locator");
        } else {
            $loc_tpl = new ilTemplate("tpl.locator.html", true, true, "Services/Locator");
        }
        
        $items = $this->getItems();
        $first = true;

        if (is_array($items)) {
            foreach ($items as $item) {
                if (!$first) {
                    $loc_tpl->touchBlock("locator_separator_prefix");
                }
                
                if ($item["ref_id"] > 0) {
                    $obj_id = ilObject::_lookupObjId($item["ref_id"]);
                    $type = ilObject::_lookupType($obj_id);
                    
                    if (!$this->getTextOnly()) {
                        $icon_path = ilObject::_getIcon(
                            $obj_id,
                            "tiny",
                            $type,
                            $this->getOffline()
                        );
                    }
                    
                    $loc_tpl->setCurrentBlock("locator_img");
                    $loc_tpl->setVariable("IMG_SRC", $icon_path);
                    $loc_tpl->setVariable(
                        "IMG_ALT",
                        $lng->txt("obj_" . $type)
                    );
                    $loc_tpl->parseCurrentBlock();
                }
                
                $loc_tpl->setCurrentBlock("locator_item");
                if ($item["link"] != "") {
                    $loc_tpl->setVariable("LINK_ITEM", $item["link"]);
                    if ($item["frame"] != "") {
                        $loc_tpl->setVariable("LINK_TARGET", ' target="' . $item["frame"] . '" ');
                    }
                    $loc_tpl->setVariable("ITEM", $item["title"]);
                } else {
                    $loc_tpl->setVariable("PREFIX", $item["title"]);
                }
                $loc_tpl->parseCurrentBlock();
                
                $first = false;
            }
        } else {
            $loc_tpl->setVariable("NOITEM", "&nbsp;");
            $loc_tpl->touchBlock("locator");
        }
        $loc_tpl->setVariable("TXT_BREADCRUMBS", $lng->txt("breadcrumb_navigation"));
        
        return trim($loc_tpl->get());
    }

    /**
     * Get text version
     */
    public function getTextVersion()
    {
        $lng = $this->lng;
        $ilSetting = $this->settings;
        
        $items = $this->getItems();
        $first = true;

        $str = "";
        if (is_array($items)) {
            foreach ($items as $item) {
                if (!$first) {
                    $str .= " > ";
                }
                
                $str .= $item["title"];
                
                $first = false;
            }
        }
        
        return $str;
    }
} // END class.LocatorGUI
