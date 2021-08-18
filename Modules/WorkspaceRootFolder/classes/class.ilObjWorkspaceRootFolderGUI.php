<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilObjWorkspaceRootFolderGUI
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ilCtrl_Calls ilObjWorkspaceRootFolderGUI: ilCommonActionDispatcherGUI, ilObjectOwnershipManagementGUI
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
        
        $tpl->setTitle($lng->txt("mm_personal_and_shared_r"));
        $tpl->setTitleIcon(ilUtil::getImagePath("icon_wsrt.svg"), $title);
        $tpl->setDescription($lng->txt("wsp_personal_resources_description"));
    }
}
