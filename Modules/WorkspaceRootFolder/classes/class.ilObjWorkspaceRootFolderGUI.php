<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Modules/WorkspaceFolder/classes/class.ilObjWorkspaceFolderGUI.php";

/**
* Class ilObjWorkspaceRootFolderGUI
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id: class.ilObjRootFolderGUI.php 27165 2011-01-04 13:48:35Z jluetzen $Id: class.ilObjRootFolderGUI.php,v 1.13 2006/03/10 09:22:58 akill Exp $
*
* @ilCtrl_Calls ilObjWorkspaceRootFolderGUI: ilCommonActionDispatcherGUI, ilObjectOwnershipManagementGUI
*
* @extends ilObject2GUI
*/
class ilObjWorkspaceRootFolderGUI extends ilObjWorkspaceFolderGUI
{

    /**
     * Constructor
     */
    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);
        global $DIC;

        $this->help = $DIC["ilHelp"];
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
    }

    public function getType()
    {
        return "wsrt";
    }
    
    public function setTabs($a_show_settings = false)
    {
        $ilHelp = $this->help;

        parent::setTabs(false);
        $ilHelp->setScreenIdComponent("wsrt");
    }
    
    protected function setTitleAndDescription()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        
        $tpl->setTitle($lng->txt("wsp_personal_workspace"));
        $tpl->setTitleIcon(ilUtil::getImagePath("icon_wsrt.svg"), $title);
        $tpl->setDescription($lng->txt("wsp_personal_workspace_description"));
    }
}
