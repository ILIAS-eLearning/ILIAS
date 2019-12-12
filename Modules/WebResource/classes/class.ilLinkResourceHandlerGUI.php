<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* Handles user interface for link resources.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilLinkResourceHandlerGUI: ilObjLinkResourceGUI
*
* @ingroup ModulesWebResource
*/
class ilLinkResourceHandlerGUI
{
    public function __construct()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        $ilias = $DIC['ilias'];
        $ilNavigationHistory = $DIC['ilNavigationHistory'];

        // initialisation stuff
        $this->ctrl =&$ilCtrl;
    }
    
    /**
    * execute command
    */
    public function executeCommand()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        $tpl = $DIC['tpl'];
        $ilNavigationHistory = $DIC['ilNavigationHistory'];
        
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);
        if ($next_class == "") {
            $this->ctrl->setCmdClass("ilobjlinkresourcegui");
            $next_class = $this->ctrl->getNextClass($this);
        }

        // add entry to navigation history
        if ($ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            $ilNavigationHistory->addItem(
                $_GET["ref_id"],
                "ilias.php?baseClass=ilLinkResourceHandlerGUI&cmd=infoScreen&ref_id=" . $_GET["ref_id"],
                "webr"
            );
        }

        switch ($next_class) {
            case 'ilobjlinkresourcegui':
                require_once "./Modules/WebResource/classes/class.ilObjLinkResourceGUI.php";
                $link_gui = new ilObjLinkResourceGUI((int) $_GET["ref_id"], ilObjLinkResourceGUI::REPOSITORY_NODE_ID);
                $this->ctrl->forwardCommand($link_gui);
                break;
        }

        $tpl->show();
    }
}
