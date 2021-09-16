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
 * Class ilContainerLinkListGUI
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilContainerLinkListGUI:
 */
class ilContainerLinkListGUI
{
    protected ilLanguage $lng;
    protected ilTree $tree;
    protected ilCtrl $ctrl;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tree = $DIC->repositoryTree();
        $ilCtrl = $DIC->ctrl();
        
        $this->ctrl = &$ilCtrl;
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }
    
    public function show() : void
    {
        $lng = $this->lng;
        $tree = $this->tree;
        $cnt = [];
        
        $tpl = new ilGlobalTemplate(
            "tpl.container_link_help.html",
            true,
            true,
            "Services/Container"
        );
        
        $type_ordering = array(
            "cat", "fold", "crs", "grp", "chat", "frm", "lres",
            "glo", "webr", "file", "exc",
            "tst", "svy", "mep", "qpl", "spl");
            
        $childs = $tree->getChilds($_GET["ref_id"]);
        foreach ($childs as $child) {
            if (in_array($child["type"], array("lm", "sahs", "htlm"))) {
                $cnt["lres"]++;
            } else {
                $cnt[$child["type"]]++;
            }
        }
            
        $tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        $tpl->setVariable("TXT_HELP_HEADER", $lng->txt("help"));
        foreach ($type_ordering as $type) {
            $tpl->setCurrentBlock("row");
            if ($type != "lres") {
                $tpl->setVariable("TYPE", $lng->txt("objs_" . $type) .
                    " (" . ((int) $cnt[$type]) . ")");
            } else {
                $tpl->setVariable("TYPE", $lng->txt("obj_lrss") .
                    " (" . ((int) $cnt["lres"]) . ")");
            }
            $tpl->setVariable("TXT_LINK", "[list-" . $type . "]");
            $tpl->parseCurrentBlock();
        }
        $tpl->printToStdout();
        exit;
    }
}
