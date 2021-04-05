<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilContainerLinkListGUI
 *
 * @author Stefan Meyer <alex.killing@gmx.de>
 * @ilCtrl_Calls ilContainerLinkListGUI:
 */
class ilContainerLinkListGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTree
     */
    protected $tree;

    public $ctrl;

    /**
    * Constructor
    * @access public
    */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tree = $DIC->repositoryTree();
        $ilCtrl = $DIC->ctrl();
        
        $this->ctrl = &$ilCtrl;
    }

    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        //$this->prepareOutput();

        switch ($next_class) {
            default:
                $this->$cmd();

                break;
        }
        return true;
    }
    
    public function show()
    {
        $lng = $this->lng;
        $tree = $this->tree;
        
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
            $tpl->setVariable("ROWCOL", "tblrow" . ((($i++) % 2) + 1));
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
} // END class.ilContainerLinkListGUI
