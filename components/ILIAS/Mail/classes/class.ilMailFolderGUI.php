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
use ILIAS\Mail\Folder\MailFolderData;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\User\Profile\PublicProfileGUI;

/**
 * @ilCtrl_Calls ilMailFolderGUI: ILIAS\User\Profile\PublicProfileGUI
 */
class ilMailFolderGUI implements ilCtrlSecurityInterface
{
    // used as single element namespace for UrlBuilder
    // added with '_' before parameter names in queries from the table
    private const string URL_BUILDER_PREFIX = 'ilMailFolderGUI';

    // controller parameters
    private const string PARAM_ACTION = 'action';
    private const string PARAM_FOLDER_ID = 'mobj_id';
    private const string PARAM_MAIL_ID = 'mail_id';
    private const string PARAM_USER_ID = 'user_id';
    private const string PARAM_TARGET_FOLDER = 'target_folder';
    private const string PARAM_INTERRUPTIVE_ITEMS = 'interruptive_items';

    // controller commands
    private const string CMD_ADD_SUB_FOLDER = 'addSubFolder';
    private const string CMD_DELETE_MAILS = 'deleteMails';
    private const string CMD_DELETE_SUB_FOLDER = 'deleteSubFolder';
    private const string CMD_DELIVER_FILE = 'deliverFile';
    private const string CMD_EMPTY_TRASH = 'emptyTrash';
    private const string CMD_MOVE_SINGLE_MAIL = 'moveSingleMail';
    private const string CMD_PRINT_MAIL = 'printMail';
    private const string CMD_RENAME_SUB_FOLDER = 'renameSubFolder';
    private const string CMD_SHOW_MAIL = 'showMail';
    private const string CMD_SHOW_FOLDER = 'showFolder';
    private const string CMD_SHOW_USER = 'showUser';
    private const string CMD_TABLE_ACTION = 'executeTableAction';

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

    public function getUnsafeGetCommands(): array
    {
        return [
            self::CMD_TABLE_ACTION
        ];
    }

    public function getSafePostCommands(): array
    {
        return [];
    }

