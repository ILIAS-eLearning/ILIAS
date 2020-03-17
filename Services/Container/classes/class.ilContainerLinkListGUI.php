<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/


/**
* Class ilContainerLinkListGUI
*
* @author Stefan Meyer <alex.killing@gmx.de>

* @version $Id$
*
* @ilCtrl_Calls ilContainerLinkListGUI:
*
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
        
        $tpl = new ilTemplate(
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
        $tpl->show();
        exit;
    }
} // END class.ilContainerLinkListGUI
