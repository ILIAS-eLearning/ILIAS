<?php

declare(strict_types=1);

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
 * Class ilObjChatroomAdminGUI
 * GUI class for chatroom objects.
 * @author            Jan Posselt <jposselt at databay.de>
 * @version           $Id$
 * @ilCtrl_Calls      ilObjChatroomAdminGUI: ilMDEditorGUI, ilInfoScreenGUI, ilPermissionGUI, ilObjectCopyGUI
 * @ilCtrl_Calls      ilObjChatroomAdminGUI: ilExportGUI, ilObjChatroomGUI
 * @ilCtrl_IsCalledBy ilObjChatroomAdminGUI: ilRepositoryGUI, ilAdministrationGUI
 * @ingroup           ModulesChatroom
 */
class ilObjChatroomAdminGUI extends ilChatroomObjectGUI
{
    public function __construct($data = null, ?int $id = 0, bool $call_by_reference = true, bool $prepare_output = true)
    {
        $this->type = 'chta';
        parent::__construct($data, $id, $call_by_reference, false);
        $this->lng->loadLanguageModule('chatroom_adm');
    }

    /**
     * @param int|string $ref_id
     */
    public static function _goto($ref_id): void
    {
        ilObjectGUI::_gotoRepositoryNode((int) $ref_id, 'view');
    }

    protected function getObjectDefinition(): ilChatroomObjectDefinition
    {
        return ilChatroomObjectDefinition::getDefaultDefinitionWithCustomGUIPath(
            'Chatroom',
            'admin'
        );
    }

    public function executeCommand(): void
    {
        $next_class = strtolower($this->ctrl->getNextClass());

        $tabFactory = new ilChatroomTabGUIFactory($this);

        switch ($next_class) {
            case strtolower(ilPermissionGUI::class):
                $tabFactory->getAdminTabsForCommand($this->ctrl->getCmd());
                $this->prepareOutput();
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case strtolower(ilObjChatroomGUI::class):
                $this->prepareOutput();
                $perm_gui = new ilObjChatroomGUI(
                    null,
                    $this->getRefId(),
                    true,
                    false
                );
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                $tabFactory->getAdminTabsForCommand($this->ctrl->getCmd());
                $res = explode('-', $this->ctrl->getCmd(), 2);
                if (!array_key_exists(1, $res)) {
                    $res[1] = '';
                }

                $this->dispatchCall($res[0], $res[1]);
        }
    }

    public function getConnector(): ilChatroomServerConnector
    {
        return new ilChatroomServerConnector(ilChatroomServerSettings::loadDefault());
    }

    public function getRefId(): int
    {
        return $this->object->getRefId();
    }
}
