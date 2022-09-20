<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
* Remote wiki GUI
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ilCtrl_Calls ilObjRemoteWikiGUI: ilPermissionGUI, ilInfoScreenGUI
* @ilCtrl_Calls ilObjRemoteWikiGUI: ilCommonActionDispatcherGUI
* @ingroup ModulesRemoteWiki
*/

class ilObjRemoteWikiGUI extends ilRemoteObjectBaseGUI implements ilCtrlBaseClassInterface
{
    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $this->lng->loadLanguageModule('rwik');
        $this->lng->loadLanguageModule('wiki');
    }

    public function getType(): string
    {
        return 'rwik';
    }

    protected function addCustomInfoFields(ilInfoScreenGUI $a_info): void
    {
        $a_info->addProperty($this->lng->txt('ecs_availability'), $this->availabilityToString());
    }

    protected function availabilityToString()
    {
        switch ($this->object->getAvailabilityType()) {
            case ilObjRemoteWiki::ACTIVATION_OFFLINE:
                return $this->lng->txt('offline');

            case ilObjRemoteWiki::ACTIVATION_ONLINE:
                return $this->lng->txt('online');
        }
        return '';
    }

    protected function addCustomEditForm(ilPropertyFormGUI $a_form): void
    {
        $radio_grp = new ilRadioGroupInputGUI($this->lng->txt('ecs_availability'), 'activation_type');
        $radio_grp->setValue($this->object->getAvailabilityType());
        $radio_grp->setDisabled(true);

        $radio_opt = new ilRadioOption($this->lng->txt('offline'), ilObjRemoteWiki::ACTIVATION_OFFLINE);
        $radio_grp->addOption($radio_opt);

        $radio_opt = new ilRadioOption($this->lng->txt('online'), ilObjRemoteWiki::ACTIVATION_ONLINE);
        $radio_grp->addOption($radio_opt);

        $a_form->addItem($radio_grp);
    }
}
