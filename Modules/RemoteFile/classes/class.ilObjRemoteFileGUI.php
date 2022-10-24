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

class ilObjRemoteFileGUI extends ilRemoteObjectBaseGUI implements ilCtrlBaseClassInterface
{
    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $this->lng->loadLanguageModule('rfil');
        $this->lng->loadLanguageModule('file');
    }

    public function getType(): string
    {
        return 'rfil';
    }

    protected function addCustomInfoFields(ilInfoScreenGUI $a_info): void
    {
        $a_info->addProperty($this->lng->txt('version'), $this->object->getVersion());
        $a_info->addProperty(
            $this->lng->txt('rfil_version_tstamp'),
            ilDatePresentation::formatDate(new ilDateTime($this->object->getVersionDateTime(), IL_CAL_UNIX))
        );
    }
}
