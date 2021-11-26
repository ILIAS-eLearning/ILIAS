<?php declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Response\ResponseHeader;

/**
 * Class ilObjChatroomGUI
 * GUI class for chatroom objects.
 * @author            Jan Posselt <jposselt at databay.de>
 * @version           $Id$
 * @ilCtrl_Calls      ilObjChatroomGUI: ilMDEditorGUI, ilInfoScreenGUI, ilPermissionGUI, ilObjectCopyGUI
 * @ilCtrl_Calls      ilObjChatroomGUI: ilExportGUI, ilCommonActionDispatcherGUI, ilPropertyFormGUI, ilExportGUI
 * @ingroup           ModulesChatroom
 */
class ilObjChatroomGUI extends ilChatroomObjectGUI implements ilCtrlBaseClassInterface
{
    public function __construct($a_data = null, $a_id = null, $a_call_by_reference = true)
    {
        if (isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'], array('getOSDNotifications', 'removeOSDNotifications'))) {
            require_once 'Services/Notifications/classes/class.ilNotificationGUI.php';
            $notifications = new ilNotificationGUI();
            $notifications->{$_REQUEST['cmd'] . 'Object'}();
            exit;
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
    public static function _goto($params) : void
    {
        global $DIC;

        $parts = array_filter(explode('_', $params));
        $ref_id = (int) $parts[0];
        $sub = (int) ($parts[1] ?? 0);

        if (ilChatroom::checkUserPermissions('read', $ref_id, false)) {
            // TODO PHP 8: Remove this code fragment if possible (seems not to be used)
            if ($sub) {
                $_REQUEST['sub'] = $_GET['sub'] = (int) $sub;
            }
            ilObjectGUI::_gotoRepositoryNode($ref_id, 'view');
        } elseif (ilChatroom::checkUserPermissions('visible', $ref_id, false)) {
            $DIC->ctrl()->setParameterByClass(ilInfoScreenGUI::class, 'ref_id', $ref_id);
            $DIC->ctrl()->redirectByClass(
                [
                    ilRepositoryGUI::class,
                    self::class,
                    ilInfoScreenGUI::class
                ],
                'info'
            );
        } elseif ($DIC->rbac()->system()->checkAccess('read', ROOT_FOLDER_ID)) {
            ilUtil::sendInfo(
                sprintf(
                    $DIC->language()->txt('msg_no_perm_read_item'),
                    ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id))
                ),
                true
            );
            ilObjectGUI::_gotoRepositoryNode(ROOT_FOLDER_ID, '');
        }

        $DIC['ilErr']->raiseError(
            sprintf(
                $DIC->language()->txt('msg_no_perm_read_item'),
                ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id))
            ),
            $DIC['ilErr']->FATAL
        );
    }

    protected function getObjectDefinition() : ilChatroomObjectDefinition
    {
        return ilChatroomObjectDefinition::getDefaultDefinition('Chatroom');
    }

    protected function initCreationForms($a_new_type) : array
    {
        $forms = parent::initCreationForms($a_new_type);

        $forms[self::CFORM_NEW]->clearCommandButtons();
        $forms[self::CFORM_NEW]->addCommandButton('create-save', $this->lng->txt($a_new_type . '_add'));
        $forms[self::CFORM_NEW]->addCommandButton('cancel', $this->lng->txt('cancel'));

        return $forms;
    }

    protected function addLocatorItems() : void
    {
        if (is_object($this->object)) {
            $this->locator->addItem(
                $this->object->getTitle(),
                $this->ctrl->getLinkTarget($this, 'view'),
                '',
                $this->getRefId()
            );
        }
    }

    public function getRefId() : int
    {
        return $this->object->getRefId();
    }

    public function executeCommand()
    {
        global $DIC;

        if ('cancel' === $this->ctrl->getCmd() && $this->getCreationMode()) {
            $this->cancelCreation();
            return;
        }

        $refId = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());
        if (!$this->getCreationMode() && $this->access->checkAccess('read', '', $refId)) {
            $DIC['ilNavigationHistory']->addItem(
                $refId,
                './goto.php?target=' . $this->type . '_' . $refId,
                $this->type
            );
        }

        $next_class = $this->ctrl->getNextClass();

        if (!$this->getCreationMode()) {
            $tabFactory = new ilChatroomTabGUIFactory($this);

            $baseClass = '';
            if ($this->http->wrapper()->query()->has('baseClass')) {
                $baseClass = $this->http->wrapper()->query()->retrieve(
                    'baseClass',
                    $this->refinery->kindlyTo()->string()
                );
            }
            if (strtolower($baseClass) === strtolower(ilAdministrationGUI::class)) {
                $tabFactory->getAdminTabsForCommand($this->ctrl->getCmd());
            } else {
                $DIC['ilHelp']->setScreenIdComponent('chtr');
                $tabFactory->getTabsForCommand($this->ctrl->getCmd());
            }
        }

        // #8701 - infoscreen actions
        if ($this->ctrl->getCmd() !== 'info' && strtolower($next_class) === strtolower(ilInfoScreenGUI::class)) {
            $this->ctrl->setCmd('info-' . $this->ctrl->getCmd());
        }

        // repository info call
        if ($this->ctrl->getCmd() === 'infoScreen') {
            $this->ctrl->setCmdClass(ilInfoScreenGUI::class);
            $this->ctrl->setCmd('info');
        }

        switch (strtolower($next_class)) {
            case strtolower(ilPropertyFormGUI::class):
                $factory = new ilChatroomFormFactory();
                $form = $factory->getClientSettingsForm();
                $this->ctrl->forwardCommand($form);
                break;

            case strtolower(ilPermissionGUI::class):
                $this->prepareOutput();
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case strtolower(ilExportGUI::class):
                $this->prepareOutput();

                $GLOBALS['DIC']->tabs()->setTabActive('export');

                $exp = new ilExportGUI($this);
                $exp->addFormat('xml');
                $this->ctrl->forwardCommand($exp);
                break;

            case strtolower(ilObjectCopyGUI::class):
                $this->prepareOutput();
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('chtr');
                $this->ctrl->forwardCommand($cp);
                break;

            case strtolower(ilCommonActionDispatcherGUI::class):
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                try {
                    $res = explode('-', $this->ctrl->getCmd('', [
                        'view-toggleAutoMessageDisplayState'
                    ]), 2);
                    $result = $this->dispatchCall($res[0], $res[1] ?? '');
                    if (!$result && method_exists($this, $this->ctrl->getCmd() . 'Object')) {
                        $this->prepareOutput();
                        $this->{$this->ctrl->getCmd() . 'Object'}();
                    }
                } catch (Exception $e) {
                    if ($this->ctrl->isAsynch()) {
                        $responseStream = Streams::ofString(json_encode([
                            'success' => false,
                            'reason' => $e->getMessage()
                        ], JSON_THROW_ON_ERROR));
                        $this->http->saveResponse(
                            $this->http->response()
                                ->withBody($responseStream)
                                ->withHeader(ResponseHeader::CONTENT_TYPE, 'application/json')
                        );
                        $this->http->sendResponse();
                        $this->http->close();
                    } else {
                        throw $e;
                    }
                }
                break;
        }
    }

    public function getConnector() : ilChatroomServerConnector
    {
        return new ilChatroomServerConnector(ilChatroomAdmin::getDefaultConfiguration()->getServerSettings());
    }

    /**
     * Calls $this->prepareOutput method and sets template variable.
     */
    public function fallback() : void
    {
        $this->prepareOutput();
        $this->tpl->setVariable('ADM_CONTENT', $this->lng->txt('invalid_operation'));
    }

    /**
     * Calls prepareOutput method.
     */
    public function settings() : void
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
    public function insertObject() : ilObjChatroom
    {
        $new_type = $this->type;
        $refId = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());
        $title = '';
        if ($this->http->wrapper()->post()->has('title')) {
            $title = ilUtil::stripSlashes(
                $this->http->wrapper()->post()->retrieve(
                    'title',
                    $this->refinery->kindlyTo()->string()
                )
            );
        }
        $desc = '';
        if ($this->http->wrapper()->post()->has('desc')) {
            $desc = ilUtil::stripSlashes(
                $this->http->wrapper()->post()->retrieve(
                    'desc',
                    $this->refinery->kindlyTo()->string()
                )
            );
        }

        // create permission is already checked in createObject.
        // This check here is done to prevent hacking attempts
        if (!$this->rbacsystem->checkAccess('create', $refId, $new_type)) {
            $this->ilias->raiseError(
                $this->lng->txt('no_create_permission'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        // create and insert object in objecttree
        $class_name = 'ilObj' . $this->objDefinition->getClassName($new_type);

        $newObj = new $class_name();
        $newObj->setType($new_type);
        $newObj->setTitle($title);
        $newObj->setDescription($desc);
        $newObj->create();
        $newObj->createReference();
        $newObj->putInTree($refId);
        $newObj->setPermissions($refId);

        $objId = $newObj->getId();

        $room = new ilChatroom();
        $room->saveSettings([
            'object_id' => $objId,
            'autogen_usernames' => 'Autogen #',
            'display_past_msgs' => 20,
            'private_rooms_enabled' => 0
        ]);

        $rbac_log_roles = $this->rbacreview->getParentRoleIds($newObj->getRefId(), false);
        $rbac_log = ilRbacLog::gatherFaPa($newObj->getRefId(), array_keys($rbac_log_roles), true);
        ilRbacLog::add(ilRbacLog::CREATE_OBJECT, $newObj->getRefId(), $rbac_log);

        $this->object = $newObj;

        return $newObj;
    }
}