    /**
     * Init class variables that can be determined in an actual request
     */
    protected function initRequest(): void
    {
        $this->umail = new ilMail($this->user->getId());
        $this->mbox = new ilMailbox($this->user->getId());

        if ($this->http->wrapper()->post()->has(self::PARAM_FOLDER_ID)) {
            $folder_id = $this->http->wrapper()->post()->retrieve(
                self::PARAM_FOLDER_ID,
                $this->refinery->kindlyTo()->int()
            );
        } elseif ($this->http->wrapper()->query()->has(self::PARAM_FOLDER_ID)) {
            $folder_id = $this->http->wrapper()->query()->retrieve(
                self::PARAM_FOLDER_ID,
                $this->refinery->kindlyTo()->int()
            );
        } else {
            $folder_id = $this->refinery->byTrying([
                $this->refinery->kindlyTo()->int(),
                $this->refinery->always(0),
            ])->transform(ilSession::get(self::PARAM_FOLDER_ID));
        }

        if ($folder_id === 0 || !$this->mbox->isOwnedFolder($folder_id)) {
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

        $next_class = $this->ctrl->getNextClass($this) ?? '';
        switch (strtolower($next_class)) {
            case strtolower(ilContactGUI::class):
                $this->ctrl->forwardCommand(new ilContactGUI());
                break;

            default:
                $cmd = $this->ctrl->getCmd() ?? '';
                match ($cmd) {
                    self::CMD_ADD_SUB_FOLDER, self::CMD_DELETE_MAILS, self::CMD_DELETE_SUB_FOLDER, self::CMD_DELIVER_FILE, self::CMD_EMPTY_TRASH, self::CMD_MOVE_SINGLE_MAIL, self::CMD_PRINT_MAIL, self::CMD_RENAME_SUB_FOLDER, self::CMD_SHOW_MAIL, self::CMD_SHOW_FOLDER, self::CMD_SHOW_USER, self::CMD_TABLE_ACTION => $this->{$cmd}(
                    ),
                    default => $this->showFolder(),
                };
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
        $for_all_entries = implode(
            '',
            $this->http->wrapper()->query()->retrieve(
                self::URL_BUILDER_PREFIX . URLBuilder::SEPARATOR . self::PARAM_MAIL_ID,
                $this->refinery->byTrying([
                        $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()),
                        $this->refinery->always([])
                    ])
            )
        ) === 'ALL_OBJECTS';

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
                        $this->ui_renderer->render(
                            $this->ui_factory->messageBox()->failure($this->lng->txt('mail_select_one'))
                        ),
                        $this->lng->txt('delete'),
                    )
                );
                $this->http->saveResponse(
                    $this->http->response()->withBody(
                        Streams::ofString($this->ui_renderer->renderAsync($modal))
                    )
                );
                $this->http->sendResponse();
                $this->http->close();
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
                $drafts_folder_id = $this->mbox->getDraftsFolder();
                $this->ctrl->setParameterByClass(ilMailFormGUI::class, self::PARAM_MAIL_ID, (string) $mail_ids[0]);
                if ($this->folder->isOutbox()) {
                    $this->umail->moveMailsToFolder($mail_ids, $drafts_folder_id);
                    $this->ctrl->setParameterByClass(
                        ilMailFormGUI::class,
                        ilMailFormGUI::PARAM_SCHEDULED_EDIT_FROM_OUTBOX,
                        '1'
                    );
                    $this->tpl->setOnScreenMessage(
                        ilGlobalTemplateInterface::MESSAGE_TYPE_INFO,
                        $this->lng->txt('mail_scheduled_edit_moved_info'),
                        true
                    );
                }
                $this->ctrl->setParameterByClass(
                    ilMailFormGUI::class,
                    self::PARAM_FOLDER_ID,
                    (string) $drafts_folder_id
                );
                $this->ctrl->setParameterByClass(
                    ilMailFolderGUI::class,
                    self::PARAM_FOLDER_ID,
                    (string) $drafts_folder_id
                );
                $this->ctrl->setParameterByClass(ilMailFormGUI::class, 'type', ilMailFormGUI::MAIL_FORM_TYPE_DRAFT);
                $this->ctrl->redirectByClass(ilMailFormGUI::class);

                // no break
            case MailFolderTableUI::ACTION_REPLY:
                $this->ctrl->setParameterByClass(
                    ilMailFormGUI::class,
                    self::PARAM_FOLDER_ID,
                    (string) $this->folder->getFolderId()
                );
                $this->ctrl->setParameterByClass(ilMailFormGUI::class, self::PARAM_MAIL_ID, (string) $mail_ids[0]);
                $this->ctrl->setParameterByClass(ilMailFormGUI::class, 'type', ilMailFormGUI::MAIL_FORM_TYPE_REPLY);
                $this->ctrl->redirectByClass(ilMailFormGUI::class);

                // no break
            case MailFolderTableUI::ACTION_FORWARD:
                $this->ctrl->setParameterByClass(
                    ilMailFormGUI::class,
                    self::PARAM_FOLDER_ID,
                    (string) $this->folder->getFolderId()
                );
                $this->ctrl->setParameterByClass(ilMailFormGUI::class, self::PARAM_MAIL_ID, (string) $mail_ids[0]);
                $this->ctrl->setParameterByClass(ilMailFormGUI::class, 'type', ilMailFormGUI::MAIL_FORM_TYPE_FORWARD);
                $this->ctrl->redirectByClass(ilMailFormGUI::class);

                // no break
            case MailFolderTableUI::ACTION_DOWNLOAD_ATTACHMENT:
                $this->deliverAttachments();
                return;

            case MailFolderTableUI::ACTION_PRINT:
                $this->printMail();
                return;

