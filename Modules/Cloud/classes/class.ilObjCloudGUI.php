<?php
declare(strict_types=0);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/


/**
 * Class ilObjCloudGUI
 * @author       Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 * @author  Martin Studer martin@fluxlabs.ch
 * @ilCtrlStructureCalls(
 *		children={
 *			"ilPermissionGUI", "ilNoteGUI", "ilInfoScreenGUI", "ilObjectCopyGUI", "ilCommonActionDispatcherGUI",
 *			"ilCloudPluginUploadGUI","ilCloudPluginCreateFolderGUI", "ilCloudPluginSettingsGUI", "ilCloudPluginDeleteGUI",
 *			"ilCloudPluginActionListGUI","ilCloudPluginItemCreationListGUI", "ilCloudPluginFileTreeGUI",
 *			"ilCloudPluginInitGUI","ilCloudPluginHeaderActionGUI", "ilCloudPluginInfoScreenGUI",
 *		}
 * )
 * @extends      ilObject2GUI
 */
class ilObjCloudGUI extends ilObject2GUI
{
    /**
     * @param int $a_id
     * @param int $a_id_type
     * @param int $a_parent_node_id
     */
    public function __construct(int $a_id = 0, int $a_id_type = self::REPOSITORY_NODE_ID, int $a_parent_node_id = 0)
    {
        global $DIC;
        $DIC['lng']->loadLanguageModule('cld');

        parent::__construct($a_id, $a_id_type, $a_parent_node_id);
    }

    public function executeCommand() : void
    {
        //Only deleting items remains possible
        if ($this->ctrl->getCmd() == "delete") {
            $this->delete();
            return;
        }
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('abandoned'), true);
        ilObjectGUI::redirectToRefId($this->parent_id);
    }

    public function getType() : string
    {
        return 'cld';
    }
}
