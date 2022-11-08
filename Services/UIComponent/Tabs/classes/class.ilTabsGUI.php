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
 * Tabs GUI
 * @author Alexander Killing <killing@leifos.de>
 *
 * @deprecated 10
 */
class ilTabsGUI
{
    protected ilCtrl $ctrl;

    public string $target_script;
    public string $obj_type;
    public ilGlobalTemplateInterface $tpl;
    public ilLanguage $lng;
    public array $tabs;
    public array $target = array();
    public array $sub_target = array();
    public array $non_tabbed_link = array();
    public bool $setup_mode = false;
    protected bool $force_one_tab = false;
    protected bool $manual_activation;
    protected bool $subtab_manual_activation;
    protected bool $sub_tabs;
    protected string $temp_var;
    protected string $back_title;
    public string $back_target;
    protected string $back_2_target;
    protected string $back_2_title;
    protected string $back_2_frame;
    protected string $back_frame;

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $lng = $DIC->language();
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

    public function setSetupMode(bool $a_val): void
    {
        $this->setup_mode = $a_val;
    }

    public function getSetupMode(): bool
    {
        return $this->setup_mode;
    }

    public function setBackTarget(
        string $a_title,
        string $a_target,
        string $a_frame = ""
    ): void {
        $this->back_title = $a_title;
        $this->back_target = $a_target;
        $this->back_frame = $a_frame;
    }

    public function setBack2Target(
        string $a_title,
        string $a_target,
        string $a_frame = ""
    ): void {
        $this->back_2_title = $a_title;
        $this->back_2_target = $a_target;
        $this->back_2_frame = $a_frame;
    }

    public function setForcePresentationOfSingleTab(bool $a_val): void
    {
        $this->force_one_tab = $a_val;
    }

    public function getForcePresentationOfSingleTab(): bool
    {
        return $this->force_one_tab;
    }

    /**
     * @deprecated since version 5.0
     * @param string|array $a_cmd
     * @param string|array $a_cmdClass
     */
    public function addTarget(
        string $a_text,
        string $a_link,
        $a_cmd = "",
        $a_cmdClass = "",
        string $a_frame = "",
        bool $a_activate = false,
        bool $a_dir_text = false
    ): void {
        if (!$a_cmdClass) {
            $a_cmdClass = array();
        }
        $a_cmdClass = !is_array($a_cmdClass) ? array(strtolower($a_cmdClass)) : $a_cmdClass;

        if ($a_activate) {
            $this->manual_activation = true;
        }
        $this->target[] = array("text" => $a_text, "link" => $a_link,
            "cmd" => $a_cmd, "cmdClass" => $a_cmdClass, "frame" => $a_frame,
            "activate" => $a_activate, "dir_text" => $a_dir_text, "id" => $a_text);
    }

    /**
     * Add a Tab
     */
    public function addTab(
        string $a_id,
        string $a_text,
        string $a_link,
        string $a_frame = ""
    ): void {
        $this->target[] = array("text" => $a_text, "link" => $a_link,
            "frame" => $a_frame, "dir_text" => true, "id" => $a_id, "cmdClass" => array());
    }

    /**
     * Remove a tab identified by its id.
     */
    public function removeTab(string $a_id): void
    {
        foreach ($this->target as $key => $target) {
            if ($target['id'] == $a_id) {
                unset($this->target[$key]);
            }
        }
    }

    /**
     * Remove a subtab identified by its id.
     */
    public function removeSubTab(string $a_id): void
    {
        foreach ($this->sub_target as $i => $sub_target) {
            if ($this->sub_target[$i]['id'] == $a_id) {
                unset($this->sub_target[$i]);
            }
        }
    }

