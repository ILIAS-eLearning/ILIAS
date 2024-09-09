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

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Mail\Folder\MailFilterUI;
use ILIAS\Mail\Folder\MailFolderSearch;
use ILIAS\Mail\Folder\MailFolderTableUI;
use ILIAS\Mail\Message\MailBoxQuery;
use ILIAS\Mail\Folder\MailFolderData;

/**
 * @ilCtrl_Calls ilMailFolderGUI:
 */
class ilMailFolderGUI
{
    // used as single element namespace for UrlBuilder
    // added with '_' before parameter names in queries from the table
    private const URL_BUILDER_PREFIX = 'ilMailFolderGUI';

    // controller parameters
    private const PARAM_ACTION = 'action';
    private const PARAM_FOLDER_ID = 'mobj_id';
    private const PARAM_MAIL_ID = 'mail_id';
    private const PARAM_USER_ID = 'user_id';
    private const PARAM_TARGET_FOLDER = 'target_folder';
    private const PARAM_INTERRUPTIVE_ITEMS = 'interruptive_items';

    // controller commands
    private const CMD_ADD_SUB_FOLDER = 'addSubFolder';
    private const CMD_DELETE_MAILS = 'deleteMails';
    private const CMD_DELETE_SUB_FOLDER = 'deleteSubFolder';
    private const CMD_DELIVER_FILE = 'deliverFile';
    private const CMD_EMPTY_TRASH = 'emptyTrash';
    private const CMD_MOVE_SINGLE_MAIL = 'moveSingleMail';
    private const CMD_PRINT_MAIL = 'printMail';
    private const CMD_RENAME_SUB_FOLDER = 'renameSubFolder';
    private const CMD_SHOW_MAIL = 'showMail';
    private const CMD_SHOW_FOLDER = 'showFolder';
    private const CMD_SHOW_USER = 'showUser';
    private const CMD_TABLE_ACTION = 'executeTableAction';

    private readonly ilGlobalTemplateInterface $tpl;
    private readonly ilCtrlInterface $ctrl;
    private readonly ilLanguage $lng;
    private readonly ilToolbarGUI $toolbar;
    private readonly ilTabsGUI $tabs;
    private readonly ilObjUser $user;
    private readonly GlobalHttpState $http;
    private readonly Refinery $refinery;
    private readonly ilErrorHandling $error;
    private readonly Factory $ui_factory;
    private readonly Renderer $ui_renderer;
    private readonly ilUIService $ui_service;
    private readonly DataFactory $data_factory;

