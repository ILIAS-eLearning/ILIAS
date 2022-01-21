<?php declare(strict_types=1);
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjChatroomAdminGUI
 * GUI class for chatroom objects.
 * @author            Jan Posselt <jposselt at databay.de>
 * @version           $Id$
 * @ilCtrl_Calls      ilObjChatroomAdminGUI: ilMDEditorGUI, ilInfoScreenGUI, ilPermissionGUI, ilObjectCopyGUI
 * @ilCtrl_Calls      ilObjChatroomAdminGUI: ilExportGUI
 * @ilCtrl_IsCalledBy ilObjChatroomAdminGUI: ilRepositoryGUI, ilAdministrationGUI
 * @ingroup           ModulesChatroom
 */
class ilObjChatroomAdminGUI extends ilChatroomObjectGUI implements ilCtrlBaseClassInterface
{
    public function __construct($a_data = null, $a_id = null, $a_call_by_reference = true)
    {
        $this->type = 'chta';
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        $this->lng->loadLanguageModule('chatroom_adm');
    }

    /**
     * @param int $ref_id
     */
    public static function _goto($ref_id) : void
    {
        ilObjectGUI::_gotoRepositoryNode((int) $ref_id, 'view');
    }

    protected function getObjectDefinition() : ilChatroomObjectDefinition
    {
        return ilChatroomObjectDefinition::getDefaultDefinitionWithCustomGUIPath(
            'Chatroom',
            'admin'
        );
    }

    public function executeCommand()
    {
        $next_class = strtolower($this->ctrl->getNextClass());

        $tabFactory = new ilChatroomTabGUIFactory($this);
        $tabFactory->getAdminTabsForCommand($this->ctrl->getCmd());

        switch ($next_class) {
            case strtolower(ilPermissionGUI::class):
                $this->prepareOutput();
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                $res = explode('-', $this->ctrl->getCmd(), 2);
                if (!array_key_exists(1, $res)) {
                    $res[1] = '';
                }

                $this->dispatchCall($res[0], $res[1]);
        }
    }

    public function getConnector() : ilChatroomServerConnector
    {
        return new ilChatroomServerConnector(ilChatroomServerSettings::loadDefault());
    }

    public function getRefId() : int
    {
        return $this->object->getRefId();
    }
}
