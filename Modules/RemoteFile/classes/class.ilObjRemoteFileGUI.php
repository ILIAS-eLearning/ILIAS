<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/WebServices/ECS/classes/class.ilRemoteObjectBaseGUI.php');

/**
* Remote file GUI
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ilCtrl_Calls ilObjRemoteFileGUI: ilPermissionGUI, ilInfoScreenGUI
* @ilCtrl_Calls ilObjRemoteFileGUI: ilCommonActionDispatcherGUI
* @ingroup ModulesRemoteFile
*/

class ilObjRemoteFileGUI extends ilRemoteObjectBaseGUI
{
    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $this->lng->loadLanguageModule('rfil');
        $this->lng->loadLanguageModule('file');
    }
    
    public function getType()
    {
        return 'rfil';
    }
    
    protected function addCustomInfoFields(ilInfoScreenGUI $a_info)
    {
        $a_info->addProperty($this->lng->txt('version'), $this->object->getVersion());
        $a_info->addProperty(
            $this->lng->txt('rfil_version_tstamp'),
            ilDatePresentation::formatDate(new ilDateTime($this->object->getVersionDateTime(), IL_CAL_UNIX))
        );
    }
}
