<?php

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

declare(strict_types=1);

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Response\ResponseHeader;
use ILIAS\Chatroom\AccessBridge;

/**
 * @ilCtrl_Calls      ilObjChatroomGUI: ilMDEditorGUI, ilInfoScreenGUI, ilPermissionGUI, ilObjectCopyGUI
 * @ilCtrl_Calls      ilObjChatroomGUI: ilExportGUI, ilCommonActionDispatcherGUI, ilPropertyFormGUI, ilExportGUI
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

    public static function _goto($params): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $parts = array_filter(explode('_', (string) $params));
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

    protected function getObjectDefinition(): ilChatroomObjectDefinition
    {
        return ilChatroomObjectDefinition::getDefaultDefinition('Chatroom');
    }

    protected function addLocatorItems(): void
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

    public function getRefId(): int
    {
        return $this->object->getRefId();
    }

    public function getUnsafeGetCommands(): array
    {
        return [];
    }

    public function getSafePostCommands(): array
    {
        return [
            'view-toggleAutoMessageDisplayState',
        ];
    }

    public function executeCommand(): void
    {
        global $DIC;

        if ('cancel' === $this->ctrl->getCmd() && $this->getCreationMode()) {
            $this->cancelCreation();
            return;
        }

        $refId = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());
        if (!$this->getCreationMode() && ilChatroom::checkPermissionsOfUser($this->user->getId(), 'read', $refId)) {
            $DIC['ilNavigationHistory']->addItem(
                $refId,
                './goto.php?target=' . $this->type . '_' . $refId,
                $this->type
            );
        }

        $next_class = $this->ctrl->getNextClass();

        $tabFactory = null;
        if (!$this->getCreationMode()) {
            $tabFactory = new ilChatroomTabGUIFactory($this);

            $baseClass = $this->http->wrapper()->query()->retrieve(
                'baseClass',
                $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->string(),
                    $this->refinery->always('')
                ])
            );
            if (strtolower($baseClass) === strtolower(ilAdministrationGUI::class)) {
                $tabFactory->getAdminTabsForCommand($this->ctrl->getCmd());
            } else {
                $DIC['ilHelp']->setScreenIdComponent('chtr');
                $tabFactory->getTabsForCommand($this->ctrl->getCmd());
            }
        }

        switch (strtolower($next_class)) {
            case strtolower(ilInfoScreenGUI::class):
                $this->infoScreen();
                break;

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
                    $res = explode('-', (string) $this->ctrl->getCmd(''), 2);
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

        if ($this->object?->getType() !== 'chta') {
            $this->addHeaderAction();
        }

        if ($tabFactory !== null &&
            $tabFactory->getActivatedTab() !== null &&
            $this->tabs_gui->getActiveTab() !== $tabFactory->getActivatedTab()) {
            $this->tabs_gui->activateTab($tabFactory->getActivatedTab());
        }
    }

    protected function createActionDispatcherGUI(): ilCommonActionDispatcherGUI
    {
        global $DIC;

        return new ilCommonActionDispatcherGUI(
            ilCommonActionDispatcherGUI::TYPE_REPOSITORY,
            new AccessBridge($DIC->rbac()->system()),
            $this->object->getType(),
            $this->ref_id,
            $this->object->getId()
        );
    }

    protected function infoScreen(): void
    {
        $this->prepareOutput();

        $info = new ilInfoScreenGUI($this);

        $info->enablePrivateNotes();

        $refId = $this->request_wrapper->retrieve(
            'ref_id',
            $this->refinery->kindlyTo()->int()
        );
        if (ilChatroom::checkUserPermissions('read', $refId, false)) {
            $info->enableNews();
        }

        $info->addMetaDataSections(
            $this->getObject()->getId(),
            0,
            $this->getObject()->getType()
        );
        $this->ctrl->forwardCommand($info);
    }

    public function getConnector(): ilChatroomServerConnector
    {
        return new ilChatroomServerConnector(ilChatroomAdmin::getDefaultConfiguration()->getServerSettings());
    }

    public function fallback(): void
    {
        $this->prepareOutput();
        $this->tpl->setVariable('ADM_CONTENT', $this->lng->txt('invalid_operation'));
    }

    public function settings(): void
    {
        $this->prepareOutput();
    }

    protected function afterImport(ilObject $new_object): void
    {
        $room = ilChatroom::byObjectId($new_object->getId());
        $connector = $this->getConnector();
        $response = $connector->sendCreatePrivateRoom($room->getRoomId(), $new_object->getOwner(), $new_object->getTitle());

        parent::afterImport($new_object);
    }

    protected function afterSave(ilObject $new_object): void
    {
        $room = new ilChatroom();
        $room->saveSettings([
            'object_id' => $new_object->getId(),
            'autogen_usernames' => 'Autogen #',
            'display_past_msgs' => 100,
        ]);

        $connector = $this->getConnector();
        $response = $connector->sendCreatePrivateRoom(
            $room->getRoomId(),
            $new_object->getOwner(),
            $new_object->getTitle()
        );

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('object_added'), true);
        $this->ctrl->setParameter($this, 'ref_id', $new_object->getRefId());
        $this->ctrl->redirect($this, 'settings-general');
    }
}
