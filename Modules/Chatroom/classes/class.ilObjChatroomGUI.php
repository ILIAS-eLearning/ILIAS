<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObjectGUI.php';
require_once 'Modules/Chatroom/classes/class.ilObjChatroom.php';
require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
require_once 'Modules/Chatroom/classes/class.ilObjChatroomAccess.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomObjectGUI.php';

/**
 * Class ilObjChatroomGUI
 * GUI class for chatroom objects.
 * @author            Jan Posselt <jposselt at databay.de>
 * @version           $Id$
 * @ilCtrl_Calls      ilObjChatroomGUI: ilMDEditorGUI, ilInfoScreenGUI, ilPermissionGUI, ilObjectCopyGUI
 * @ilCtrl_Calls      ilObjChatroomGUI: ilExportGUI, ilCommonActionDispatcherGUI, ilPropertyFormGUI, ilExportGUI
 * @ingroup           ModulesChatroom
 */
class ilObjChatroomGUI extends ilChatroomObjectGUI
{
    /**
     * {@inheritdoc}
     */
    public function __construct($a_data = null, $a_id = null, $a_call_by_reference = true)
    {
        if (in_array($_REQUEST['cmd'], array('getOSDNotifications', 'removeOSDNotifications'))) {
            require_once 'Services/Notifications/classes/class.ilNotificationGUI.php';
            $notifications = new ilNotificationGUI();
            $notifications->{$_REQUEST['cmd'] . 'Object'}();
            exit;
        }

        if ($a_data == null) {
            if ($_GET['serverInquiry']) {
                require_once dirname(__FILE__) . '/class.ilChatroomServerHandler.php';
                new ilChatroomServerHandler();
                return;
            }
        }

        $this->type = 'chtr';
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        $this->lng->loadLanguageModule('chatroom');
        $this->lng->loadLanguageModule('chatroom_adm');
    }

