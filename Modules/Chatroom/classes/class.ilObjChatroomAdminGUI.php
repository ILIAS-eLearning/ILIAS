<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObjectGUI.php';
require_once 'Modules/Chatroom/classes/class.ilObjChatroom.php';
require_once 'Modules/Chatroom/classes/class.ilObjChatroomAccess.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomObjectGUI.php';

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
class ilObjChatroomAdminGUI extends ilChatroomObjectGUI
{
    /**
     * {@inheritdoc}
     */
    public function __construct($a_data = null, $a_id = null, $a_call_by_reference = true)
    {
        global $DIC;

        $DIC->language()->loadLanguageModule('chatroom_adm');

        if ($a_data == null) {
            if ($_GET['serverInquiry']) {
                require_once dirname(__FILE__) . '/class.ilChatroomServerHandler.php';
                new ilChatroomServerHandler();
                return;
            }
        }

        $this->type = 'chta';
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
    }

    /**
     * Overwrites $_GET['ref_id'] with given $ref_id.
     * @param int $ref_id
     */
    public static function _goto($ref_id)
    {
        include_once 'Services/Object/classes/class.ilObjectGUI.php';
        ilObjectGUI::_gotoRepositoryNode($ref_id, 'view');
    }

    /**
     * Returns object definition by calling getDefaultDefinitionWithCustomGUIPath
     * method in ilChatroomObjectDefinition.
     * @return ilChatroomObjectDefinition
     */
    protected function getObjectDefinition()
    {
        return ilChatroomObjectDefinition::getDefaultDefinitionWithCustomGUIPath(
            'Chatroom',
            'admin'
        );
    }

    /**
     * Returns empty array.
     * @return array
     */
    public function _forwards()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function executeCommand()
    {
        global $DIC;

        $next_class = $DIC->ctrl()->getNextClass();

        require_once 'Modules/Chatroom/classes/class.ilChatroomTabGUIFactory.php';

        $tabFactory = new ilChatroomTabGUIFactory($this);
        $tabFactory->getAdminTabsForCommand($DIC->ctrl()->getCmd());

        switch ($next_class) {
            case 'ilpermissiongui':
                include_once 'Services/AccessControl/classes/class.ilPermissionGUI.php';
                $this->prepareOutput();
                $perm_gui = new ilPermissionGUI($this);
                $DIC->ctrl()->forwardCommand($perm_gui);
                break;

            default:
                $res = explode('-', $DIC->ctrl()->getCmd(), 2);
                if (!array_key_exists(1, $res)) {
                    $res[1] = '';
                }
                $this->dispatchCall($res[0], $res[1]);
        }
    }

    /**
     * @return ilChatroomServerConnector
     */
    public function getConnector()
    {
        require_once 'Modules/Chatroom/classes/class.ilChatroomServerConnector.php';
        require_once 'Modules/Chatroom/classes/class.ilChatroomServerSettings.php';

        $settings = ilChatroomServerSettings::loadDefault();
        $connector = new ilChatroomServerConnector($settings);

        return $connector;
    }

    /**
     * Returns RefId.
     * @return int
     */
    public function getRefId()
    {
        return $this->object->getRefId();
    }
}