    private ilMail $umail;
    private ilMailbox $mbox;
    private MailFolderData $folder;

    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
        $this->tabs = $DIC->tabs();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->error = $DIC['ilErr'];
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->ui_service = $DIC->uiService();
        $this->data_factory = new ILIAS\Data\Factory();
    }

    /**
     * Init class variables that can be determined in an actual request
     */
    protected function initRequest(): void
    {
        $this->umail = new ilMail($this->user->getId());
        $this->mbox = new ilMailbox($this->user->getId());

        if ($this->http->wrapper()->post()->has(self::PARAM_FOLDER_ID)) {
            $folder_id = $this->http->wrapper()->post()->retrieve(self::PARAM_FOLDER_ID, $this->refinery->kindlyTo()->int());
        } elseif ($this->http->wrapper()->query()->has(self::PARAM_FOLDER_ID)) {
            $folder_id = $this->http->wrapper()->query()->retrieve(self::PARAM_FOLDER_ID, $this->refinery->kindlyTo()->int());
        } else {
            $folder_id = $this->refinery->byTrying([
                $this->refinery->kindlyTo()->int(),
                $this->refinery->always(0),
            ])->transform(ilSession::get(self::PARAM_FOLDER_ID));
        }

        if (0 === $folder_id || !$this->mbox->isOwnedFolder($folder_id)) {
            $folder_id = $this->mbox->getInboxFolder();
        }

        $folder = $this->mbox->getFolderData($folder_id);
        if ($folder === null) {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('mail_operation_on_invalid_folder')
            );
            $this->tpl->printToStdout();
        }
        $this->folder = $folder;
    }

    public function executeCommand(): void
    {
        $this->initRequest();

        $nextClass = $this->ctrl->getNextClass($this) ?? '';
        switch (strtolower($nextClass)) {
            case strtolower(ilContactGUI::class):
                $this->ctrl->forwardCommand(new ilContactGUI());
                break;

            default:
                $cmd = $this->ctrl->getCmd() ?? '';
                switch ($cmd) {
                    case self::CMD_ADD_SUB_FOLDER:
                    case self::CMD_DELETE_MAILS:
                    case self::CMD_DELETE_SUB_FOLDER:
                    case self::CMD_DELIVER_FILE:
                    case self::CMD_EMPTY_TRASH:
                    case self::CMD_MOVE_SINGLE_MAIL:
                    case self::CMD_PRINT_MAIL:
                    case self::CMD_RENAME_SUB_FOLDER:
                    case self::CMD_SHOW_MAIL:
                    case self::CMD_SHOW_FOLDER:
                    case self::CMD_SHOW_USER:
                    case self::CMD_TABLE_ACTION:
                        $this->{$cmd}();
                        break;

                    default:
                        $this->showFolder();
                }
        }
    }

    protected function executeTableAction(): void
    {
        $action = $this->http->wrapper()->query()->retrieve(
            self::URL_BUILDER_PREFIX . URLBuilder::SEPARATOR . self::PARAM_ACTION,
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always('')
            ])
        );

        // Magic value of data table in ui framework, no public constant found
        $for_all_entries = 'ALL_OBJECTS' === implode(
            '',
            $this->http->wrapper()->query()->retrieve(
                self::URL_BUILDER_PREFIX . URLBuilder::SEPARATOR . self::PARAM_MAIL_ID,
                $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()),
                $this->refinery->always([])
            ])
            )
        );

        if ($for_all_entries) {
            // we must apply the filter because the shown table is filtered, too
            $mail_ids = $this->getFilteredSearch()->getMaiIds();
        } else {
            $mail_ids = $this->getMailIdsFromRequest();
        }

        if (empty($mail_ids)) {
            // no redirect possible from async call
            if ($action === MailFolderTableUI::ACTION_DELETE) {
                $modal = $this->ui_factory->modal()->lightbox(
                    $this->ui_factory->modal()->lightboxTextPage(
                        $this->lng->txt('mail_select_one'),
                        $this->lng->txt('delete'),
                    )
                );
                echo $this->ui_renderer->render($modal);
                exit;
            } else {
                $this->tpl->setOnScreenMessage(
                    ilGlobalTemplateInterface::MESSAGE_TYPE_INFO,
                    $this->lng->txt('mail_select_one'),
                    true
                );
                $this->redirectToFolder();
            }
        }

        switch ($action) {
            case MailFolderTableUI::ACTION_SHOW:
                $this->showMail();
                return;

            case MailFolderTableUI::ACTION_EDIT:
                $this->ctrl->setParameterByClass(ilMailFormGUI::class, self::PARAM_FOLDER_ID, (string) $this->folder->getFolderId());
                $this->ctrl->setParameterByClass(ilMailFormGUI::class, self::PARAM_MAIL_ID, (string) $mail_ids[0]);
                $this->ctrl->setParameterByClass(ilMailFormGUI::class, 'type', ilMailFormGUI::MAIL_FORM_TYPE_DRAFT);
                $this->ctrl->redirectByClass(ilMailFormGUI::class);
                break;

            case MailFolderTableUI::ACTION_REPLY:
                $this->ctrl->setParameterByClass(ilMailFormGUI::class, self::PARAM_FOLDER_ID, (string) $this->folder->getFolderId());
                $this->ctrl->setParameterByClass(ilMailFormGUI::class, self::PARAM_MAIL_ID, (string) $mail_ids[0]);
                $this->ctrl->setParameterByClass(ilMailFormGUI::class, 'type', ilMailFormGUI::MAIL_FORM_TYPE_REPLY);
                $this->ctrl->redirectByClass(ilMailFormGUI::class);
                break;

            case MailFolderTableUI::ACTION_FORWARD:
                $this->ctrl->setParameterByClass(ilMailFormGUI::class, self::PARAM_FOLDER_ID, (string) $this->folder->getFolderId());
                $this->ctrl->setParameterByClass(ilMailFormGUI::class, self::PARAM_MAIL_ID, (string) $mail_ids[0]);
                $this->ctrl->setParameterByClass(ilMailFormGUI::class, 'type', ilMailFormGUI::MAIL_FORM_TYPE_FORWARD);
                $this->ctrl->redirectByClass(ilMailFormGUI::class);
                break;

            case MailFolderTableUI::ACTION_DOWNLOAD_ATTACHMENT:
                $this->deliverAttachments();
                return;

            case MailFolderTableUI::ACTION_PRINT:
                $this->printMail();
                return;

            case MailFolderTableUI::ACTION_PROFILE:
                $mail_data = $this->umail->getMail($mail_ids[0] ?? 0);
                if (!empty($user = ilMailUserCache::getUserObjectById($mail_data['sender_id'] ?? 0))) {
                    if ($user->hasPublicProfile()) {
                        $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, (string) $this->folder->getFolderId());
                        $this->ctrl->setParameter($this, self::PARAM_USER_ID, (string) $user->getId());
                        $this->ctrl->redirect($this, self::CMD_SHOW_USER);
                    }
                } else {
                    $this->tpl->setOnScreenMessage(
                        ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                        $this->lng->txt('permission_denied')
                    );
                    break;
                }

                // no break
            case MailFolderTableUI::ACTION_MARK_READ:
                $this->umail->markRead($mail_ids);
                $this->tpl->setOnScreenMessage(
                    ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
                    $this->lng->txt('saved_successfully'),
                    true
                );
                break;

            case MailFolderTableUI::ACTION_MARK_UNREAD:
                $this->umail->markUnread($mail_ids);
                $this->tpl->setOnScreenMessage(
                    ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
                    $this->lng->txt('saved_successfully'),
                    true
                );
                break;

            case MailFolderTableUI::ACTION_MOVE_TO:
                $folder_id = $this->http->wrapper()->query()->retrieve(
                    self::URL_BUILDER_PREFIX . URLBuilder::SEPARATOR . self::PARAM_TARGET_FOLDER,
                    $this->refinery->kindlyTo()->int()
                );
                if (empty($folder_id)) {
                    $this->tpl->setOnScreenMessage(
                        ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                        $this->lng->txt('mail_move_error')
                    );
                } elseif ($this->umail->moveMailsToFolder($mail_ids, $folder_id)) {
                    $this->tpl->setOnScreenMessage(
                        ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
                        $this->lng->txt('mail_moved'),
                        true
                    );
                } else {
                    $this->tpl->setOnScreenMessage(
                        ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                        $this->lng->txt('mail_move_error')
                    );
                }
                break;

            case MailFolderTableUI::ACTION_DELETE:     // async call
                $this->confirmDeleteMails($mail_ids);
                break;

            default:
                $this->tpl->setOnScreenMessage(
                    ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                    $this->lng->txt('permission_denied')
                );
                break;
        }

        $this->redirectToFolder();
    }

    protected function emptyTrash(): void
    {
        $this->umail->deleteMailsOfFolder($this->mbox->getTrashFolder());
        $this->tpl->setOnScreenMessage(
            ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt('mail_deleted'),
            true
        );
        $this->redirectToFolder();
    }

    /**
     * @throws ilCtrlException
     */
    protected function showUser(): void
    {
        $userId = 0;
        if ($this->http->wrapper()->query()->has(self::PARAM_USER_ID)) {
            $userId = $this->http->wrapper()->query()->retrieve(self::PARAM_USER_ID, $this->refinery->kindlyTo()->int());
        }
        $this->tpl->setVariable('TBL_TITLE', implode(' ', [
            $this->lng->txt('profile_of'),
            ilObjUser::_lookupLogin($userId),
        ]));
        $this->tpl->setVariable('TBL_TITLE_IMG', ilUtil::getImagePath('standard/icon_usr.svg'));
        $this->tpl->setVariable('TBL_TITLE_IMG_ALT', $this->lng->txt('public_profile'));

        $profile_gui = new ilPublicUserProfileGUI($userId);

        $mailId = 0;
        if ($this->http->wrapper()->query()->has(self::PARAM_MAIL_ID)) {
            $mailId = $this->http->wrapper()->query()->retrieve(self::PARAM_MAIL_ID, $this->refinery->kindlyTo()->int());
        }

        if (!empty($mailId)) {
            $this->ctrl->setParameter($this, self::PARAM_MAIL_ID, $mailId);
            $this->tabs->clearTargets();
            $this->tabs->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, self::CMD_SHOW_MAIL));
        } else {
            $this->tabs->clearTargets();
            $this->tabs->setBackTarget($this->lng->txt('back_to_folder'), $this->ctrl->getLinkTarget($this, self::CMD_SHOW_FOLDER));
        }

        $this->ctrl->clearParameters($this);

        $this->tpl->setTitle($this->lng->txt('mail'));
        $this->tpl->setContent($this->ctrl->getHTML($profile_gui));
        $this->tpl->printToStdout();
    }

    protected function showFolder(): void
    {
        $components = [];
        $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $this->folder->getFolderId());

        if ($this->folder->isUserLocalFolder()) {
            $this->toolbar->addComponent($this->ui_factory->button()->standard(
                $this->lng->txt('mail_add_subfolder'),
                $this->ctrl->getLinkTarget($this, self::CMD_ADD_SUB_FOLDER)
            ));
        }

        if ($this->folder->isUserFolder()) {
            $this->toolbar->addComponent($this->ui_factory->button()->standard(
                $this->lng->txt('rename'),
                $this->ctrl->getLinkTarget($this, self::CMD_RENAME_SUB_FOLDER)
            ));

            $components[] = $modal = $this->ui_factory->modal()->interruptive(
                $this->lng->txt('delete'),
                $this->lng->txt('mail_sure_delete_folder'),
                $this->ctrl->getLinkTarget($this, self::CMD_DELETE_SUB_FOLDER)
            );
            $this->toolbar->addComponent($this->ui_factory->button()->standard(
                $this->lng->txt('delete'),
                '#'
            )
                ->withOnClick($modal->getShowSignal()));
        }

        if ($this->folder->isTrash()) {
            $components[] = $modal = $this->ui_factory->modal()->interruptive(
                $this->lng->txt('mail_empty_trash'),
                $this->lng->txt('mail_empty_trash_confirmation'),
                $this->ctrl->getLinkTarget($this, self::CMD_EMPTY_TRASH)
            );
            $this->toolbar->addComponent($this->ui_factory->button()->standard(
                $this->lng->txt('mail_empty_trash'),
                '#'
            )
                ->withOnClick($modal->getShowSignal()));
        }

        [   $url_builder,
            $action_token,
            $row_id_token,
            $target_token,
        ] = (new URLBuilder($this->data_factory->uri(
            ilUtil::_getHttpPath() . '/' .
                    $this->ctrl->getLinkTarget($this, self::CMD_TABLE_ACTION)
        ))
            )->acquireParameters(
                [self::URL_BUILDER_PREFIX],
                self::PARAM_ACTION,
                self::PARAM_MAIL_ID,
                self::PARAM_TARGET_FOLDER
            );

        $table = new MailFolderTableUI(
            $url_builder,
            $action_token,
            $row_id_token,
            $target_token,
            $this->mbox->getSubFolders(),
            $this->folder,
            $this->getFilteredSearch(),
            $this->umail,
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->http->request(),
            $this->data_factory,
            $this->refinery,
            $this->user->getDateFormat(),
            new DateTimeZone($this->user->getTimeZone())
        );

        $components[] = $this->getFilterUI()->getComponent();
        $components[] = $table->getComponent();

        $this->tpl->setTitle($this->folder->getTitle());
        $this->tpl->setContent($this->ui_renderer->render($components));
        $this->tpl->printToStdout();
    }

    protected function redirectToFolder()
    {
        $this->ctrl->clearParameters($this);
        $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $this->folder->getFolderId());
        $this->ctrl->redirect($this, self::CMD_SHOW_FOLDER);
    }

    protected function deleteSubFolder(): void
    {
        $parent_folder_id = $this->mbox->getParentFolderId($this->folder->getFolderId());
        if ($parent_folder_id > 0 && $this->mbox->deleteFolder($this->folder->getFolderId())) {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
                $this->lng->txt('mail_folder_deleted'),
                true
            );
            $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $parent_folder_id);
            $this->ctrl->redirect($this, self::CMD_SHOW_FOLDER);
        } else {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('mail_error_delete'),
                true
            );
            $this->redirectToFolder();
        }
    }

    protected function addSubFolder(ilPropertyFormGUI $form = null): void
    {
        $form = $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, self::CMD_ADD_SUB_FOLDER),
            [
                'folder' => $this->ui_factory->input()->field()->section([
                    'title' => $this->ui_factory->input()->field()->text($this->lng->txt('title'))->withRequired(true)
                ], $this->lng->txt('mail_add_folder'))
            ]
        );

        $request = $this->http->request();
        if ($request->getMethod() === "POST") {
            $form = $form->withRequest($request);
            $data = $form->getData();
            if (!empty($data['folder']['title'])) {
                $new_folder_id = $this->mbox->addFolder($this->folder->getFolderId(), (string) $data['folder']['title']);
                if ($new_folder_id > 0) {
                    $this->tpl->setOnScreenMessage(ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS, $this->lng->txt('mail_folder_created'), true);
                    $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $new_folder_id);
                    $this->ctrl->redirect($this, self::CMD_SHOW_FOLDER);
                } else {
                    $this->tpl->setOnScreenMessage(ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE, $this->lng->txt('mail_folder_exists'));
                }
            }
        }
        $this->tpl->setContent($this->ui_renderer->render($form));
        $this->tpl->printToStdout();
    }

    protected function renameSubFolder(ilPropertyFormGUI $form = null): void
    {
        $form = $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, self::CMD_RENAME_SUB_FOLDER),
            [
                'folder' => $this->ui_factory->input()->field()->section([
                    'title' => $this->ui_factory->input()->field()->text($this->lng->txt('title'))->withRequired(true)
                ], $this->lng->txt('mail_rename_folder'))
            ]
        );

        $request = $this->http->request();
        if ($request->getMethod() === "POST") {
            $form = $form->withRequest($request);
            $data = $form->getData();
            if (!empty($data['folder']['title'])) {
                if ($this->mbox->renameFolder($this->folder->getFolderId(), (string) $data['folder']['title'])) {
                    $this->tpl->setOnScreenMessage(ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS, $this->lng->txt('mail_folder_name_changed'), true);
                    $this->redirectToFolder();
                } else {
                    $this->tpl->setOnScreenMessage(ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE, $this->lng->txt('mail_folder_exists'));
                }
            }
        }
        $this->tpl->setContent($this->ui_renderer->render($form));
        $this->tpl->printToStdout();
    }

    protected function getFilterUI(): MailFilterUI
    {
        return new MailFilterUI(
            $this->ctrl->getFormAction($this, self::CMD_SHOW_FOLDER),
            ilSearchSettings::getInstance()->enabledLucene(),
            $this->folder,
            $this->ui_factory,
            $this->ui_service->filter(),
            $this->lng,
            new DateTimeZone($this->user->getTimeZone()),
        );
    }

    /**
     * Searcher for mails in the folder, initialized with the current filter values
     * needed for table display and actions for the whole table
     */
    protected function getFilteredSearch(): MailFolderSearch
    {
        return new MailFolderSearch(
            $this->folder,
            $this->getFilterUI()->getData(),
            ilSearchSettings::getInstance()->enabledLucene(),
        );
    }

    /**
     * @return int[]
     */
    protected function getMailIdsFromRequest(): array
    {
        $mailIds = [];
        // table actions have a prefix, controller commands and modal items have none
        foreach ([self::URL_BUILDER_PREFIX . URLBuilder::SEPARATOR . self::PARAM_MAIL_ID,
                  self::PARAM_MAIL_ID,
                  self::PARAM_INTERRUPTIVE_ITEMS
                 ] as $param
        ) {
            foreach ([$this->http->wrapper()->post(),
                      $this->http->wrapper()->query()] as $wrapper
            ) {
                if ($wrapper->has($param)) {
                    return $wrapper->retrieve(
                        $param,
                        $this->refinery->byTrying([
                            $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()),
                            $this->refinery->always([])
                        ])
                    );
                }
            }
        }
        return [];
    }

    /**
     * Move a single mail to a folder
     * Called from showMail page
     */
    protected function moveSingleMail(): void
    {
        $mailIds = $this->getMailIdsFromRequest();
        if (1 !== count($mailIds)) {
            $this->showMail();
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_one'));
            return;
        }

        $newFolderId = 0;
        if ($this->http->wrapper()->query()->has('folder_id')) {
            $newFolderId = $this->http->wrapper()->query()->retrieve(
                'folder_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $redirectFolderId = $newFolderId;

        foreach ($mailIds as $mailId) {
            $mailData = $this->umail->getMail($mailId);
            if (isset($mailData['folder_id']) &&
                is_numeric($mailData['folder_id']) &&
                (int) $mailData['folder_id'] > 0
            ) {
                $redirectFolderId = (int) $mailData['folder_id'];
                break;
            }
        }

        if ($this->umail->moveMailsToFolder($mailIds, $newFolderId)) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_moved'), true);
            $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $redirectFolderId);
            $this->ctrl->redirect($this, self::CMD_SHOW_FOLDER);
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_move_error'));
            $this->showMail();
        }
    }

    protected function deleteMails(?array $mail_ids = null): void
    {
        if (!$this->folder->isTrash()) {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('mail_operation_on_invalid_folder'),
                true
            );
            $this->redirectToFolder();
        }

        $this->umail->deleteMails($this->getMailIdsFromRequest());
        $this->tpl->setOnScreenMessage(ilGlobalTemplateInterface::MESSAGE_TYPE_INFO, $this->lng->txt('mail_deleted'), true);
        $this->redirectToFolder();
    }

    /**
     * Confirm the deletion of selected mails in async modal
     * @param int[] $mail_ids
     */
    protected function confirmDeleteMails(array $mail_ids): never
    {
        $user_timezone = new DateTimeZone($this->user->getTimeZone());
        $records = $this->getFilteredSearch()->forMailIds($mail_ids)->getPagedRecords(10, 0, null, null);
        $items = [];
        foreach($records as $record) {
            $prefix = '';
            if (!empty($record->getSendTime())) {
                $time = $record->getSendTime()->setTimezone($user_timezone);
                $prefix = $time->format($this->user->getDateFormat()->toString()) . ' ';
            }
            $items[] = $this->ui_factory->modal()->interruptiveItem()->standard(
                (string) $record->getMailId(),
                $prefix . $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform($record->getSubject())
            );
        }

        $modal = $this->ui_factory->modal()->interruptive(
            $this->lng->txt('delete'),
            $this->lng->txt('mail_sure_delete'),
            $this->ctrl->getFormAction($this, self::CMD_DELETE_MAILS)
        )->withAffectedItems($items);

        echo $this->ui_renderer->renderAsync($modal);
        exit;
    }

    protected function showMail(): void
    {
        $mailId = $this->getMailIdsFromRequest()[0] ?? 0;

        if ($mailId <= 0) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $mailData = $this->umail->getMail($mailId);
        if ($mailData === null) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->umail->markRead([$mailId]);

        $this->tpl->setTitle($this->lng->txt('mail_mails_of'));

        $this->tabs->clearTargets();
        $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $mailData['folder_id']);
        $this->tabs->setBackTarget(
            $this->lng->txt('back_to_folder'),
            $this->ctrl->getFormAction($this, self::CMD_SHOW_FOLDER)
        );
        $this->ctrl->clearParameters($this);

        $this->ctrl->setParameter($this, self::PARAM_MAIL_ID, $mailId);
        $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $mailData['folder_id']);
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, self::CMD_SHOW_MAIL));
        $this->ctrl->clearParameters($this);

        $form = new ilPropertyFormGUI();
        $form->setId('MailContent');
        $form->setPreventDoubleSubmission(false);
        $form->setTableWidth('100%');
        $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $mailData['folder_id']);
        $this->ctrl->setParameter($this, self::PARAM_MAIL_ID, $mailId);
        $form->setFormAction($this->ctrl->getFormAction($this, self::CMD_SHOW_MAIL));
        $this->ctrl->clearParameters($this);
        $form->setTitle($this->lng->txt('mail_mails_of'));

        /** @var ilObjUser|null $sender */
        $sender = ilObjectFactory::getInstanceByObjId($mailData['sender_id'], false);
        $replyBtn = null;
        if ($sender instanceof ilObjUser && $sender->getId() !== 0 && !$sender->isAnonymous()) {
            $this->ctrl->setParameterByClass(
                ilMailFormGUI::class,
                self::PARAM_FOLDER_ID,
                $mailData['folder_id']
            );
            $this->ctrl->setParameterByClass(ilMailFormGUI::class, self::PARAM_MAIL_ID, $mailId);
            $this->ctrl->setParameterByClass(ilMailFormGUI::class, 'type', ilMailFormGUI::MAIL_FORM_TYPE_REPLY);
            $replyBtn = $this->ui_factory->button()->primary(
                $this->lng->txt('reply'),
                $this->ctrl->getLinkTargetByClass(ilMailFormGUI::class)
            );
            $this->toolbar->addStickyItem($replyBtn);
            $this->ctrl->clearParametersByClass(ilMailFormGUI::class);
        }

        $this->ctrl->setParameterByClass(ilMailFormGUI::class, self::PARAM_FOLDER_ID, $mailData['folder_id']);
        $this->ctrl->setParameterByClass(ilMailFormGUI::class, self::PARAM_MAIL_ID, $mailId);
        $this->ctrl->setParameterByClass(ilMailFormGUI::class, 'type', ilMailFormGUI::MAIL_FORM_TYPE_FORWARD);
        if ($replyBtn === null) {
            $fwdBtn = $this->ui_factory->button()->primary(
                $this->lng->txt('forward'),
                $this->ctrl->getLinkTargetByClass(ilMailFormGUI::class)
            );
            $this->toolbar->addStickyItem($fwdBtn);
        } else {
            $fwdBtn = $this->ui_factory->button()->standard(
                $this->lng->txt('forward'),
                $this->ctrl->getLinkTargetByClass(ilMailFormGUI::class)
            );
            $this->toolbar->addComponent($fwdBtn);
        }
        $this->ctrl->clearParametersByClass(ilMailFormGUI::class);

        if ($sender && $sender->getId() && !$sender->isAnonymous()) {
            $linked_fullname = $sender->getPublicName();
            $picture = ilUtil::img(
                $sender->getPersonalPicturePath('xsmall'),
                $sender->getPublicName(),
                '',
                '',
                0,
                '',
                'ilMailAvatar'
            );

            if (in_array(ilObjUser::_lookupPref($sender->getId(), 'public_profile'), ['y', 'g'])) {
                $this->ctrl->setParameter($this, self::PARAM_MAIL_ID, $mailId);
                $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $mailData['folder_id']);
                $this->ctrl->setParameter($this, self::PARAM_USER_ID, $sender->getId());
                $linked_fullname = '<br /><a class="mailusername" href="' . $this->ctrl->getLinkTarget(
                    $this,
                    self::CMD_SHOW_USER
                ) . '" title="' . $linked_fullname . '">' . $linked_fullname . '</a>';
                $this->ctrl->clearParameters($this);
            }

            $from = new ilCustomInputGUI($this->lng->txt('from') . ':');
            $from->setHtml($picture . ' ' . $linked_fullname);
        } elseif (!$sender || !$sender->getId()) {
            $from = new ilCustomInputGUI($this->lng->txt('from') . ':');
            $from->setHtml(trim(($mailData['import_name'] ?? '') . ' (' . $this->lng->txt('user_deleted') . ')'));
        } else {
            $from = new ilCustomInputGUI($this->lng->txt('from') . ':');
            $from->setHtml(
                ilUtil::img(
                    ilUtil::getImagePath('logo/HeaderIconAvatar.svg'),
                    ilMail::_getIliasMailerName(),
                    '',
                    '',
                    0,
                    '',
                    'ilMailAvatar'
                ) .
                '<br />' . ilMail::_getIliasMailerName()
            );
        }
        $form->addItem($from);

        $to = new ilCustomInputGUI($this->lng->txt('mail_to') . ':');
        $to->setHtml(ilUtil::htmlencodePlainString(
            $this->umail->formatNamesForOutput($mailData['rcp_to'] ?? ''),
            false
        ));
        $form->addItem($to);

        if ($mailData['rcp_cc']) {
            $cc = new ilCustomInputGUI($this->lng->txt('mail_cc') . ':');
            $cc->setHtml(ilUtil::htmlencodePlainString(
                $this->umail->formatNamesForOutput($mailData['rcp_cc'] ?? ''),
                false
            ));
            $form->addItem($cc);
        }

        if ($mailData['rcp_bcc']) {
            $bcc = new ilCustomInputGUI($this->lng->txt('mail_bcc') . ':');
            $bcc->setHtml(ilUtil::htmlencodePlainString(
                $this->umail->formatNamesForOutput($mailData['rcp_bcc'] ?? ''),
                false
            ));
            $form->addItem($bcc);
        }

        $subject = new ilCustomInputGUI($this->lng->txt('subject') . ':');
        $subject->setHtml(ilUtil::htmlencodePlainString($mailData['m_subject'] ?? '', true));
        $form->addItem($subject);

        $date = new ilCustomInputGUI($this->lng->txt('mail_sent_datetime') . ':');
        $date->setHtml(ilDatePresentation::formatDate(
            new ilDateTime($mailData['send_time'], IL_CAL_DATETIME)
        ));
        $form->addItem($date);

        $message = new ilCustomInputGUI($this->lng->txt('message') . ':');
        $message->setHtml(ilUtil::htmlencodePlainString($mailData['m_message'] ?? '', true));
        $form->addItem($message);

        if ($mailData['attachments']) {
            $att = new ilCustomInputGUI($this->lng->txt('attachments') . ':');

            $radiog = new ilRadioGroupInputGUI('', 'filename');
            foreach ($mailData['attachments'] as $file) {
                $radiog->addOption(new ilRadioOption($file, md5($file)));
            }

            $att->setHtml($radiog->render());
            $form->addCommandButton(self::CMD_DELIVER_FILE, $this->lng->txt('download'));
            $form->addItem($att);
        }

        $current_folder = $this->mbox->getFolderData((int) $mailData['folder_id']);
        if ($current_folder === null) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_operation_on_invalid_folder'), true);
            $this->ctrl->setParameterByClass(ilMailGUI::class, self::PARAM_FOLDER_ID, $this->mbox->getInboxFolder());
            $this->ctrl->redirectByClass(ilMailGUI::class);
        }

        $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $this->folder->getFolderId());
        $this->tabs->addTab(
            'current_folder',
            $current_folder->getTitle(),
            $this->ctrl->getLinkTarget($this, self::CMD_SHOW_FOLDER)
        );
        $this->ctrl->clearParameters($this);
        $this->tabs->activateTab('current_folder');

        $move_links = [];
        $folders = $this->mbox->getSubFolders();
        foreach ($folders as $folder) {
            if ((!$folder->isTrash() || !$current_folder->isTrash()) &&
                $folder->getFolderId() !== $mailData['folder_id']) {

                $move_links[] = $this->ui_factory->button()->shy(
                    sprintf(
                        $this->lng->txt('mail_move_to_folder_x'),
                        $folder->getTitle()
                    ) . ($folder->isTrash() ? ' (' . $this->lng->txt('delete') . ')' : ''),
                    '#',
                )->withOnLoadCode(static fn($id): string => "
                        document.getElementById('$id').addEventListener('click', function(e) {
                            const frm = this.closest('form'),
                                action = new URL(frm.action),
                                action_params = new URLSearchParams(action.search);

                            action_params.delete('cmd');
                            action_params.append('cmd', '" . self::CMD_MOVE_SINGLE_MAIL . "');
                            action_params.delete('folder_id');
                            action_params.append('folder_id', '" . $folder->getFolderId() . "');

                            action.search = action_params.toString();

                            frm.action = action.href;
                            frm.submit();

                            e.preventDefault();
                            e.stopPropagation();

                            return false;
                        });");
            }
        }

        if ($current_folder->isTrash()) {
            $deleteBtn = $this->ui_factory->button()
                                          ->standard($this->lng->txt('delete'), '#')
                                          ->withOnLoadCode(static fn($id): string => "
                    document.getElementById('$id').addEventListener('click', function() {
                        const frm = this.closest('form'),
                            action = new URL(frm.action),
                            action_params = new URLSearchParams(action.search);
    
                        action_params.delete('cmd');
                        action_params.append('cmd', '" . self::CMD_DELETE_MAILS . "');
    
                        action.search = action_params.toString();
    
                        frm.action = action.href;
                        frm.submit();
                        return false;
                    });
                ");
            $this->toolbar->addComponent($deleteBtn);
        }

        if ($move_links !== []) {
            $this->toolbar->addComponent(
                $this->ui_factory->dropdown()->standard($move_links)
                                             ->withLabel($this->lng->txt('mail_move_to_folder_btn_label'))
            );
        }

        $this->toolbar->addSeparator();

        $this->ctrl->setParameter($this, self::PARAM_MAIL_ID, $mailId);
        $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $mailData['folder_id']);
        $print_url = $this->ctrl->getLinkTarget($this, self::CMD_PRINT_MAIL);
        $this->ctrl->clearParameters($this);
        $print_btn = $this->ui_factory->button()
                                      ->standard($this->lng->txt('print'), '#')
                                      ->withOnLoadCode(static fn($id): string => "
                document.getElementById('$id').addEventListener('click', function() {
                    const frm = this.closest('form'),
                        action = frm.action;

                    frm.action = '$print_url';
                    frm.target = '_blank';
                    frm.submit();

                    frm.action = action;
                    frm.removeAttribute('target');

                    return false;
                });
            ");
        $this->toolbar->addComponent($print_btn);

        $prevMail = $this->umail->getPreviousMail($mailId);
        $nextMail = $this->umail->getNextMail($mailId);
        if (is_array($prevMail) || is_array($nextMail)) {
            $this->toolbar->addSeparator();

            if ($prevMail && $prevMail[self::PARAM_MAIL_ID]) {
                $this->ctrl->setParameter($this, self::PARAM_MAIL_ID, $prevMail[self::PARAM_MAIL_ID]);
                $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $this->folder->getFolderId());
                $prevBtn = $this->ui_factory->button()
                                            ->standard(
                                                $this->lng->txt('previous'),
                                                $this->ctrl->getLinkTarget($this, self::CMD_SHOW_MAIL)
                                            );
                $this->toolbar->addComponent($prevBtn);
                $this->ctrl->clearParameters($this);
            }

            if ($nextMail && $nextMail[self::PARAM_MAIL_ID]) {
                $this->ctrl->setParameter($this, self::PARAM_MAIL_ID, $nextMail[self::PARAM_MAIL_ID]);
                $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $this->folder->getFolderId());
                $nextBtn = $this->ui_factory->button()
                                            ->standard(
                                                $this->lng->txt('next'),
                                                $this->ctrl->getLinkTarget($this, self::CMD_SHOW_MAIL)
                                            );
                $this->toolbar->addComponent($nextBtn);
                $this->ctrl->clearParameters($this);
            }
        }

        $this->tpl->setContent($form->getHTML());
        $this->tpl->printToStdout();
    }

    protected function printMail(): void
    {
        $tplprint = new ilTemplate('tpl.mail_print.html', true, true, 'components/ILIAS/Mail');

        $mailId = $this->getMailIdsFromRequest()[0] ?? 0;
        $mailData = $this->umail->getMail($mailId);

        $sender = ilObjectFactory::getInstanceByObjId($mailData['sender_id'], false);

        $tplprint->setVariable('TXT_FROM', $this->lng->txt('from'));
        if ($sender instanceof ilObjUser && $sender->getId() !== 0 && !$sender->isAnonymous()) {
            $tplprint->setVariable('FROM', $sender->getPublicName());
        } elseif (!$sender instanceof ilObjUser || 0 === $sender->getId()) {
            $tplprint->setVariable(
                'FROM',
                trim(($mailData['import_name'] ?? '') . ' (' . $this->lng->txt('user_deleted') . ')')
            );
        } else {
            $tplprint->setVariable('FROM', ilMail::_getIliasMailerName());
        }

        $tplprint->setVariable('TXT_TO', $this->lng->txt('mail_to'));
        $tplprint->setVariable('TO', $mailData['rcp_to']);

        if ($mailData['rcp_cc']) {
            $tplprint->setCurrentBlock('cc');
            $tplprint->setVariable('TXT_CC', $this->lng->txt('mail_cc'));
            $tplprint->setVariable('CC', $mailData['rcp_cc']);
            $tplprint->parseCurrentBlock();
        }

        if ($mailData['rcp_bcc']) {
            $tplprint->setCurrentBlock('bcc');
            $tplprint->setVariable('TXT_BCC', $this->lng->txt('mail_bcc'));
            $tplprint->setVariable('BCC', $mailData['rcp_bcc']);
            $tplprint->parseCurrentBlock();
        }

        $tplprint->setVariable('TXT_SUBJECT', $this->lng->txt('subject'));
        $tplprint->setVariable('SUBJECT', htmlspecialchars($mailData['m_subject']));

        $tplprint->setVariable('TXT_DATE', $this->lng->txt('date'));
        $tplprint->setVariable(
            'DATE',
            ilDatePresentation::formatDate(new ilDateTime($mailData['send_time'], IL_CAL_DATETIME))
        );

        $tplprint->setVariable('TXT_MESSAGE', $this->lng->txt('message'));
        $tplprint->setVariable('MAIL_MESSAGE', nl2br(htmlspecialchars($mailData['m_message'])));

        $tplprint->show();
    }

    protected function deliverFile(): void
    {
        $mailId = 0;
        if ($this->http->wrapper()->query()->has(self::PARAM_MAIL_ID)) {
            $mailId = $this->http->wrapper()->query()->retrieve(self::PARAM_MAIL_ID, $this->refinery->kindlyTo()->int());
        }

        if ($mailId <= 0) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $filename = '';
        if ($this->http->wrapper()->post()->has('filename')) {
            $filename = $this->http->wrapper()->post()->retrieve('filename', $this->refinery->kindlyTo()->string());
        }

        if (is_string(ilSession::get('filename')) && ilSession::get('filename') !== '') {
            $filename = ilSession::get('filename');
            ilSession::set('filename', null);
        }

        try {
            if ($mailId > 0 && $filename !== '') {
                while (str_contains((string) $filename, '..')) {
                    $filename = str_replace('..', '', $filename);
                }

                $mailFileData = new ilFileDataMail($this->user->getId());
                try {
                    $file = $mailFileData->getAttachmentPathAndFilenameByMd5Hash($filename, (int) $mailId);
                    ilFileDelivery::deliverFileLegacy($file['path'], $file['filename']);
                } catch (OutOfBoundsException $e) {
                    throw new ilMailException('mail_error_reading_attachment', $e->getCode(), $e);
                }
            } else {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_attachment'));
                $this->showMail();
            }
        } catch (Exception $e) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt($e->getMessage()), true);
            $this->redirectToFolder();
        }
    }

    protected function deliverAttachments(): void
    {
        try {
            $mailId = $this->getMailIdsFromRequest()[0] ?? 0;
            $mailData = $this->umail->getMail((int) $mailId);
            if (null === $mailData || [] === (array) $mailData['attachments']) {
                throw new ilMailException('mail_error_reading_attachment');
            }

            $type = '';
            if ($this->http->wrapper()->query()->has('type')) {
                $type = $this->http->wrapper()->query()->retrieve('type', $this->refinery->kindlyTo()->string());
            }

            $mailFileData = new ilFileDataMail($this->user->getId());
            if (count($mailData['attachments']) === 1) {
                $attachment = current($mailData['attachments']);

                try {
                    if ('draft' === $type) {
                        if (!$mailFileData->checkFilesExist([$attachment])) {
                            throw new OutOfBoundsException('');
                        }
                        $pathToFile = $mailFileData->getAbsoluteAttachmentPoolPathByFilename($attachment);
                        $fileName = $attachment;
                    } else {
                        $file = $mailFileData->getAttachmentPathAndFilenameByMd5Hash(
                            md5($attachment),
                            (int) $mailId
                        );
                        $pathToFile = $file['path'];
                        $fileName = $file['filename'];
                    }
                    ilFileDelivery::deliverFileLegacy($pathToFile, $fileName);
                } catch (OutOfBoundsException $e) {
                    throw new ilMailException('mail_error_reading_attachment', $e->getCode(), $e);
                }
            } else {
                $mailFileData->deliverAttachmentsAsZip(
                    $mailData['m_subject'],
                    (int) $mailId,
                    $mailData['attachments'],
                    'draft' === $type
                );
            }
        } catch (Exception $e) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt($e->getMessage()), true);
            $this->redirectToFolder();
        }
    }
}