    /**
     * Replace a tab.
     */
    public function replaceTab(
        string $a_old_id,
        string $a_new_id,
        string $a_text,
        string $a_link,
        string $a_frame = ''
    ): void {
        for ($i = 0, $iMax = count($this->target); $i < $iMax; $i++) {
            if ($this->target[$i]['id'] == $a_old_id) {
                $this->target[$i] = array(
                    "text" => $a_text,
                    "link" => $a_link,
                    "frame" => $a_frame,
                    "dir_text" => true,
                    "id" => $a_new_id,
                    "cmdClass" => array());
            }
        }
    }

    /**
     * clear all targets
     */
    public function clearTargets(): void
    {
        global $DIC;

        $ilHelp = $DIC["ilHelp"] ?? null;

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
     * @deprecated
     * @param string|array $a_cmd
     * @param string|array $a_cmdClass
     */
    public function addSubTabTarget(
        string $a_text,
        string $a_link,
        $a_cmd = "",
        $a_cmdClass = "",
        string $a_frame = "",
        bool $a_activate = false,
        bool $a_dir_text = false
    ): void {
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

    public function addSubTab(
        string $a_id,
        string $a_text,
        string $a_link,
        string $a_frame = ""
    ): void {
        $this->sub_target[] = array("text" => $a_text, "link" => $a_link,
            "frame" => $a_frame, "dir_text" => true, "id" => $a_id, "cmdClass" => array());
    }

    /**
     * @deprecated since version 5.2
     */
    public function setTabActive(string $a_id): void
    {
        foreach ($this->target as $key => $target) {
            $this->target[$key]['activate'] = $this->target[$key]['id'] === $a_id;
        }
        if ($a_id !== "") {
            $this->manual_activation = true;
        } else {
            $this->manual_activation = false;
        }
    }

    public function activateTab(string $a_id): void
    {
        $this->setTabActive($a_id);
    }

    /**
     * @deprecated since version 5.2
     */
    public function setSubTabActive(string $a_text): void
    {
        for ($i = 0, $iMax = count($this->sub_target); $i < $iMax; $i++) {
            $this->sub_target[$i]['activate'] = $this->sub_target[$i]['id'] === $a_text;
        }
        $this->subtab_manual_activation = true;
    }

    public function activateSubTab(string $a_id): void
    {
        $this->setSubTabActive($a_id);
    }

    public function clearSubTabs(): void
    {
        $this->sub_target = array();
    }

    public function getHTML(bool $a_after_tabs_anchor = false): string
    {
        return $this->__getHTML(false, $a_after_tabs_anchor);
    }

    public function getSubTabHTML(): string
    {
        return $this->__getHTML(true);
    }

    public function addNonTabbedLink(
        string $a_id,
        string $a_text,
        string $a_link,
        string $a_frame = ""
    ): void {
        $this->non_tabbed_link[] = array("text" => $a_text, "link" => $a_link,
            "frame" => $a_frame, "dir_text" => true, "id" => $a_id, "cmdClass" => array());
    }

    public function removeNonTabbedLinks(): void
    {
        $this->non_tabbed_link = [];
    }

    private function __getHTML(
        bool $a_get_sub_tabs,
        bool $a_after_tabs_anchor = false
    ): string {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $cmd = null;
        $cmdClass = null;
        $sr_pre = "";
        $hash = "";

        $ilHelp = $DIC["ilHelp"] ?? null;

        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilUser = null;
        if (isset($DIC["ilUser"])) {
            $ilUser = $DIC->user();
        }
        $component_factory = $DIC["component.factory"] ?? null;

        // user interface hook [uihk]
        if ($component_factory && !$this->getSetupMode()) {
            foreach ($component_factory->getActivePluginsInSlot("uihk") as $plugin) {
                $gui_class = $plugin->getUIClassInstance();
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

            // back 2 tab
            if ($this->back_2_title !== "") {
                $tpl->setCurrentBlock("back_2_tab");
                $tpl->setVariable("BACK_2_ICON", ilGlyphGUI::get(ilGlyphGUI::PREVIOUS, ilGlyphGUI::NO_TEXT));
                $tpl->setVariable("BACK_2_TAB_LINK", $this->back_2_target);
                $tpl->setVariable("BACK_2_TAB_TEXT", $this->back_2_title);
                if ($this->back_2_frame !== "") {
                    $tpl->setVariable("BACK_2_TAB_TARGET", ' target="' . $this->back_2_frame . '" ');
                }

                $tpl->parseCurrentBlock();
            }

            // back tab
            if ($this->back_title !== "") {
                $tpl->setCurrentBlock("back_tab");
                $tpl->setVariable("BACK_ICON", ilGlyphGUI::get(ilGlyphGUI::PREVIOUS, ilGlyphGUI::NO_TEXT));
                $tpl->setVariable("BACK_TAB_LINK", $this->back_target);
                $tpl->setVariable("BACK_TAB_TEXT", $this->back_title);
                if ($this->back_frame !== "") {
                    $tpl->setVariable("BACK_TAB_TARGET", ' target="' . $this->back_frame . '" ');
                }
                $tpl->parseCurrentBlock();
            }
        }

        $targets = $a_get_sub_tabs ? $this->sub_target : $this->target;

        $i = 0;

        // do not display one tab only
        if ((count($targets) > 1 || $this->force_one_tab) || ($this->back_title !== "" && !$a_get_sub_tabs)
            || (count($this->non_tabbed_link) > 0 && !$a_get_sub_tabs)) {
            foreach ($targets as $target) {
                $i++;

                if (isset($target['cmd'])) {
                    if (!is_array($target['cmd'])) {
                        $target['cmd'] = [$target['cmd']];
                    }
                } else {
                    $target['cmd'] = [];
                }

                if ($this->isTabActive($a_get_sub_tabs, $target, $cmd, $cmdClass)) {
                    $tabtype = $pre . "tabactive";
                } else {
                    $tabtype = $pre . "tabinactive";
                }

                if (($a_get_sub_tabs ? $this->subtab_manual_activation : $this->manual_activation) && ($target["activate"] ?? false)) {
                    $tabtype = $pre . "tabactive";
                }

                if ($tabtype === "tabactive" || $tabtype === "subtabactive") {
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
                    if ($ttext !== "") {
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
                $hash = "";
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
                $tpl->setVariable("TXT_SUBTABS", $this->getTabTextOfId($this->getActiveTab()).": ".$lng->txt("subtabs"));
            } else {
                $tpl->setVariable("TXT_TABS", $lng->txt("tabs"));

                // non tabbed links
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
                        if ($ttext !== "") {
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

    protected function getTabTextOfId(string $id): string
    {
        foreach ($this->target as $i => $target) {
            if ($this->target[$i]['id'] == $id) {
                if ($target["dir_text"]) {
                    return $target["text"];
                } else {
                    return $this->lng->txt($target["text"]);
                }
            }
        }
        return "";
    }

    public function getActiveTab(): string
    {
        foreach ($this->target as $i => $target) {
            if ($this->target[$i]['activate']) {
                return $this->target[$i]['id'];
            }
        }
        return "";
    }

    public function hasTabs(): bool
    {
        return $this->target !== [];
    }

    private function isTabActive(bool $isSubTabsContext, array $target, ?string $cmd, ?string $cmdClass): bool
    {
        if (($isSubTabsContext && $this->subtab_manual_activation) || (!$isSubTabsContext && $this->manual_activation)) {
            return false;
        }

        $cmdClass = (string) $cmdClass;
        $cmd = (string) $cmd;

        $targetMatchesCmdClass = (
            !$target['cmdClass'] ||
            in_array(strtolower($cmdClass), array_map('strtolower', $target['cmdClass']), true)
        );

        $targetMatchesCmd = (
            in_array(strtolower($cmd), array_map('strtolower', $target['cmd']), true) ||
            (count($target['cmd']) === 1 && $target['cmd'][0] === '')
        );

        return $targetMatchesCmd && $targetMatchesCmdClass;
    }
}