    /**
     * Overwrites $_GET['ref_id'] with given $ref_id.
     * @param string $params
     */
    public static function _goto($params)
    {
        global $DIC;

        $parts = explode('_', $params);
        $ref_id = $parts[0];
        $sub = $parts[1];

        if (ilChatroom::checkUserPermissions('read', $ref_id, false)) {
            if ($sub) {
                $_REQUEST['sub'] = $_GET['sub'] = (int) $sub;
            }
            include_once 'Services/Object/classes/class.ilObjectGUI.php';
            ilObjectGUI::_gotoRepositoryNode($ref_id, 'view');
        } elseif ($DIC->rbac()->system()->checkAccess('read', ROOT_FOLDER_ID)) {
            ilUtil::sendInfo(sprintf($DIC->language()->txt('msg_no_perm_read_item'), ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id))), true);
            include_once 'Services/Object/classes/class.ilObjectGUI.php';
            ilObjectGUI::_gotoRepositoryNode(ROOT_FOLDER_ID, '');
        }

        $DIC['ilErr']->raiseError(sprintf($DIC->language()->txt('msg_no_perm_read_item'), ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id))), $DIC['ilErr']->FATAL);
    }

    /**
     * Returns object definition by calling getDefaultDefinition method
     * in ilChatroomObjectDefinition.
     * @return ilChatroomObjectDefinition
     */
    protected function getObjectDefinition()
    {
        return ilChatroomObjectDefinition::getDefaultDefinition('Chatroom');
    }

    /**
     * {@inheritdoc}
     */
    protected function initCreationForms($a_new_type)
    {
        $forms = parent::initCreationForms($a_new_type);

        $forms[self::CFORM_NEW]->clearCommandButtons();
        $forms[self::CFORM_NEW]->addCommandButton('create-save', $this->lng->txt($a_new_type . '_add'));
        $forms[self::CFORM_NEW]->addCommandButton('cancel', $this->lng->txt('cancel'));
        return $forms;
    }

    protected function addLocatorItems()
    {
        global $DIC;

        if (is_object($this->object)) {
            $DIC['ilLocator']->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, 'view'), '', $this->getRefId());
        }
    }

    /**
     * Returns RefId
     * @return integer
     */
    public function getRefId()
    {
        return $this->object->getRefId();
    }

    /**
     * Returns an empty array.
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

        if ('cancel' == $this->ctrl->getCmd() && $this->getCreationMode()) {
            parent::cancelCreation();
            return;
        }

        // add entry to navigation history
        if (!$this->getCreationMode() && $DIC->access()->checkAccess('read', '', (int) $_GET['ref_id'])) {
            $DIC['ilNavigationHistory']->addItem($_GET['ref_id'], './goto.php?target=' . $this->type . '_' . $_GET['ref_id'], $this->type);
        }

        $next_class = $this->ctrl->getNextClass();

        require_once 'Modules/Chatroom/classes/class.ilChatroomTabGUIFactory.php';
        if (!$this->getCreationMode()) {
            $tabFactory = new ilChatroomTabGUIFactory($this);

            if (strtolower($_GET['baseClass']) == 'iladministrationgui') {
                $tabFactory->getAdminTabsForCommand($this->ctrl->getCmd());
            } else {
                $DIC['ilHelp']->setScreenIdComponent("chtr");
                $tabFactory->getTabsForCommand($this->ctrl->getCmd());
            }
        }

        // #8701 - infoscreen actions
        if ($next_class == 'ilinfoscreengui' && $this->ctrl->getCmd() != 'info') {
            $this->ctrl->setCmd('info-' . $this->ctrl->getCmd());
        }
        // repository info call
        if ($this->ctrl->getCmd() == 'infoScreen') {
            $this->ctrl->setCmdClass('ilinfoscreengui');
            $this->ctrl->setCmd('info');
        }

        switch ($next_class) {
            case "ilpropertyformgui":
                include_once "Services/Form/classes/class.ilPropertyFormGUI.php";

                require_once 'Modules/Chatroom/classes/class.ilChatroomFormFactory.php';
                $factory = new ilChatroomFormFactory();
                $form = $factory->getClientSettingsForm();

                $this->ctrl->forwardCommand($form);
                break;
            case 'ilpermissiongui':
                include_once 'Services/AccessControl/classes/class.ilPermissionGUI.php';
                $this->prepareOutput();
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilexportgui':
                $this->prepareOutput();

                $GLOBALS['DIC']->tabs()->setTabActive('export');

                require_once 'Services/Export/classes/class.ilExportGUI.php';
                $exp = new ilExportGUI($this);
                $exp->addFormat('xml');
                $this->ctrl->forwardCommand($exp);
                break;

            case 'ilobjectcopygui':
                $this->prepareOutput();
                include_once 'Services/Object/classes/class.ilObjectCopyGUI.php';
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('chtr');
                $this->ctrl->forwardCommand($cp);
                break;

            case "ilcommonactiondispatchergui":
                include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                try {
                    $res = explode('-', $this->ctrl->getCmd(), 2);
                    $result = $this->dispatchCall($res[0], isset($res[1]) ? $res[1] : '');
                    if (!$result && method_exists($this, $this->ctrl->getCmd() . 'Object')) {
                        $this->prepareOutput();
                        $this->{$this->ctrl->getCmd() . 'Object'}();
                    }
                } catch (Exception $e) {
                    $error = array(
                        'success' => false,
                        'reason' => $e->getMessage()
                    );
                    echo json_encode($error);
                    exit;
                }
                break;
        }
    }

    /**
     * @return ilChatroomServerConnector
     */
    public function getConnector()
    {
        require_once 'Modules/Chatroom/classes/class.ilChatroomServerConnector.php';
        require_once 'Modules/Chatroom/classes/class.ilChatroomServerSettings.php';
        require_once 'Modules/Chatroom/classes/class.ilChatroomAdmin.php';

        $settings = ilChatroomAdmin::getDefaultConfiguration()->getServerSettings();
        $connector = new ilChatroomServerConnector($settings);

        return $connector;
    }

    /**
     * Calls $this->prepareOutput method and sets template variable.
     */
    public function fallback()
    {
        $this->prepareOutput();
        $this->tpl->setVariable('ADM_CONTENT', $this->lng->txt('invalid_operation'));
    }

    /**
     * Calls prepareOutput method.
     */
    public function settings()
    {
        $this->prepareOutput();
    }

    /**
     * Instantiates, prepares and returns object.
     * $class_name = 'ilObj' . $objDefinition->getClassName( $new_type ).
     * Fetches title from $_POST['title'], description from $_POST['desc']
     * and RefID from $_GET['ref_id'].
     * @return ilObject
     */
    public function insertObject()
    {
        global $DIC;

        $new_type = $this->type;

        // create permission is already checked in createObject.
        // This check here is done to prevent hacking attempts
        if (!$DIC->rbac()->system()->checkAccess('create', $_GET['ref_id'], $new_type)) {
            $this->ilias->raiseError(
                $this->lng->txt('no_create_permission'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        $location = $DIC['objDefinition']->getLocation($new_type);

        // create and insert object in objecttree
        $class_name = 'ilObj' . $DIC['objDefinition']->getClassName($new_type);
        include_once $location . '/class.' . $class_name . '.php';

        /**
         * @var $newObj ilObjChatroom
         */
        $newObj = new $class_name();
        $newObj->setType($new_type);
        $newObj->setTitle(ilUtil::stripSlashes($_POST['title']));
        $newObj->setDescription(ilUtil::stripSlashes($_POST['desc']));
        $newObj->create();
        $newObj->createReference();
        $newObj->putInTree($_GET['ref_id']);
        $newObj->setPermissions($_GET['ref_id']);

        $objId = $newObj->getId();

        $room = new ilChatroom();

        $room->saveSettings(
            array(
                'object_id' => $objId,
                'autogen_usernames' => 'Autogen #',
                'display_past_msgs' => 20,
                'private_rooms_enabled' => 0
            )
        );

        include_once 'Services/AccessControl/classes/class.ilRbacLog.php';
        $rbac_log_roles = $DIC->rbac()->review()->getParentRoleIds($newObj->getRefId(), false);
        $rbac_log = ilRbacLog::gatherFaPa($newObj->getRefId(), array_keys($rbac_log_roles), true);
        ilRbacLog::add(ilRbacLog::CREATE_OBJECT, $newObj->getRefId(), $rbac_log);

        $this->object = $newObj;

        return $newObj;
    }
}