            case MailFolderTableUI::ACTION_PROFILE:
                $mail_data = $this->umail->getMail($mail_ids[0] ?? 0);
                if (!empty($user = ilMailUserCache::getUserObjectById($mail_data['sender_id'] ?? 0)) &&
                    $user->hasPublicProfile()) {
                    $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, (string) $this->folder->getFolderId());
                    $this->ctrl->setParameter($this, self::PARAM_USER_ID, (string) $user->getId());
                    $this->ctrl->redirect($this, self::CMD_SHOW_USER);
                } else {
                    $this->tpl->setOnScreenMessage(
                        ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                        $this->lng->txt('permission_denied'),
                        true
                    );
                }
                break;

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
                if (empty($folder_id) || $folder_id === $this->mbox->getOutboxFolder()) {
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

            case MailFolderTableUI::ACTION_DELETE: // async call
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
        $usr_id = $this->http->wrapper()->query()->retrieve(
            self::PARAM_USER_ID,
            $this->refinery->byTrying([$this->refinery->kindlyTo()->int(), $this->refinery->always(0)])
        );

        $this->tpl->setVariable('TBL_TITLE', implode(' ', [
            $this->lng->txt('profile_of'),
            ilObjUser::_lookupLogin($usr_id),
        ]));
        $this->tpl->setVariable('TBL_TITLE_IMG', ilUtil::getImagePath('standard/icon_usr.svg'));
        $this->tpl->setVariable('TBL_TITLE_IMG_ALT', $this->lng->txt('public_profile'));

        $profile_gui = new PublicProfileGUI($usr_id);

        $mail_id = $this->http->wrapper()->query()->retrieve(
            self::PARAM_MAIL_ID,
            $this->refinery->byTrying([$this->refinery->kindlyTo()->int(), $this->refinery->always(0)])
        );

        if ($mail_id > 0) {
            $this->ctrl->setParameter($this, self::PARAM_MAIL_ID, $mail_id);
            $this->tabs->clearTargets();
            $this->tabs->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, self::CMD_SHOW_MAIL));
        } else {
            $this->tabs->clearTargets();
            $this->tabs->setBackTarget(
                $this->lng->txt('back_to_folder'),
                $this->ctrl->getLinkTarget($this, self::CMD_SHOW_FOLDER)
            );
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
            $this->toolbar->addComponent(
                $this->ui_factory->button()->standard(
                    $this->lng->txt('mail_add_subfolder'),
                    $this->ctrl->getLinkTarget($this, self::CMD_ADD_SUB_FOLDER)
                )
            );
        }

        if ($this->folder->isUserFolder()) {
            $this->toolbar->addComponent(
                $this->ui_factory->button()->standard(
                    $this->lng->txt('rename'),
                    $this->ctrl->getLinkTarget($this, self::CMD_RENAME_SUB_FOLDER)
                )
            );

            $components[] = $modal = $this->ui_factory->modal()->interruptive(
                $this->lng->txt('delete'),
                $this->lng->txt('mail_sure_delete_folder'),
                $this->ctrl->getLinkTarget($this, self::CMD_DELETE_SUB_FOLDER)
            );
            $this->toolbar->addComponent(
                $this->ui_factory->button()->standard(
                    $this->lng->txt('delete'),
                    '#'
                )
                                 ->withOnClick($modal->getShowSignal())
            );
        }

        if ($this->folder->isTrash()) {
            $components[] = $modal = $this->ui_factory->modal()->interruptive(
                $this->lng->txt('mail_empty_trash'),
                $this->lng->txt('mail_empty_trash_confirmation'),
                $this->ctrl->getLinkTarget($this, self::CMD_EMPTY_TRASH)
            );
            $this->toolbar->addComponent(
                $this->ui_factory->button()->standard(
                    $this->lng->txt('mail_empty_trash'),
                    '#'
                )->withOnClick($modal->getShowSignal())
            );
        }

        [
            $url_builder,
            $action_token,
            $row_id_token,
            $target_token,
        ] = (new URLBuilder(
            $this->data_factory->uri(
                ilUtil::_getHttpPath() . '/' .
                $this->ctrl->getLinkTarget($this, self::CMD_TABLE_ACTION)
            )
        )
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
            $this->user->getTimeFormat(),
            new DateTimeZone($this->user->getTimeZone())
        );

        if ($this->folder->isOutbox()) {
            $components[] = $this->ui_factory->messageBox()->info(
                $this->lng->txt('mail_message_scheduled_info')
            );
        }

        $components[] = $this->getFilterUI()->getComponent();
        $components[] = $table->getComponent();

        $this->tpl->setTitle($this->folder->getTitle());
        $this->tpl->setContent($this->ui_renderer->render($components));
        $this->tpl->printToStdout();
    }

    protected function redirectToFolder(): never
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

    protected function addSubFolder(): void
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
        if ($request->getMethod() === 'POST') {
            $form = $form->withRequest($request);
            $data = $form->getData();
            if (!empty($data['folder']['title'])) {
                $new_folder_id = $this->mbox->addFolder(
                    $this->folder->getFolderId(),
                    (string) $data['folder']['title']
                );
                if ($new_folder_id > 0) {
                    $this->tpl->setOnScreenMessage(
                        ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
                        $this->lng->txt('mail_folder_created'),
                        true
                    );
                    $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $new_folder_id);
                    $this->ctrl->redirect($this, self::CMD_SHOW_FOLDER);
                } else {
                    $this->tpl->setOnScreenMessage(
                        ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                        $this->lng->txt('mail_folder_exists')
                    );
                }
            }
        }
        $this->tpl->setContent($this->ui_renderer->render($form));
        $this->tpl->printToStdout();
    }

    protected function renameSubFolder(): void
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
        if ($request->getMethod() === 'POST') {
            $form = $form->withRequest($request);
            $data = $form->getData();
            if (!empty($data['folder']['title'])) {
                if ($this->mbox->renameFolder($this->folder->getFolderId(), (string) $data['folder']['title'])) {
                    $this->tpl->setOnScreenMessage(
                        ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
                        $this->lng->txt('mail_folder_name_changed'),
                        true
                    );
                    $this->redirectToFolder();
                } else {
                    $this->tpl->setOnScreenMessage(
                        ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                        $this->lng->txt('mail_folder_exists')
                    );
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
        // table actions have a prefix, controller commands and modal items have none
        foreach (
            [
                self::URL_BUILDER_PREFIX . URLBuilder::SEPARATOR . self::PARAM_MAIL_ID,
                self::PARAM_MAIL_ID,
                self::PARAM_INTERRUPTIVE_ITEMS
            ] as $param
        ) {
            foreach (
                [
                    $this->http->wrapper()->post(),
                    $this->http->wrapper()->query()
                ] as $wrapper
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
        $mail_ids = $this->getMailIdsFromRequest();
        if (count($mail_ids) !== 1) {
            $this->showMail();
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_one'));
            return;
        }

        $new_folder_id = $this->http->wrapper()->query()->retrieve(
            'folder_id',
            $this->refinery->byTrying([$this->refinery->kindlyTo()->int(), $this->refinery->always(0)])
        );
        $redirect_folder_id = $new_folder_id;

        foreach ($mail_ids as $mail_id) {
            $mail_data = $this->umail->getMail($mail_id);
            if (isset($mail_data['folder_id']) &&
                is_numeric($mail_data['folder_id']) &&
                (int) $mail_data['folder_id'] > 0) {
                $redirect_folder_id = (int) $mail_data['folder_id'];
                break;
            }
        }

        if ($this->umail->moveMailsToFolder($mail_ids, $new_folder_id)) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_moved'), true);
            $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $redirect_folder_id);
            $this->ctrl->redirect($this, self::CMD_SHOW_FOLDER);
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_move_error'));
            $this->showMail();
        }
    }

    protected function deleteMails(): void
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
        $this->tpl->setOnScreenMessage(
            ilGlobalTemplateInterface::MESSAGE_TYPE_INFO,
            $this->lng->txt('mail_deleted'),
            true
        );
        $this->redirectToFolder();
    }

    /**
     * Confirm the deletion of selected mails in async modal
     * @param int[] $mail_ids
     */
    protected function confirmDeleteMails(array $mail_ids): void
    {
        $user_timezone = new DateTimeZone($this->user->getTimeZone());
        $records = $this->getFilteredSearch()->forMailIds($mail_ids)->getPagedRecords(10, 0, null, null);
        $items = [];
        foreach ($records as $record) {
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
            $this->lng->txt('mail_sure_delete_' . (count($items) === 1 ? 's' : 'p')),
            $this->ctrl->getFormAction($this, self::CMD_DELETE_MAILS)
        )->withAffectedItems($items);

        $this->http->saveResponse(
            $this->http->response()->withBody(
                Streams::ofString($this->ui_renderer->renderAsync($modal))
            )
        );
        $this->http->sendResponse();
        $this->http->close();
    }

    protected function showMail(): void
    {
        $ui_components = [];

        $mail_id = $this->getMailIdsFromRequest()[0] ?? 0;
        if ($mail_id <= 0) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $mail_data = $this->umail->getMail($mail_id);
        if ($mail_data === null) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->umail->markRead([$mail_id]);

        $this->tpl->setTitle($this->lng->txt('mail_mails_of'));

        $this->tabs->clearTargets();
        $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $mail_data['folder_id']);
        $this->tabs->setBackTarget(
            $this->lng->txt('back_to_folder'),
            $this->ctrl->getFormAction($this, self::CMD_SHOW_FOLDER)
        );
        $this->ctrl->clearParameters($this);

        $this->ctrl->setParameter($this, self::PARAM_MAIL_ID, $mail_id);
        $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $mail_data['folder_id']);
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, self::CMD_SHOW_MAIL));
        $this->ctrl->clearParameters($this);

        $form = new ilPropertyFormGUI();
        $form->setId('MailContent');
        $form->setPreventDoubleSubmission(false);
        $form->setTableWidth('100%');
        $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $mail_data['folder_id']);
        $this->ctrl->setParameter($this, self::PARAM_MAIL_ID, $mail_id);
        $form->setFormAction($this->ctrl->getFormAction($this, self::CMD_SHOW_MAIL));
        $this->ctrl->clearParameters($this);
        $form->setTitle($this->lng->txt('mail_mails_of'));

        /** @var ilObjUser|null $sender */
        $sender = ilObjectFactory::getInstanceByObjId($mail_data['sender_id'], false);
        $reply_btn = null;
        if ($sender instanceof ilObjUser && $sender->getId() !== 0 && !$sender->isAnonymous()) {
            $this->ctrl->setParameterByClass(
                ilMailFormGUI::class,
                self::PARAM_FOLDER_ID,
                $mail_data['folder_id']
            );
            $this->ctrl->setParameterByClass(ilMailFormGUI::class, self::PARAM_MAIL_ID, $mail_id);
            $this->ctrl->setParameterByClass(ilMailFormGUI::class, 'type', ilMailFormGUI::MAIL_FORM_TYPE_REPLY);
            $reply_btn = $this->ui_factory->button()->primary(
                $this->lng->txt('reply'),
                $this->ctrl->getLinkTargetByClass(ilMailFormGUI::class)
            );
            $this->toolbar->addStickyItem($reply_btn);
            $this->ctrl->clearParametersByClass(ilMailFormGUI::class);
        }

        $this->ctrl->setParameterByClass(ilMailFormGUI::class, self::PARAM_FOLDER_ID, $mail_data['folder_id']);
        $this->ctrl->setParameterByClass(ilMailFormGUI::class, self::PARAM_MAIL_ID, $mail_id);
        $this->ctrl->setParameterByClass(ilMailFormGUI::class, 'type', ilMailFormGUI::MAIL_FORM_TYPE_FORWARD);
        if ($reply_btn === null) {
            $fwd_btn = $this->ui_factory->button()->primary(
                $this->lng->txt('forward'),
                $this->ctrl->getLinkTargetByClass(ilMailFormGUI::class)
            );
            $this->toolbar->addStickyItem($fwd_btn);
        } else {
            $fwd_btn = $this->ui_factory->button()->standard(
                $this->lng->txt('forward'),
                $this->ctrl->getLinkTargetByClass(ilMailFormGUI::class)
            );
            $this->toolbar->addComponent($fwd_btn);
        }
        $this->ctrl->clearParametersByClass(ilMailFormGUI::class);

        if ($sender && $sender->getId() && !$sender->isAnonymous()) {
            $linked_fullname = $sender->getPublicName();
            $avatar = $this->ui_factory->symbol()->avatar()->picture(
                $sender->getPersonalPicturePath('xsmall'),
                $sender->getPublicName()
            );

            if (in_array(ilObjUser::_lookupPref($sender->getId(), 'public_profile'), ['y', 'g'])) {
                $this->ctrl->setParameter($this, self::PARAM_MAIL_ID, $mail_id);
                $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $mail_data['folder_id']);
                $this->ctrl->setParameter($this, self::PARAM_USER_ID, $sender->getId());
                $linked_fullname = '<br /><a class="mailusername" href="' . $this->ctrl->getLinkTarget(
                    $this,
                    self::CMD_SHOW_USER
                ) . '" title="' . $linked_fullname . '">' . $linked_fullname . '</a>';
                $this->ctrl->clearParameters($this);
            }

            $from = new ilCustomInputGUI($this->lng->txt('from') . ':');
            $from->setHtml($this->ui_renderer->render($avatar) . ' ' . $linked_fullname);
        } elseif (!$sender || !$sender->getId()) {
            $from = new ilCustomInputGUI($this->lng->txt('from') . ':');
            $from->setHtml(trim(($mail_data['import_name'] ?? '') . ' (' . $this->lng->txt('user_deleted') . ')'));
        } else {
            $from = new ilCustomInputGUI($this->lng->txt('from') . ':');
            $from->setHtml(
                $this->ui_renderer->render(
                    $this->ui_factory
                        ->symbol()
                        ->avatar()
                        ->picture(ilUtil::getImagePath('logo/HeaderIconAvatar.svg'), ilMail::_getIliasMailerName())
                ) . '<br />' . ilMail::_getIliasMailerName()
            );
        }
        $form->addItem($from);

        $to = new ilCustomInputGUI($this->lng->txt('mail_to') . ':');
        $to->setHtml(
            ilUtil::htmlencodePlainString(
                $this->umail->formatNamesForOutput($mail_data['rcp_to'] ?? ''),
                false
            )
        );
        $form->addItem($to);

        if ($mail_data['rcp_cc']) {
            $cc = new ilCustomInputGUI($this->lng->txt('mail_cc') . ':');
            $cc->setHtml(
                ilUtil::htmlencodePlainString(
                    $this->umail->formatNamesForOutput($mail_data['rcp_cc']),
                    false
                )
            );
            $form->addItem($cc);
        }

        if ($mail_data['rcp_bcc']) {
            $bcc = new ilCustomInputGUI($this->lng->txt('mail_bcc') . ':');
            $bcc->setHtml(
                ilUtil::htmlencodePlainString(
                    $this->umail->formatNamesForOutput($mail_data['rcp_bcc']),
                    false
                )
            );
            $form->addItem($bcc);
        }

        $subject = new ilCustomInputGUI($this->lng->txt('subject') . ':');
        $subject->setHtml(ilUtil::htmlencodePlainString($mail_data['m_subject'] ?? '', true));
        $form->addItem($subject);

        $date = new ilCustomInputGUI($this->lng->txt('mail_sent_datetime') . ':');
        $date->setHtml(
            ilDatePresentation::formatDate(
                new ilDateTime($mail_data['send_time'], IL_CAL_DATETIME)
            )
        );
        $form->addItem($date);

        $message = new ilCustomInputGUI($this->lng->txt('message') . ':');
        $message->setHtml(
            str_replace(
                ['{', '}'],
                ['&#123;', '&#125;'],
                $this->refinery->string()->makeClickable()->transform(
                    html_entity_decode(
                        $this->refinery->string()->markdown()->toHTML()->transform($mail_data['m_message']) ?? ''
                    )
                )
            )
        );

        $form->addItem($message);

        if ($mail_data['attachments']) {
            $att = new ilCustomInputGUI($this->lng->txt('attachments') . ':');

            $radiog = new ilRadioGroupInputGUI('', 'filename');
            foreach ($mail_data['attachments'] as $file) {
                $radiog->addOption(new ilRadioOption($file, md5($file)));
            }

            $att->setHtml($radiog->render());
            $form->addCommandButton(self::CMD_DELIVER_FILE, $this->lng->txt('download'));
            $form->addItem($att);
        }

        $current_folder = $this->mbox->getFolderData((int) $mail_data['folder_id']);
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
                $folder->getFolderId() !== $mail_data['folder_id']) {
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

        if ($this->folder->isTrash()) {
            $this->ctrl->setParameter($this, self::PARAM_MAIL_ID, $mail_id);
            $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $mail_data['folder_id']);
            $modal = $this->ui_factory->modal()->interruptive(
                $this->lng->txt('delete'),
                $this->lng->txt('mail_sure_delete_s'),
                $this->ctrl->getLinkTarget($this, self::CMD_DELETE_MAILS)
            )->withAffectedItems([
                $this->ui_factory->modal()->interruptiveItem()->standard(
                    (string) $mail_id,
                    ilDatePresentation::formatDate(
                        new ilDateTime($mail_data['send_time'], IL_CAL_DATETIME)
                    ) . ' ' . $mail_data['m_subject']
                )
            ]);
            $this->toolbar->addComponent(
                $this->ui_factory->button()->standard(
                    $this->lng->txt('delete'),
                    '#'
                )->withOnClick($modal->getShowSignal())
            );
            $this->ctrl->clearParameters($this);

            $ui_components[] = $modal;
        }

        if ($move_links !== []) {
            $this->toolbar->addComponent(
                $this->ui_factory->dropdown()->standard($move_links)
                                 ->withLabel($this->lng->txt('mail_move_to_folder_btn_label'))
            );
        }

        $this->toolbar->addSeparator();

        $this->ctrl->setParameter($this, self::PARAM_MAIL_ID, $mail_id);
        $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $mail_data['folder_id']);
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

        $prev_mail = $this->umail->getPreviousMail($mail_id);
        $next_mail = $this->umail->getNextMail($mail_id);
        if (is_array($prev_mail) || is_array($next_mail)) {
            $this->toolbar->addSeparator();

            if ($prev_mail && $prev_mail[self::PARAM_MAIL_ID]) {
                $this->ctrl->setParameter($this, self::PARAM_MAIL_ID, $prev_mail[self::PARAM_MAIL_ID]);
                $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $this->folder->getFolderId());
                $pref_btn = $this->ui_factory->button()
                                             ->standard(
                                                 $this->lng->txt('previous'),
                                                 $this->ctrl->getLinkTarget($this, self::CMD_SHOW_MAIL)
                                             );
                $this->toolbar->addComponent($pref_btn);
                $this->ctrl->clearParameters($this);
            }

            if ($next_mail && $next_mail[self::PARAM_MAIL_ID]) {
                $this->ctrl->setParameter($this, self::PARAM_MAIL_ID, $next_mail[self::PARAM_MAIL_ID]);
                $this->ctrl->setParameter($this, self::PARAM_FOLDER_ID, $this->folder->getFolderId());
                $next_btn = $this->ui_factory->button()
                                             ->standard(
                                                 $this->lng->txt('next'),
                                                 $this->ctrl->getLinkTarget($this, self::CMD_SHOW_MAIL)
                                             );
                $this->toolbar->addComponent($next_btn);
                $this->ctrl->clearParameters($this);
            }
        }

        $this->tpl->setContent($form->getHTML() . $this->ui_renderer->render($ui_components));
        $this->tpl->printToStdout();
    }

    protected function printMail(): void
    {
        $tplprint = new ilTemplate('tpl.mail_print.html', true, true, 'components/ILIAS/Mail');

        $mail_id = $this->getMailIdsFromRequest()[0] ?? 0;
        $mail_data = $this->umail->getMail($mail_id);

        $sender = ilObjectFactory::getInstanceByObjId($mail_data['sender_id'], false);

        $tplprint->setVariable('TXT_FROM', $this->lng->txt('from'));
        if ($sender instanceof ilObjUser && $sender->getId() !== 0 && !$sender->isAnonymous()) {
            $tplprint->setVariable('FROM', $sender->getPublicName());
        } elseif (!$sender instanceof ilObjUser || $sender->getId() === 0) {
            $tplprint->setVariable(
                'FROM',
                trim(($mail_data['import_name'] ?? '') . ' (' . $this->lng->txt('user_deleted') . ')')
            );
        } else {
            $tplprint->setVariable('FROM', ilMail::_getIliasMailerName());
        }

        $tplprint->setVariable('TXT_TO', $this->lng->txt('mail_to'));
        $tplprint->setVariable('TO', $mail_data['rcp_to']);

        if ($mail_data['rcp_cc']) {
            $tplprint->setCurrentBlock('cc');
            $tplprint->setVariable('TXT_CC', $this->lng->txt('mail_cc'));
            $tplprint->setVariable('CC', $mail_data['rcp_cc']);
            $tplprint->parseCurrentBlock();
        }

        if ($mail_data['rcp_bcc']) {
            $tplprint->setCurrentBlock('bcc');
            $tplprint->setVariable('TXT_BCC', $this->lng->txt('mail_bcc'));
            $tplprint->setVariable('BCC', $mail_data['rcp_bcc']);
            $tplprint->parseCurrentBlock();
        }

        $tplprint->setVariable('TXT_SUBJECT', $this->lng->txt('subject'));
        $tplprint->setVariable('SUBJECT', htmlspecialchars((string) $mail_data['m_subject']));

        $tplprint->setVariable('TXT_DATE', $this->lng->txt('date'));
        $tplprint->setVariable(
            'DATE',
            ilDatePresentation::formatDate(new ilDateTime($mail_data['send_time'], IL_CAL_DATETIME))
        );

        $tplprint->setVariable('TXT_MESSAGE', $this->lng->txt('message'));
        $tplprint->setVariable('MAIL_MESSAGE', html_entity_decode($this->refinery->string()->markdown()->toHTML()->transform($mail_data['m_message'])));

        $tplprint->show();
    }

    protected function deliverFile(): void
    {
        $mail_id = $this->http->wrapper()->query()->retrieve(
            self::PARAM_MAIL_ID,
            $this->refinery->byTrying([$this->refinery->kindlyTo()->int(), $this->refinery->always(0)])
        );
        if ($mail_id <= 0) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $filename = $this->http->wrapper()->post()->retrieve(
            'filename',
            $this->refinery->byTrying([$this->refinery->kindlyTo()->string(), $this->refinery->always('')])
        );

        if (is_string(ilSession::get('filename')) && ilSession::get('filename') !== '') {
            $filename = ilSession::get('filename');
            ilSession::set('filename', null);
        }

        try {
            if ($mail_id > 0 && $filename !== '') {
                while (str_contains((string) $filename, '..')) {
                    $filename = str_replace('..', '', $filename);
                }

                $mail_file_data = new ilFileDataMail($this->user->getId());
                try {
                    $file = $mail_file_data->getAttachmentPathAndFilenameByMd5Hash($filename, (int) $mail_id);
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
            $mail_id = $this->getMailIdsFromRequest()[0] ?? 0;
            $mail_data = $this->umail->getMail($mail_id);
            if ($mail_data === null || [] === (array) $mail_data['attachments']) {
                throw new ilMailException('mail_error_reading_attachment');
            }

            $type = $this->http->wrapper()->query()->retrieve(
                'type',
                $this->refinery->byTrying([$this->refinery->kindlyTo()->string(), $this->refinery->always('')])
            );

            $mail_file_data = new ilFileDataMail($this->user->getId());
            if (count($mail_data['attachments']) === 1) {
                $attachment = current($mail_data['attachments']);

                try {
                    if ($type === 'draft') {
                        if (!$mail_file_data->checkFilesExist([$attachment])) {
                            throw new OutOfBoundsException('');
                        }
                        $path_to_file = $mail_file_data->getAbsoluteAttachmentPoolPathByFilename($attachment);
                        $filename = $attachment;
                    } else {
                        $file = $mail_file_data->getAttachmentPathAndFilenameByMd5Hash(
                            md5((string) $attachment),
                            $mail_id
                        );
                        $path_to_file = $file['path'];
                        $filename = $file['filename'];
                    }
                    ilFileDelivery::deliverFileLegacy($path_to_file, $filename);
                } catch (OutOfBoundsException $e) {
                    throw new ilMailException('mail_error_reading_attachment', $e->getCode(), $e);
                }
            } else {
                $mail_file_data->deliverAttachmentsAsZip(
                    $mail_data['m_subject'],
                    $mail_id,
                    $mail_data['attachments'],
                    $type === 'draft'
                );
            }
        } catch (Exception $e) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt($e->getMessage()), true);
            $this->redirectToFolder();
        }
    }
}
