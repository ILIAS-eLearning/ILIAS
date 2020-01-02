<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/WebServices/ECS/classes/class.ilRemoteObjectBaseGUI.php');

/**
* Remote learning module GUI
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ilCtrl_Calls ilObjRemoteLearningModuleGUI: ilPermissionGUI, ilInfoScreenGUI
* @ilCtrl_Calls ilObjRemoteLearningModuleGUI: ilCommonActionDispatcherGUI
* @ingroup ModulesRemoteLearningModule
*/

class ilObjRemoteLearningModuleGUI extends ilRemoteObjectBaseGUI
{
    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $this->lng->loadLanguageModule('rlm');
        // $this->lng->loadLanguageModule('lres');
    }
    
    public function getType()
    {
        return 'rlm';
    }
    
    protected function addCustomInfoFields(ilInfoScreenGUI $a_info)
    {
        $a_info->addProperty($this->lng->txt('ecs_availability'), $this->availabilityToString());
    }
    
    protected function availabilityToString()
    {
        switch ($this->object->getAvailabilityType()) {
            case ilObjRemoteLearningModule::ACTIVATION_OFFLINE:
                return $this->lng->txt('offline');
            
            case ilObjRemoteLearningModule::ACTIVATION_ONLINE:
                return $this->lng->txt('online');
        }
        return '';
    }
    
    protected function addCustomEditForm(ilPropertyFormGUI $a_form)
    {
        $radio_grp = new ilRadioGroupInputGUI($this->lng->txt('ecs_availability'), 'activation_type');
        $radio_grp->setValue($this->object->getAvailabilityType());
        $radio_grp->setDisabled(true);

        $radio_opt = new ilRadioOption($this->lng->txt('offline'), ilObjRemoteLearningModule::ACTIVATION_OFFLINE);
        $radio_grp->addOption($radio_opt);

        $radio_opt = new ilRadioOption($this->lng->txt('online'), ilObjRemoteLearningModule::ACTIVATION_ONLINE);
        $radio_grp->addOption($radio_opt);

        $a_form->addItem($radio_grp);
    }
}
