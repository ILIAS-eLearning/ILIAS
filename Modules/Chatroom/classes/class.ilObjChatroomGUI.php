<?php declare(strict_types=1);

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
class ilObjChatroomGUI extends ilChatroomObjectGUI implements ilCtrlSecurityInterface
{
    public function __construct($data = null, ?int $id = 0, bool $call_by_reference = true, bool $prepare_output = true)
    {
        $this->type = 'chtr';
        parent::__construct($data, $id, $call_by_reference, false);
        $this->lng->loadLanguageModule('chatroom');
        $this->lng->loadLanguageModule('chatroom_adm');
    }

    /**
     * @ineritdoc
     */
    public static function _goto($params) : void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $parts = array_filter(explode('_', $params));
        $ref_id = (int) $parts[0];
        $sub = (int) ($parts[1] ?? 0);

        if (ilChatroom::checkUserPermissions('read', $ref_id, false)) {
            if ($sub) {
                $DIC->ctrl()->setParameterByClass(self::class, 'sub', $sub);
            }

            $DIC->ctrl()->setParameterByClass(self::class, 'ref_id', $ref_id);
            $DIC->ctrl()->redirectByClass(
                [
                    ilRepositoryGUI::class,
                    self::class,
                ],
                'view'
            );
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
            $main_tpl->setOnScreenMessage('info', sprintf(
                $DIC->language()->txt('msg_no_perm_read_item'),
                ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id))
            ), true);
            ilObjectGUI::_gotoRepositoryNode(ROOT_FOLDER_ID);
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

    protected function initCreationForms(string $new_type) : array
    {
        $forms = parent::initCreationForms($new_type);

        $forms[self::CFORM_NEW]->clearCommandButtons();
        $forms[self::CFORM_NEW]->addCommandButton('create-save', $this->lng->txt($new_type . '_add'));
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

    /**
     * @inheritDoc
     */
    public function getUnsafeGetCommands() : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getSafePostCommands() : array
    {
        return [
            'view-toggleAutoMessageDisplayState',
        ];
    }

    public function executeCommand() : void
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
                $this->prepareOutput();
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                try {
                    $res = explode('-', $this->ctrl->getCmd(''), 2);
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
        if (!$this->rbac_system->checkAccess('create', $refId, $new_type)) {
            $this->ilias->raiseError(
                $this->lng->txt('no_create_permission'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        // create and insert object in objecttree
        $class_name = 'ilObj' . $this->obj_definition->getClassName($new_type);

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

        $rbac_log_roles = $this->rbac_review->getParentRoleIds($newObj->getRefId());
        $rbac_log = ilRbacLog::gatherFaPa($newObj->getRefId(), array_keys($rbac_log_roles), true);
        ilRbacLog::add(ilRbacLog::CREATE_OBJECT, $newObj->getRefId(), $rbac_log);

        $this->object = $newObj;

        return $newObj;
    }
}
