<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Tabs GUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilTabsGUI
{

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    public $target_script;
    public $obj_type;
    public $tpl;
    public $lng;
    public $tabs;
    public $target = array();
    public $sub_target = array();
    public $non_tabbed_link = array();
    public $setup_mode = false;

    protected $force_one_tab = false;

    /**
    * Constructor
    * @access	public
    */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $tpl = $DIC["tpl"];
        $lng = $DIC->language();

        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->manual_activation = false;
        $this->subtab_manual_activation = false;
        $this->temp_var = "TABS";
        $this->sub_tabs = false;
        $this->back_title = "";
        $this->back_target = "";
        $this->back_2_target = "";
        $this->back_2_title = "";
    }
    
    /**
     * Set setup mode
     *
     * @param boolean $a_val setup mode
     */
    public function setSetupMode($a_val)
    {
        $this->setup_mode = $a_val;
    }
    
    /**
     * Get setup mode
     *
     * @return boolean setup mode
     */
    public function getSetupMode()
    {
        return $this->setup_mode;
    }
    
    /**
    * back target for upper context
    */
    public function setBackTarget($a_title, $a_target, $a_frame = "")
    {
        $this->back_title = $a_title;
        $this->back_target = $a_target;
        $this->back_frame = $a_frame;
    }

    /**
    * back target for tow level upper context
    */
    public function setBack2Target($a_title, $a_target, $a_frame = "")
    {
        $this->back_2_title = $a_title;
        $this->back_2_target = $a_target;
        $this->back_2_frame = $a_frame;
    }

    /**
     * Set force presentation of single tab
     *
     * @param bool $a_val force presentation of single tab
     */
    public function setForcePresentationOfSingleTab($a_val)
    {
        $this->force_one_tab = $a_val;
    }

    /**
     * Get force presentation of single tab
     *
     * @return bool force presentation of single tab
     */
    public function getForcePresentationOfSingleTab()
    {
        return $this->force_one_tab;
    }

    /**
    * @deprecated since version 5.0
    *
    * Use addTab/addSubTab and activateTab/activateSubTab.
    *
    * Add a target to the tabbed menu. If no target has set $a_activate to
    * true, ILIAS tries to determine the current activated menu item
    * automatically using $a_cmd and $a_cmdClass. If one item is set
    * activated (and only one should be activated) the automatism is disabled.
    *
    * @param	string		$a_text			menu item text
    * @param	string		$a_link			menu item link
    * @param	string		$a_cmd			command, used for auto activation
    * @param	string		$a_cmdClass		used for auto activation. String or array of cmd classes
    * @param	string		$a_frame		frame target
    * @param	boolean		$a_activate		activate this menu item
    */
    public function addTarget(
        $a_text,
        $a_link,
        $a_cmd = "",
        $a_cmdClass = "",
        $a_frame = "",
        $a_activate = false,
        $a_dir_text = false
    ) {
        if (!$a_cmdClass) {
            $a_cmdClass = array();
        }
        $a_cmdClass = !is_array($a_cmdClass) ? array(strtolower($a_cmdClass)) : $a_cmdClass;
        #$a_cmdClass = strtolower($a_cmdClass);

        if ($a_activate) {
            $this->manual_activation = true;
        }
        $this->target[] = array("text" => $a_text, "link" => $a_link,
            "cmd" => $a_cmd, "cmdClass" => $a_cmdClass, "frame" => $a_frame,
            "activate" => $a_activate, "dir_text" => $a_dir_text, "id" => $a_text);
    }
    
    /**
    * Add a Tab
    *
    * @param	string		id
    * @param	string		text (no lang var!)
    * @param	string		link
    * @param	string		frame target
    */
    public function addTab($a_id, $a_text, $a_link, $a_frame = "")
    {
        $this->target[] = array("text" => $a_text, "link" => $a_link,
            "frame" => $a_frame, "dir_text" => true, "id" => $a_id, "cmdClass" => array());
    }
    
    /**
     * Remove a tab identified by its id.
     *
     * @param 	string	$a_id	Id of tab to remove
     * @return bool	false if tab wasn't found
     * @access public
     */
    public function removeTab($a_id)
    {
        foreach ($this->target as $key => $target) {
            if ($target['id'] == $a_id) {
                unset($this->target[$key]);
                return true;
            }
        }
        return false;
    }

    /**
     * Remove a tab identified by its id.
     *
     * @param 	string	$a_id	Id of tab to remove
     * @return bool	false if tab wasn't found
     * @access public
     */
    public function removeSubTab($a_id)
    {
        foreach ($this->sub_target as $i => $sub_target) {
            if ($this->sub_target[$i]['id'] == $a_id) {
                unset($this->sub_target[$i]);
                return true;
            }
        }
        return false;
    }

    /**
     * Replace a tab.
     * In contrast to a combination of removeTab and addTab, the position is kept.
     *
     * @param string $a_old_id				old id of tab
     * @param string $a_new_id				new id if tab
     * @param string $a_text				tab text
     * @param string $a_link				tab link
     * @param string $a_frame[optional]		frame
     * @return bool
     */
    public function replaceTab($a_old_id, $a_new_id, $a_text, $a_link, $a_frame = '')
    {
        for ($i = 0; $i < count($this->target); $i++) {
            if ($this->target[$i]['id'] == $a_old_id) {
                $this->target[$i] = array();
                $this->target[$i] = array(
                    "text" => $a_text,
                    "link" => $a_link,
                    "frame" => $a_frame,
                    "dir_text" => true,
                    "id" => $a_new_id,
                    "cmdClass" => array());
                return true;
            }
        }
        return false;
    }

    /**
    * clear all targets
    */
    public function clearTargets()
    {
        global $DIC;

        $ilHelp = null;
        if (isset($DIC["ilHelp"])) {
            $ilHelp = $DIC["ilHelp"];
        }
        
        if (!$this->getSetupMode()) {
            $ilHelp->setScreenIdComponent("");
        }

        $this->target = array();
        $this->sub_target = array();
        $this->non_tabbed_link = array();
        $this->back_title = "";
        $this->back_target = "";
        $this->back_2_target = "";
        $this->back_2_title = "";
        $this->setTabActive("");
        $this->setSubTabActive("");
    }

    /**
    * DEPRECATED.
    *
    * Use addTab/addSubTab and activateTab/activateSubTab.
    *
    * Add a Subtarget to the tabbed menu. If no target has set $a_activate to
    * true, ILIAS tries to determine the current activated menu item
    * automatically using $a_cmd and $a_cmdClass. If one item is set
    * activated (and only one should be activated) the automatism is disabled.
    *
    * @param	string		$a_text			menu item text
    * @param	string		$a_link			menu item link
    * @param	string		$a_cmd			command, used for auto activation
    * @param	string		$a_cmdClass		used for auto activation. String or array of cmd classes
    * @param	string		$a_frame		frame target
    * @param	boolean		$a_activate		activate this menu item
    * @param	boolean		$a_dir_text		text is direct text, no language variable
    */
    public function addSubTabTarget(
        $a_text,
        $a_link,
        $a_cmd = "",
        $a_cmdClass = "",
        $a_frame = "",
        $a_activate = false,
        $a_dir_text = false
    ) {
        if (!$a_cmdClass) {
            $a_cmdClass = array();
        }
        $a_cmdClass = !is_array($a_cmdClass) ? array(strtolower($a_cmdClass)) : $a_cmdClass;
        #$a_cmdClass = strtolower($a_cmdClass);

        if ($a_activate) {
            $this->subtab_manual_activation = true;
        }
        $this->sub_target[] = array("text" => $a_text, "link" => $a_link,
            "cmd" => $a_cmd, "cmdClass" => $a_cmdClass, "frame" => $a_frame,
            "activate" => $a_activate, "dir_text" => $a_dir_text, "id" => $a_text);
    }

    /**
    * Add a Subtab
    *
    * @param	string		id
    * @param	string		text (no lang var!)
    * @param	string		link
    * @param	string		frame target
    */
    public function addSubTab($a_id, $a_text, $a_link, $a_frame = "")
    {
        $this->sub_target[] = array("text" => $a_text, "link" => $a_link,
            "frame" => $a_frame, "dir_text" => true, "id" => $a_id, "cmdClass" => array());
    }

    /**
     * DEPRECATED.
     * @deprecated since version 5.2
     *
     * Use addTab/addSubTab and activateTab/activateSubTab.
     *
     * Activate a specific tab identified by name
     * This method overrides the definition in YOUR_OBJECT::getTabs() and deactivates all other tabs.
     *
     * @param	string		$a_text			menu item text
     */
    public function setTabActive($a_id)
    {
        foreach ($this->target as $key => $target) {
            $this->target[$key]['activate'] = $this->target[$key]['id'] == $a_id;
        }
        if ($a_id != "") {
            $this->manual_activation = true;
        } else {
            $this->manual_activation = false;
        }
        return true;
    }

    /**
    * Activate a specific tab identified its id
    *
    * @param	string		$a_text			menu item text
    */
    public function activateTab($a_id)
    {
        $this->setTabActive($a_id);
    }

    /**
    * @deprecated since version 5.2
    *
    * Use addTab/addSubTab and activateTab/activateSubTab.
    *
    * Activate a specific tab identified by name
    * This method overrides the definition in YOUR_OBJECT::getTabs() and deactivates all other tabs.
    *
    * @param	string		$a_text			menu item text
    * @param	boolean
    */
    public function setSubTabActive($a_text)
    {
        for ($i = 0; $i < count($this->sub_target);$i++) {
            $this->sub_target[$i]['activate'] = $this->sub_target[$i]['id'] == $a_text;
        }
        $this->subtab_manual_activation = true;
        return true;
    }

    /**
    * Activate a specific subtab identified its id
    *
    * @param	string		$a_text			menu item text
    */
    public function activateSubTab($a_id)
    {
        $this->setSubTabActive($a_id);
    }

    /**
    * Clear all already added sub tabs
    *
    * @param	boolean
    */
    public function clearSubTabs()
    {
        $this->sub_target = array();
        return true;
    }

    /**
     * get tabs code as html
     *
     * @param bool $a_after_tabs_anchor
     * @return string
    */
    public function getHTML($a_after_tabs_anchor = false)
    {
        return $this->__getHTML(false, $a_after_tabs_anchor);
    }
    
    /**
     * get sub tabs code as html
     * @return string
    */
    public function getSubTabHTML()
    {
        return $this->__getHTML(true);
    }

    /**
    * Add a non-tabbed link (outside of tabs at same level)
    *
    * @param	string		id
    * @param	string		text (no lang var!)
    * @param	string		link
    * @param	string		frame target
    */
    public function addNonTabbedLink($a_id, $a_text, $a_link, $a_frame = "")
    {
        $this->non_tabbed_link[] = array("text" => $a_text, "link" => $a_link,
            "frame" => $a_frame, "dir_text" => true, "id" => $a_id, "cmdClass" => array());
    }

    public function removeNonTabbedLinks()
    {
        $this->non_tabbed_link = [];
    }

    /**
     * get tabs code as html
     * @param bool $a_get_sub_tabs choose tabs or sub tabs
     * @param bool $a_after_tabs_anchor
     * @return string
     * @access Private
     */
    private function __getHTML($a_get_sub_tabs, $a_after_tabs_anchor = false)
    {
        global $DIC;

        $ilHelp = null;
        if (isset($DIC["ilHelp"])) {
            $ilHelp = $DIC["ilHelp"];
        }

        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilUser = null;
        if (isset($DIC["ilUser"])) {
            $ilUser = $DIC->user();
        }
        $ilPluginAdmin = null;
        if (isset($DIC["ilPluginAdmin"])) {
            $ilPluginAdmin = $DIC["ilPluginAdmin"];
        }

        // user interface hook [uihk]
        if (!$this->getSetupMode()) {
            $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
            foreach ($pl_names as $pl) {
                $ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
                $gui_class = $ui_plugin->getUIClassInstance();
                $resp = $gui_class->modifyGUI(
                    "",
                    $a_get_sub_tabs ? "sub_tabs" : "tabs",
                    array("tabs" => $this)
                );
            }
        }

        
        // user interface hook [uihk]
        if (!$this->getSetupMode()) {
            $cmd = $ilCtrl->getCmd();
            $cmdClass = $ilCtrl->getCmdClass();
        }

        if ($a_get_sub_tabs) {
            $tpl = new ilTemplate("tpl.sub_tabs.html", true, true, "Services/UIComponent/Tabs");
            $pre = "sub";
            $pre2 = "SUB_";
            $sr_pre = "sub_";
        } else {
            $tpl = new ilTemplate("tpl.tabs.html", true, true, "Services/UIComponent/Tabs");
            if ($a_after_tabs_anchor) {
                $tpl->touchBlock("after_tabs");
            }
            $pre = $pre2 = "";

            include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");

            // back 2 tab
            if ($this->back_2_title != "") {
                $tpl->setCurrentBlock("back_2_tab");
                $tpl->setVariable("BACK_2_ICON", ilGlyphGUI::get(ilGlyphGUI::PREVIOUS, ilGlyphGUI::NO_TEXT));
                $tpl->setVariable("BACK_2_TAB_LINK", ilUtil::secureUrl($this->back_2_target));
                $tpl->setVariable("BACK_2_TAB_TEXT", $this->back_2_title);
                if ($this->back_2_frame != "") {
                    $tpl->setVariable("BACK_2_TAB_TARGET", ' target="' . $this->back_2_frame . '" ');
                }

                $tpl->parseCurrentBlock();
            }
            
            // back tab
            if ($this->back_title != "") {
                $tpl->setCurrentBlock("back_tab");
                $tpl->setVariable("BACK_ICON", ilGlyphGUI::get(ilGlyphGUI::PREVIOUS, ilGlyphGUI::NO_TEXT));
                $tpl->setVariable("BACK_TAB_LINK", ilUtil::secureUrl($this->back_target));
                $tpl->setVariable("BACK_TAB_TEXT", $this->back_title);
                if ($this->back_frame != "") {
                    $tpl->setVariable("BACK_TAB_TARGET", ' target="' . $this->back_frame . '" ');
                }
                $tpl->parseCurrentBlock();
            }
        }

        $targets = $a_get_sub_tabs ? $this->sub_target : $this->target;

        $i = 0;
        
        // do not display one tab only
        if ((count($targets) > 1 || $this->force_one_tab) || ($this->back_title != "" && !$a_get_sub_tabs)
            || (count($this->non_tabbed_link) > 0 && !$a_get_sub_tabs)) {
            foreach ($targets as $target) {
                $i++;
                
                if (!is_array($target["cmd"])) {
                    $target["cmd"] = array($target["cmd"]);
                }
                if (!($a_get_sub_tabs ? $this->subtab_manual_activation : $this->manual_activation) &&
                    (in_array($cmd, $target["cmd"]) || ($target["cmd"][0] == "" && count($target["cmd"]) == 1)) &&
                    (in_array($cmdClass, $target["cmdClass"]) || !$target["cmdClass"])) {
                    $tabtype = $pre . "tabactive";
                } else {
                    $tabtype = $pre . "tabinactive";
                }
                
                if (($a_get_sub_tabs ? $this->subtab_manual_activation : $this->manual_activation) && $target["activate"]) {
                    $tabtype = $pre . "tabactive";
                }

                if ($tabtype == "tabactive" || $tabtype == "subtabactive") {
                    $tpl->setCurrentBlock("sel_text");
                    $tpl->setVariable("TXT_SELECTED", $lng->txt("stat_selected"));
                    $tpl->parseCurrentBlock();
                    
                    if (!$this->getSetupMode()) {
                        if ($a_get_sub_tabs) {
                            $part = ilHelpGUI::ID_PART_SUB_SCREEN;
                        } else {
                            $part = ilHelpGUI::ID_PART_SCREEN;
                        }
                        $ilHelp->setDefaultScreenId($part, $target["id"]);
                    }
                }
    
                $tpl->setCurrentBlock($pre . "tab");
                $tpl->setVariable("ID", $pre . "tab_" . $target["id"]);
                
                // tooltip
                if (!$this->getSetupMode()) {
                    $ttext = $ilHelp->getTabTooltipText($target["id"]);
                    if ($ttext != "") {
                        include_once("./Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
                        ilTooltipGUI::addTooltip(
                            $pre . "tab_" . $target["id"],
                            $ttext,
                            "",
                            "bottom center",
                            "top center",
                            false
                        );
                    }
                }

                // bs-patch: start
                $tabtype = in_array($tabtype, array("tabactive", "subtabactive"))
                    ? "active"
                    : "";
                // bs-patch: end

                $tpl->setVariable($pre2 . "TAB_TYPE", $tabtype);
                if (!$this->getSetupMode()) {
                    $hash = ($ilUser->getPref("screen_reader_optimization"))
                        ? "#after_" . $sr_pre . "tabs"
                        : "";
                }
                
                $tpl->setVariable($pre2 . "TAB_LINK", $target["link"] . $hash);
                if ($target["dir_text"]) {
                    $tpl->setVariable($pre2 . "TAB_TEXT", $target["text"]);
                } else {
                    $tpl->setVariable($pre2 . "TAB_TEXT", $lng->txt($target["text"]));
                }
                if ($target["frame"] != "") {
                    $tpl->setVariable($pre2 . "TAB_TARGET", ' target="' . $target["frame"] . '" ');
                }
                $tpl->parseCurrentBlock();
            }
            
            if ($a_get_sub_tabs) {
                $tpl->setVariable("TXT_SUBTABS", $lng->txt("subtabs"));
            } else {
                $tpl->setVariable("TXT_TABS", $lng->txt("tabs"));

                // non tabbed links
                include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
                foreach ($this->non_tabbed_link as $link) {
                    $tpl->setCurrentBlock("tab");
                    $tpl->setVariable("TAB_TYPE", "nontabbed");
                    $tpl->setVariable("TAB_ICON", " " . ilGlyphGUI::get(ilGlyphGUI::NEXT, ilGlyphGUI::NO_TEXT));
                    $tpl->setVariable("TAB_TEXT", $link["text"]);
                    $tpl->setVariable("TAB_LINK", $link["link"]);
                    $tpl->setVariable("TAB_TARGET", $link["frame"]);
                    $tpl->setVariable("ID", "nontab_" . $link["id"]);
                    $tpl->parseCurrentBlock();
                    
                    // tooltip
                    if (!$this->getSetupMode()) {
                        $ttext = $ilHelp->getTabTooltipText($link["id"]);
                        if ($ttext != "") {
                            include_once("./Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
                            ilTooltipGUI::addTooltip(
                                "nontab_" . $link["id"],
                                $ttext,
                                "",
                                "bottom center",
                                "top center",
                                false
                            );
                        }
                    }
                }
            }

            return $tpl->get();
        } else {
            return "";
        }
    }
    
    public function getActiveTab()
    {
        foreach ($this->target as $i => $target) {
            if ($this->target[$i]['activate']) {
                return $this->target[$i]['id'];
            }
        }
    }
    
    public function hasTabs()
    {
        return (bool) sizeof($this->target);
    }
}
