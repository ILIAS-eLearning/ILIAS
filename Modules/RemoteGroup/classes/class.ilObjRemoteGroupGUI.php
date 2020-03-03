<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/WebServices/ECS/classes/class.ilRemoteObjectBaseGUI.php');

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ilCtrl_Calls ilObjRemoteGroupGUI: ilPermissionGUI, ilInfoScreenGUI
* @ilCtrl_Calls ilObjRemoteGroupGUI: ilCommonActionDispatcherGUI
* @ingroup ModulesRemoteGroup
*/

class ilObjRemoteGroupGUI extends ilRemoteObjectBaseGUI
{
    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $this->lng->loadLanguageModule('rgrp');
        $this->lng->loadLanguageModule('grp');
    }
    
    public function getType()
    {
        return 'rgrp';
    }

    protected function addCustomInfoFields(ilInfoScreenGUI $a_info)
    {
        $a_info->addProperty($this->lng->txt('grp_visibility'), $this->availabilityToString());
    }
    
    protected function availabilityToString()
    {
        switch ($this->object->getAvailabilityType()) {
            case ilObjRemoteGroup::ACTIVATION_OFFLINE:
                return $this->lng->txt('offline');
            
            case ilObjRemoteGroup::ACTIVATION_UNLIMITED:
                return $this->lng->txt('grp_unlimited');
            
            case ilObjRemoteGroup::ACTIVATION_LIMITED:
                return ilDatePresentation::formatPeriod(
                    new ilDateTime($this->object->getStartingTime(), IL_CAL_UNIX),
                    new ilDateTime($this->object->getEndingTime(), IL_CAL_UNIX)
                );
        }
        return '';
    }
    
    protected function addCustomEditForm(ilPropertyFormGUI $a_form)
    {
        $radio_grp = new ilRadioGroupInputGUI($this->lng->txt('grp_visibility'), 'activation_type');
        $radio_grp->setValue($this->object->getAvailabilityType());
        $radio_grp->setDisabled(true);

        $radio_opt = new ilRadioOption($this->lng->txt('grp_visibility_unvisible'), ilObjRemoteGroup::ACTIVATION_OFFLINE);
        $radio_grp->addOption($radio_opt);

        $radio_opt = new ilRadioOption($this->lng->txt('grp_visibility_limitless'), ilObjRemoteGroup::ACTIVATION_UNLIMITED);
        $radio_grp->addOption($radio_opt);

        // :TODO: not supported in ECS yet
        $radio_opt = new ilRadioOption($this->lng->txt('grp_visibility_until'), ilObjRemoteGroup::ACTIVATION_LIMITED);
        
        $start = new ilDateTimeInputGUI($this->lng->txt('grp_start'), 'start');
        $start->setDate(new ilDateTime(time(), IL_CAL_UNIX));
        $start->setDisabled(true);
        $start->setShowTime(true);
        $radio_opt->addSubItem($start);
        $end = new ilDateTimeInputGUI($this->lng->txt('grp_end'), 'end');
        $end->setDate(new ilDateTime(time(), IL_CAL_UNIX));
        $end->setDisabled(true);
        $end->setShowTime(true);
        $radio_opt->addSubItem($end);
        
        $radio_grp->addOption($radio_opt);
        $a_form->addItem($radio_grp);
    }
}
