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

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilObjForumGUI
 * @author       Stefan Meyer <meyer@leifos.com>
 * @author       Nadia Matuschek <nmatuschek@databay.de>
 * @ilCtrl_Calls ilObjForumGUI: ilPermissionGUI, ilForumExportGUI, ilInfoScreenGUI
 * @ilCtrl_Calls ilObjForumGUI: ilColumnGUI, ilPublicUserProfileGUI, ilForumModeratorsGUI, ilRepositoryObjectSearchGUI
 * @ilCtrl_Calls ilObjForumGUI: ilObjectCopyGUI, ilExportGUI, ilCommonActionDispatcherGUI, ilRatingGUI
 * @ilCtrl_Calls ilObjForumGUI: ilForumSettingsGUI, ilContainerNewsSettingsGUI, ilLearningProgressGUI, ilForumPageGUI
 * @ilCtrl_Calls ilObjForumGUI: ilObjectContentStyleSettingsGUI
 * @ingroup      ModulesForum
 */
class ilObjForumGUI extends ilObjectGUI implements ilDesktopItemHandling, ilForumObjectConstants, ilCtrlSecurityInterface
{
    use ilForumRequestTrait;

    private array $viewModeOptions = [
        ilForumProperties::VIEW_TREE => 'sort_by_posts',
        ilForumProperties::VIEW_DATE_ASC => 'sort_by_date',
    ];

    private array $sortationOptions = [
        ilForumProperties::VIEW_DATE_ASC => 'ascending_order',
        ilForumProperties::VIEW_DATE_DESC => 'descending_order',
    ];

    private \ILIAS\GlobalScreen\Services $globalScreen;
    public string $modal_history = '';
    public ilForumProperties $objProperties;
    private ilForumTopic $objCurrentTopic;
    private ilForumPost $objCurrentPost;
    private bool $display_confirm_post_activation = false;
    private bool $is_moderator;
    private ?ilPropertyFormGUI $replyEditForm = null;
    private bool $hideToolbar = false;
    private $httpRequest;
    private \ILIAS\HTTP\Services $http;
    private Factory $uiFactory;
    private Renderer $uiRenderer;
    private ?array $forumObjects = null;
    private string $confirmation_gui_html = '';
    private ilForumSettingsGUI $forum_settings_gui;
    public ilNavigationHistory $ilNavigationHistory;
    private string $requestAction;
    private array $modalActionsContainer = [];

    public ilObjectDataCache $ilObjDataCache;
    public \ILIAS\DI\RBACServices $rbac;
    public ilHelpGUI $ilHelp;

    private int $selectedSorting;
    private ilForumThreadSettingsSessionStorage $selected_post_storage;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;
    protected \ILIAS\Style\Content\GUIService $content_style_gui;

    public function __construct($data, int $id = 0, bool $call_by_reference = true, bool $prepare_output = true)
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->ctrl->saveParameter($this, ['ref_id']);

        $this->httpRequest = $DIC->http()->request();
        $this->http = $DIC->http();

        $this->uiFactory = $DIC->ui()->factory();
        $this->uiRenderer = $DIC->ui()->renderer();
        $this->globalScreen = $DIC->globalScreen();

        $this->ilObjDataCache = $DIC['ilObjDataCache'];
        $this->ilNavigationHistory = $DIC['ilNavigationHistory'];
        $this->ilHelp = $DIC['ilHelp'];
        $this->rbac = $DIC->rbac();

        $this->type = 'frm';
        parent::__construct($data, $id, $call_by_reference, false);

        $this->tpl->addJavaScript('./Services/JavaScript/js/Basic.js');

        $this->lng->loadLanguageModule('forum');
        $this->lng->loadLanguageModule('content');

        $this->initSessionStorage();

        $ref_id = $this->retrieveRefId();
        $thr_pk = $this->retrieveThrPk();
        $pos_pk = $this->retrieveIntOrZeroFrom($this->http->wrapper()->query(), 'pos_pk');

        $this->objProperties = ilForumProperties::getInstance($this->ilObjDataCache->lookupObjId($ref_id));
        $this->is_moderator = $this->access->checkAccess('moderate_frm', '', $ref_id);

        $this->objCurrentTopic = new ilForumTopic($thr_pk, $this->is_moderator);
        $this->checkUsersViewMode();
        if ($this->selectedSorting === ilForumProperties::VIEW_TREE && ($this->selected_post_storage->get($thr_pk) > 0)) {
            $this->objCurrentPost = new ilForumPost(
                $this->selected_post_storage->get($thr_pk) ?? 0,
                $this->is_moderator
            );
            $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
        } else {
            $this->selected_post_storage->set($this->objCurrentTopic->getId(), 0);
            $this->objCurrentPost = new ilForumPost(
                $pos_pk,
                $this->is_moderator
            );
        }

        $this->requestAction = (string) ($this->httpRequest->getQueryParams()['action'] ?? '');
        $cs = $DIC->contentStyle();
        $this->content_style_gui = $cs->gui();
        if (is_object($this->object)) {
            $this->content_style_domain = $cs->domain()->styleForRefId($this->object->getRefId());
        }
    }

    protected function initSessionStorage(): void
    {
        $forumValues = ilSession::get('frm');
        if (!is_array($forumValues)) {
            $forumValues = [];
            ilSession::set('frm', $forumValues);
        }

        $threadId = $this->httpRequest->getQueryParams()['thr_pk'] ?? 0;
        if ((int) $threadId > 0 && !isset($forumValues[(int) $threadId])) {
            $forumValues[(int) $threadId] = [];
            ilSession::set('frm', $forumValues);
        }

        $this->selected_post_storage = new ilForumThreadSettingsSessionStorage('frm_selected_post');
    }

    private function retrieveRefId(): int
    {
        return $this->retrieveIntOrZeroFrom($this->http->wrapper()->query(), 'ref_id');
    }

    private function retrieveThrPk(): int
    {
        return $this->retrieveIntOrZeroFrom($this->http->wrapper()->query(), 'thr_pk');
    }

    private function retrieveThreadIds(): array
    {
        $thread_ids = [];
        if ($this->http->wrapper()->post()->has('thread_ids')) {
            $thread_ids = $this->http->wrapper()->post()->retrieve(
                'thread_ids',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }

        return $thread_ids;
    }

    private function retrieveDraftId(): int
    {
        return $this->retrieveIntOrZeroFrom($this->http->wrapper()->query(), 'draft_id');
    }

    protected function toggleExplorerNodeStateObject(): void
    {
        $exp = new ilForumExplorerGUI(
            'frm_exp_' . $this->objCurrentTopic->getId(),
            $this,
            'viewThread',
            $this->objCurrentTopic,
            $this->objCurrentTopic->getPostRootNode($this->is_moderator)
        );
        $exp->toggleExplorerNodeState();
    }

    protected function ensureValidPageForCurrentPosting(
        array $subtree_nodes,
        array $pagedPostings,
        int $pageSize,
        ilForumPost $firstForumPost
    ): void {
        if ($firstForumPost->getId() === $this->objCurrentPost->getId()) {
            return;
        }

        if ($subtree_nodes !== [] && $this->objCurrentPost->getId() > 0) {
            $isCurrentPostingInPage = array_filter($pagedPostings, function (ilForumPost $posting): bool {
                return $posting->getId() === $this->objCurrentPost->getId();
            });

            if ([] === $isCurrentPostingInPage) {
                $pageOfCurrentPosting = 0;
                $i = 0;
                foreach ($subtree_nodes as $node) {
                    if ($i > 0 && 0 === $i % $pageSize) {
                        ++$pageOfCurrentPosting;
                    }

                    if ($node->getId() === $this->objCurrentPost->getId()) {
                        break;
                    }

                    ++$i;
                }

                $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
                $this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
                $this->ctrl->setParameter($this, 'page', $pageOfCurrentPosting);
                $this->ctrl->setParameter(
                    $this,
                    'orderby',
                    $this->getOrderByParam()
                );
                $this->ctrl->redirect($this, 'viewThread', (string) $this->objCurrentPost->getId());
            }
        }
    }

    public function ensureThreadBelongsToForum(int $objId, ilForumTopic $thread): void
    {
        $forumId = ilObjForum::lookupForumIdByObjId($objId);
        if ($thread->getForumId() !== $forumId) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }
    }

    private function decorateWithAutosave(ilPropertyFormGUI $form): void
    {
        $draft_id = $this->retrieveDraftId();

        if (ilForumPostDraft::isAutoSavePostDraftAllowed()) {
            $interval = ilForumPostDraft::lookupAutosaveInterval();

            $this->tpl->addJavaScript('./Modules/Forum/js/autosave.js');
            $autosave_cmd = 'autosaveDraftAsync';
            if ($this->objCurrentPost->getId() === 0 && $this->objCurrentPost->getThreadId() === 0) {
                $autosave_cmd = 'autosaveThreadDraftAsync';
            }
            $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
            $this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
            $draft_id = max($draft_id, 0);
            $this->ctrl->setParameter($this, 'draft_id', $draft_id);
            $this->ctrl->setParameter($this, 'action', ilUtil::stripSlashes($this->requestAction));
            $this->tpl->addOnLoadCode(
                "il.Language.setLangVar('saving', " . json_encode($this->lng->txt('saving'), JSON_THROW_ON_ERROR) . ");"
            );

            $this->tpl->addOnLoadCode('il.ForumDraftsAutosave.init(' . json_encode([
                'loading_img_src' => ilUtil::getImagePath('loader.svg'),
                'draft_id' => $draft_id,
                'interval' => $interval * 1000,
                'url' => $this->ctrl->getFormAction($this, $autosave_cmd, '', true),
                'selectors' => [
                    'form' => '#form_' . $form->getId()
                ]
            ], JSON_THROW_ON_ERROR) . ');');
        }
    }

    private function isTopLevelReplyCommand(): bool
    {
        return in_array(
            strtolower($this->ctrl->getCmd()),
            array_map('strtolower', ['createTopLevelPost', 'saveTopLevelPost', 'saveTopLevelDraft']),
            true
        );
    }

    public function getUnsafeGetCommands(): array
    {
        return [
            'enableForumNotification',
            'disableForumNotification',
            'toggleThreadNotification'
        ];
    }

    public function getSafePostCommands(): array
    {
        return [];
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $exclude_cmds = [
            'viewThread',
            'markPostUnread',
            'markPostRead',
            'showThreadNotification',
            'performPostActivation',
            'askForPostActivation',
            'askForPostDeactivation',
            'toggleThreadNotification',
            'toggleThreadNotificationTab',
            'toggleStickiness',
            'cancelPost',
            'savePost',
            'saveTopLevelPost',
            'createTopLevelPost',
            'saveTopLevelDraft',
            'quotePost',
            'getQuotationHTMLAsynch',
            'autosaveDraftAsync',
            'autosaveThreadDraftAsync',
            'saveAsDraft',
            'editDraft',
            'updateDraft',
            'deliverDraftZipFile',
            'deliverZipFile',
            'cancelDraft',
            'deleteThreadDrafts',
            'deletePosting',
            'deletePostingDraft',
            'revokeCensorship',
            'addCensorship',
        ];

        if (!in_array($cmd, $exclude_cmds, true)) {
            $this->prepareOutput();
        }

        $ref_id = $this->retrieveRefId();

        if (!$this->getCreationMode() && !$this->ctrl->isAsynch() && $this->access->checkAccess(
            'read',
            '',
            $ref_id
        )) {
            $this->ilNavigationHistory->addItem(
                $ref_id,
                ilLink::_getLink($ref_id, 'frm'),
                'frm'
            );
        }

        switch (strtolower($next_class)) {
            case strtolower(ilForumPageGUI::class):
                if (in_array(strtolower($cmd), array_map('strtolower', [
                    self::UI_CMD_COPAGE_DOWNLOAD_FILE,
                    self::UI_CMD_COPAGE_DISPLAY_FULLSCREEN,
                    self::UI_CMD_COPAGE_DOWNLOAD_PARAGRAPH,
                ]), true)
                ) {
                    if (!$this->checkPermissionBool('read')) {
                        $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
                    }
                } elseif (!$this->checkPermissionBool('write') || $this->user->isAnonymous()) {
                    $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
                }

                $this->content_style_gui->addCss($this->tpl, $this->ref_id);
                $this->tpl->setCurrentBlock('SyntaxStyle');
                $this->tpl->setVariable('LOCATION_SYNTAX_STYLESHEET', ilObjStyleSheet::getSyntaxStylePath());
                $this->tpl->parseCurrentBlock();

                /** @var ilObjForum $obj */
                $obj = $this->object;

                $forwarder = new ilForumPageCommandForwarder(
                    $this->http,
                    $this->ctrl,
                    $this->tabs_gui,
                    $this->lng,
                    $obj,
                    $this->user,
                    $this->content_style_domain
                );

                $pageContent = $forwarder->forward();
                if ($pageContent !== '') {
                    $this->tpl->setContent($pageContent);
                }
                break;

            case strtolower(ilLearningProgressGUI::class):
                if (!ilLearningProgressAccess::checkAccess($this->object->getRefId())) {
                    $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
                }

                $this->tabs_gui->activateTab('learning_progress');

                $usrId = $this->user->getId();
                if (
                    isset($this->request->getQueryParams()['user_id']) &&
                    is_numeric($this->request->getQueryParams()['user_id'])
                ) {
                    $usrId = (int) $this->request->getQueryParams()['user_id'];
                }

                $this->ctrl->forwardCommand(new ilLearningProgressGUI(
                    ilLearningProgressBaseGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId(),
                    $usrId
                ));
                break;

            case strtolower(ilObjectContentStyleSettingsGUI::class):
                $forum_settings_gui = new ilForumSettingsGUI($this);
                $forum_settings_gui->settingsTabs();
                $settings_gui = $this->content_style_gui
                    ->objectSettingsGUIForRefId(
                        null,
                        $this->ref_id
                    );
                $this->ctrl->forwardCommand($settings_gui);
                break;

            case strtolower(ilForumSettingsGUI::class):
                $forum_settings_gui = new ilForumSettingsGUI($this);
                $this->ctrl->forwardCommand($forum_settings_gui);
                break;

            case strtolower(ilRepositoryObjectSearchGUI::class):
                $this->addHeaderAction();
                $this->setSideBlocks();
                $this->tabs_gui->activateTab("forums_threads");
                $this->ctrl->setReturn($this, 'view');
                $search_gui = new ilRepositoryObjectSearchGUI(
                    $this->object->getRefId(),
                    $this,
                    'view'
                );
                $this->ctrl->forwardCommand($search_gui);
                break;

            case strtolower(ilPermissionGUI::class):
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case strtolower(ilForumExportGUI::class):
                $fex_gui = new ilForumExportGUI();
                $this->ctrl->forwardCommand($fex_gui);
                $this->http->close();
                break;

            case strtolower(ilForumModeratorsGUI::class):
                $fm_gui = new ilForumModeratorsGUI();
                $this->ctrl->forwardCommand($fm_gui);
                break;

            case strtolower(ilInfoScreenGUI::class):
                $this->infoScreen();
                break;

            case strtolower(ilColumnGUI::class):
                $this->showThreadsObject();
                break;

            case strtolower(ilPublicUserProfileGUI::class):
                $user = $this->retrieveIntOrZeroFrom($this->http->wrapper()->query(), 'user');
                $profile_gui = new ilPublicUserProfileGUI($user);
                $add = $this->getUserProfileAdditional($ref_id, $user);
                $profile_gui->setAdditional($add);
                $ret = $this->ctrl->forwardCommand($profile_gui);
                $this->tpl->setContent($ret);
                break;

            case strtolower(ilObjectCopyGUI::class):
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('frm');
                $this->ctrl->forwardCommand($cp);
                break;

            case strtolower(ilExportGUI::class):
                $this->tabs_gui->activateTab('export');
                $exp = new ilExportGUI($this);
                $exp->addFormat('xml');
                $this->ctrl->forwardCommand($exp);
                break;

            case strtolower(ilRatingGUI::class):
                if (!$this->objProperties->isIsThreadRatingEnabled() || $this->user->isAnonymous()) {
                    $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
                }

                if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
                    $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
                }

                $this->ensureThreadBelongsToForum($this->object->getId(), $this->objCurrentTopic);

                $rating_gui = new ilRatingGUI();
                $rating_gui->setObject(
                    $this->object->getId(),
                    $this->object->getType(),
                    $this->objCurrentTopic->getId(),
                    'thread'
                );

                $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
                $this->ctrl->forwardCommand($rating_gui);

                $avg = ilRating::getOverallRatingForObject(
                    $this->object->getId(),
                    $this->object->getType(),
                    $this->objCurrentTopic->getId(),
                    'thread'
                );
                $this->objCurrentTopic->setAverageRating($avg['avg']);
                $this->objCurrentTopic->update();

                $this->ctrl->redirect($this, "showThreads");

                // no break
            case strtolower(ilCommonActionDispatcherGUI::class):
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            case strtolower(ilContainerNewsSettingsGUI::class):
                $forum_settings_gui = new ilForumSettingsGUI($this);
                $forum_settings_gui->settingsTabs();

                $this->lng->loadLanguageModule('cont');
                $news_set_gui = new ilContainerNewsSettingsGUI($this);
                $news_set_gui->setNewsBlockForced(true);
                $news_set_gui->setPublicNotification(true);
                $this->ctrl->forwardCommand($news_set_gui);
                break;

            default:
                if (in_array($cmd, $this->getTableCommands(), true)) {
                    $notificationCommands = [
                        'enableAdminForceNoti',
                        'disableAdminForceNoti',
                        'enableHideUserToggleNoti',
                        'disableHideUserToggleNoti'
                    ];

                    if (!in_array($cmd, $notificationCommands, true)) {
                        $cmd = 'performThreadsAction';
                    }
                } elseif (($cmd === null || $cmd === '') && $this->getTableCommands() === []) {
                    $cmd = 'showThreads';
                }

                $cmd .= 'Object';
                $this->$cmd();

                break;
        }

        if (
            $cmd !== 'viewThreadObject' && $cmd !== 'showUserObject' && !in_array(
                strtolower($next_class),
                array_map('strtolower', [ilForumPageGUI::class]),
                true
            )
        ) {
            $this->addHeaderAction();
        }
    }

    /**
     * @return string[]
     */
    private function getTableCommands(): array
    {
        $tableCommands = [];
        if ($this->http->wrapper()->post()->has('selected_cmd')) {
            $tableCommands[] = $this->http->wrapper()->post()->retrieve(
                'selected_cmd',
                $this->refinery->kindlyTo()->string()
            );
        }
        if ($this->http->wrapper()->post()->has('selected_cmd2')) {
            $tableCommands[] = $this->http->wrapper()->post()->retrieve(
                'selected_cmd2',
                $this->refinery->kindlyTo()->string()
            );
        }

        return $tableCommands;
    }

    public function infoScreenObject(): void
    {
        $this->ctrl->setCmd('showSummary');
        $this->ctrl->setCmdClass('ilinfoscreengui');
        $this->infoScreen();
    }

    protected function initEditCustomForm(ilPropertyFormGUI $a_form): void
    {
        $this->forum_settings_gui = new ilForumSettingsGUI($this);
        $this->forum_settings_gui->getCustomForm($a_form);
    }

    protected function getEditFormCustomValues(array &$a_values): void
    {
        $this->forum_settings_gui->getCustomValues($a_values);
    }

    protected function updateCustom(ilPropertyFormGUI $form): void
    {
        $this->forum_settings_gui->updateCustomValues($form);
    }

    private function getThreadEditingForm(int $a_thread_id): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $this->ctrl->setParameter($this, 'thr_pk', $a_thread_id);
        $form->setFormAction($this->ctrl->getFormAction($this, 'updateThread'));

        $ti_prop = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $ti_prop->setRequired(true);
        $ti_prop->setMaxLength(255);
        $ti_prop->setSize(50);
        $form->addItem($ti_prop);

        $form->addCommandButton('updateThread', $this->lng->txt('save'));
        $form->addCommandButton('showThreads', $this->lng->txt('cancel'));

        return $form;
    }

    public function editThreadObject(int $threadId, ilPropertyFormGUI $form = null): void
    {
        if (!$this->is_moderator) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $thread = new ilForumTopic($threadId);
        $this->ensureThreadBelongsToForum($this->object->getId(), $thread);

        $this->tabs_gui->activateTab('forums_threads');

        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getThreadEditingForm($threadId);
            $form->setValuesByArray([
                'title' => $thread->getSubject()
            ]);
        }

        $this->tpl->setContent($form->getHTML());
    }

    public function updateThreadObject(): void
    {
        if (!$this->is_moderator) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if ($this->objCurrentTopic->getId() === 0) {
            $this->showThreadsObject();
            return;
        }

        $this->ensureThreadBelongsToForum($this->object->getId(), $this->objCurrentTopic);

        $form = $this->getThreadEditingForm($this->objCurrentTopic->getId());
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->editThreadObject($this->objCurrentTopic->getId(), $form);
            return;
        }

        $this->objCurrentTopic->setSubject($form->getInput('title'));
        $this->objCurrentTopic->updateThreadTitle();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
        $this->showThreadsObject();
    }

    public function markAllReadObject(): void
    {
        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->object->markAllThreadsRead($this->user->getId());
        $this->tpl->setOnScreenMessage('info', $this->lng->txt('forums_all_threads_marked_read'));
        $this->showThreadsObject();
    }

    public function showThreadsObject(): void
    {
        $this->getSubTabs();
        $this->setSideBlocks();
        $this->getCenterColumnHTML();
    }

    public function sortThreadsObject(): void
    {
        $this->getSubTabs('sortThreads');
        $this->setSideBlocks();
        $this->getCenterColumnHTML();
    }

    public function getSubTabs($subtab = 'showThreads'): void
    {
        if ($this->is_moderator && $this->objProperties->getThreadSorting() === 1) {
            $this->tabs_gui->addSubTabTarget(
                'show',
                $this->ctrl->getLinkTarget($this, 'showThreads'),
                'showThreads',
                $this::class,
                '',
                $subtab === 'showThreads'
            );

            if ($this->object->getNumStickyThreads() > 1) {
                $this->tabs_gui->addSubTabTarget(
                    'sticky_threads_sorting',
                    $this->ctrl->getLinkTarget($this, 'sortThreads'),
                    'sortThreads',
                    $this::class,
                    '',
                    $subtab === 'sortThreads'
                );
            }
        }
    }

    public function getContent(): string
    {
        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $cmd = $this->ctrl->getCmd();
        $frm = $this->object->Forum;
        $frm->setForumId($this->object->getId());
        $frm->setForumRefId($this->object->getRefId());
        $frm->setMDB2Wherecondition('top_frm_fk = %s ', ['integer'], [$frm->getForumId()]);

        $threadsTemplate = new ilTemplate(
            'tpl.forums_threads_liste.html',
            true,
            true,
            'Modules/Forum'
        );

        if ($this->confirmation_gui_html !== '') {
            $threadsTemplate->setVariable('CONFIRMATION_GUI', $this->confirmation_gui_html);
        }

        // Create topic button
        if (!$this->hideToolbar() && $this->access->checkAccess('add_thread', '', $this->object->getRefId())) {
            $btn = ilLinkButton::getInstance();
            $btn->setUrl($this->ctrl->getLinkTarget($this, 'createThread'));
            $btn->setCaption('forums_new_thread');
            $this->toolbar->addStickyItem($btn);
        }

        // Mark all topics as read button
        if ($this->confirmation_gui_html === '' && !$this->user->isAnonymous()) {
            $this->toolbar->addButton(
                $this->lng->txt('forums_mark_read'),
                $this->ctrl->getLinkTarget($this, 'markAllRead')
            );
            $this->ctrl->clearParameters($this);
        }

        if (!$this->user->isAnonymous() && $this->access->checkAccess('write', '', $this->ref_id)) {
            $this->lng->loadLanguageModule('cntr');
            $this->toolbar->addComponent(
                $this->uiFactory->button()->standard(
                    $this->lng->txt('cntr_text_media_editor'),
                    $this->ctrl->getLinkTargetByClass(ilForumPageGUI::class, 'edit')
                )
            );
        }

        if (ilForumPostDraft::isSavePostDraftAllowed()) {
            $drafts = ilForumPostDraft::getThreadDraftData(
                $this->user->getId(),
                ilObjForum::lookupForumIdByObjId($this->object->getId())
            );
            if ($drafts !== []) {
                $draftsTable = new ilForumDraftsTableGUI(
                    $this,
                    $cmd,
                    $this->access->checkAccess('add_thread', '', $this->object->getRefId())
                );
                $draftsTable->setData($drafts);
                $threadsTemplate->setVariable('THREADS_DRAFTS_TABLE', $draftsTable->getHTML());
            }
        }

        // Import information: Topic (variable $topicData) means frm object, not thread
        $topicData = $frm->getOneTopic();
        if ($topicData->getTopPk() > 0) {
            $frm->setDbTable('frm_data');
            $frm->setMDB2WhereCondition('top_pk = %s ', ['integer'], [$topicData->getTopPk()]);
            $frm->updateVisits($topicData->getTopPk());

            ilChangeEvent::_recordReadEvent(
                $this->object->getType(),
                $this->object->getRefId(),
                $this->object->getId(),
                $this->user->getId()
            );

            if (!in_array($cmd, ['showThreads', 'sortThreads'])) {
                $cmd = 'showThreads';
            }

            $ref_id = $this->retrieveRefId();

            $tbl = new ilForumTopicTableGUI(
                $this,
                $cmd,
                $ref_id,
                $topicData,
                $this->is_moderator,
                (int) $this->settings->get('forum_overview', '0')
            );
            $tbl->init();
            $tbl->setMapper($frm)->fetchData();
            $threadsTemplate->setVariable('THREADS_TABLE', $tbl->getHTML());
        }

        $this->tpl->setPermanentLink($this->object->getType(), $this->object->getRefId(), '', '_top');

        $this->initStyleSheets();

        $forwarder = new ilForumPageCommandForwarder(
            $GLOBALS['DIC']['http'],
            $this->ctrl,
            $this->tabs_gui,
            $this->lng,
            $this->object,
            $this->user,
            $this->content_style_domain
        );
        $forwarder->setPresentationMode(ilForumPageCommandForwarder::PRESENTATION_MODE_PRESENTATION);

        $this->tpl->setContent($forwarder->forward() . $threadsTemplate->get());

        return '';
    }

    protected function initStyleSheets(): void
    {
        $this->content_style_gui->addCss($this->tpl, $this->ref_id);
        $this->tpl->setCurrentBlock('SyntaxStyle');
        $this->tpl->setVariable('LOCATION_SYNTAX_STYLESHEET', ilObjStyleSheet::getSyntaxStylePath());
        $this->tpl->parseCurrentBlock();
    }

    /**
     * @param ilForumPostDraft[] $drafts
     * @throws ilCtrlException
     * @throws ilTemplateException
     */
    protected function renderDraftContent(
        ilTemplate $tpl,
        string $action,
        ilForumPost $referencePosting,
        array $drafts
    ): void {
        $frm = $this->object->Forum;

        $ref_id = $this->retrieveRefId();
        $draft_id = $this->retrieveDraftId();

        foreach ($drafts as $draft) {
            $tmp_file_obj = new ilFileDataForumDrafts($this->object->getId(), $draft->getDraftId());
            $filesOfDraft = $tmp_file_obj->getFilesOfPost();
            ksort($filesOfDraft);

            if ($action !== 'showdraft' && $filesOfDraft !== []) {
                foreach ($filesOfDraft as $file) {
                    $tpl->setCurrentBlock('attachment_download_row');
                    $this->ctrl->setParameter($this, 'draft_id', $tmp_file_obj->getDraftId());
                    $this->ctrl->setParameter($this, 'file', $file['md5']);
                    $tpl->setVariable('HREF_DOWNLOAD', $this->ctrl->getLinkTarget($this, 'viewThread'));
                    $tpl->setVariable('TXT_FILENAME', $file['name']);
                    $this->ctrl->setParameter($this, 'file', '');
                    $this->ctrl->setParameter($this, 'draft_id', '');
                    $this->ctrl->clearParameters($this);
                    $tpl->parseCurrentBlock();
                }

                $tpl->setCurrentBlock('attachments');
                $tpl->setVariable('TXT_ATTACHMENTS_DOWNLOAD', $this->lng->txt('forums_attachments'));
                $tpl->setVariable(
                    'DOWNLOAD_IMG',
                    ilGlyphGUI::get(ilGlyphGUI::ATTACHMENT, $this->lng->txt('forums_download_attachment'))
                );
                if (count($filesOfDraft) > 1) {
                    $download_zip_button = ilLinkButton::getInstance();
                    $download_zip_button->setCaption($this->lng->txt('download'), false);
                    $this->ctrl->setParameter($this, 'draft_id', $draft->getDraftId());
                    $download_zip_button->setUrl($this->ctrl->getLinkTarget($this, 'deliverDraftZipFile'));
                    $this->ctrl->setParameter($this, 'draft_id', '');
                    $tpl->setVariable('DOWNLOAD_ZIP', $download_zip_button->render());
                }
                $tpl->parseCurrentBlock();
            }

            $page = 0;
            if ($this->http->wrapper()->query()->has('page')) {
                $page = $this->http->wrapper()->query()->retrieve(
                    'page',
                    $this->refinery->kindlyTo()->int()
                );
            }
            $this->renderSplitButton(
                $tpl,
                $action,
                false,
                $referencePosting,
                (int) $page,
                $draft
            );

            $rowCol = 'tblrowmarked';
            $tpl->setVariable('ROWCOL', ' ' . $rowCol);
            $depth = $referencePosting->getDepth() - 1;
            if ($this->selectedSorting === ilForumProperties::VIEW_TREE) {
                ++$depth;
            }
            $tpl->setVariable('DEPTH', $depth);

            $this->ctrl->setParameter($this, 'pos_pk', $referencePosting->getId());
            $this->ctrl->setParameter($this, 'thr_pk', $referencePosting->getThreadId());
            $this->ctrl->setParameter($this, 'draft_id', $draft->getDraftId());

            $backurl = urlencode($this->ctrl->getLinkTarget($this, 'viewThread', (string) $referencePosting->getId()));

            $this->ctrl->setParameter($this, 'backurl', $backurl);
            $this->ctrl->setParameter($this, 'thr_pk', $referencePosting->getThreadId());
            $this->ctrl->setParameter($this, 'user', $draft->getPostDisplayUserId());

            $authorinfo = new ilForumAuthorInformation(
                $draft->getPostAuthorId(),
                $draft->getPostDisplayUserId(),
                $draft->getPostUserAlias(),
                '',
                [
                    'href' => $this->ctrl->getLinkTarget($this, 'showUser')
                ]
            );

            $this->ctrl->clearParameters($this);

            if ($authorinfo->hasSuffix()) {
                $tpl->setVariable('AUTHOR', $authorinfo->getSuffix());
                $tpl->setVariable('USR_NAME', $draft->getPostUserAlias());
            } else {
                $tpl->setVariable('AUTHOR', $authorinfo->getLinkedAuthorShortName());
                if ($authorinfo->getAuthorName(true) && !$this->objProperties->isAnonymized()) {
                    $tpl->setVariable('USR_NAME', $authorinfo->getAuthorName(true));
                }
            }
            $tpl->setVariable('DRAFT_ANCHOR', 'draft_' . $draft->getDraftId());

            $tpl->setVariable('USR_IMAGE', $authorinfo->getProfilePicture());
            $tpl->setVariable(
                'USR_ICON_ALT',
                ilLegacyFormElementsUtil::prepareFormOutput($authorinfo->getAuthorShortName())
            );
            if ($authorinfo->getAuthor()->getId() && ilForum::_isModerator(
                $ref_id,
                $draft->getPostAuthorId()
            )) {
                if ($authorinfo->getAuthor()->getGender() === 'f') {
                    $tpl->setVariable('ROLE', $this->lng->txt('frm_moderator_f'));
                } elseif ($authorinfo->getAuthor()->getGender() === 'm') {
                    $tpl->setVariable('ROLE', $this->lng->txt('frm_moderator_m'));
                } elseif ($authorinfo->getAuthor()->getGender() === 'n') {
                    $tpl->setVariable('ROLE', $this->lng->txt('frm_moderator_n'));
                }
            }

            if ($draft->getUpdateUserId() > 0) {
                $draft->setPostUpdate($draft->getPostUpdate());

                $this->ctrl->setParameter($this, 'backurl', $backurl);
                $this->ctrl->setParameter($this, 'thr_pk', $referencePosting->getThreadId());
                $this->ctrl->setParameter($this, 'user', $referencePosting->getUpdateUserId());
                $this->ctrl->setParameter($this, 'draft_id', $draft->getDraftId());

                $authorinfo = new ilForumAuthorInformation(
                    $draft->getPostAuthorId(),
                    // We assume the editor is the author here
                    $draft->getPostDisplayUserId(),
                    $draft->getPostUserAlias(),
                    '',
                    ['href' => $this->ctrl->getLinkTarget($this, 'showUser')]
                );

                $this->ctrl->clearParameters($this);

                $tpl->setVariable(
                    'POST_UPDATE_TXT',
                    $this->lng->txt('edited_on') . ': ' . $frm->convertDate($draft->getPostUpdate()) . ' - ' . strtolower($this->lng->txt('by'))
                );
                $tpl->setVariable('UPDATE_AUTHOR', $authorinfo->getLinkedAuthorShortName());
                if ($authorinfo->getAuthorName(true) && !$this->objProperties->isAnonymized() && !$authorinfo->hasSuffix()) {
                    $tpl->setVariable('UPDATE_USR_NAME', $authorinfo->getAuthorName(true));
                }
            }

            $draft->setPostMessage($frm->prepareText($draft->getPostMessage()));

            $tpl->setVariable('SUBJECT', $draft->getPostSubject());
            $tpl->setVariable('POST_DATE', $frm->convertDate($draft->getPostDate()));

            if (!$referencePosting->isCensored() || ($this->objCurrentPost->getId() === $referencePosting->getId() && $action === 'censor')) {
                $spanClass = '';
                if (ilForum::_isModerator($this->ref_id, $draft->getPostDisplayUserId())) {
                    $spanClass = 'moderator';
                }

                if ($draft->getPostMessage() === strip_tags($draft->getPostMessage())) {
                    // We can be sure, that there are not html tags
                    $draft->setPostMessage(nl2br($draft->getPostMessage()));
                }

                if ($spanClass !== "") {
                    $tpl->setVariable(
                        'POST',
                        "<span class=\"" . $spanClass . "\">" . ilRTE::_replaceMediaObjectImageSrc(
                            $draft->getPostMessage(),
                            1
                        ) . "</span>"
                    );
                } else {
                    $tpl->setVariable('POST', ilRTE::_replaceMediaObjectImageSrc($draft->getPostMessage(), 1));
                }
            }

            if ($action === 'editdraft' && $draft->getDraftId() === $draft_id) {
                $oEditReplyForm = $this->getReplyEditForm();

                if (!$this->objCurrentTopic->isClosed() && in_array($this->requestAction, ['showdraft', 'editdraft'])) {
                    $this->renderPostingForm($tpl, $frm, $referencePosting, $this->requestAction);
                }

                $tpl->setVariable('EDIT_DRAFT_ANCHOR', 'draft_edit_' . $draft->getDraftId());
                $tpl->setVariable('DRAFT_FORM', $oEditReplyForm->getHTML() . $this->modal_history);
            }

            $tpl->parseCurrentBlock();
        }
    }

    protected function renderPostContent(
        ilTemplate $tpl,
        ilForumPost $node,
        string $action,
        int $pageIndex,
        int $postIndex
    ): void {
        $forumObj = $this->object;
        $frm = $this->object->Forum;

        $fileDataOfForum = new ilFileDataForum($forumObj->getId(), $node->getId());

        $filesOfPost = $fileDataOfForum->getFilesOfPost();
        ksort($filesOfPost);
        if ($filesOfPost !== [] && ($action !== 'showedit' || $node->getId() !== $this->objCurrentPost->getId())) {
            foreach ($filesOfPost as $file) {
                $tpl->setCurrentBlock('attachment_download_row');
                $this->ctrl->setParameter($this, 'pos_pk', $node->getId());
                $this->ctrl->setParameter($this, 'file', $file['md5']);
                $tpl->setVariable('HREF_DOWNLOAD', $this->ctrl->getLinkTarget($this, 'viewThread'));
                $tpl->setVariable('TXT_FILENAME', $file['name']);
                $this->ctrl->clearParameters($this);
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock('attachments');
            $tpl->setVariable('TXT_ATTACHMENTS_DOWNLOAD', $this->lng->txt('forums_attachments'));
            $tpl->setVariable(
                'DOWNLOAD_IMG',
                ilGlyphGUI::get(ilGlyphGUI::ATTACHMENT, $this->lng->txt('forums_download_attachment'))
            );
            if (count($filesOfPost) > 1) {
                $download_zip_button = ilLinkButton::getInstance();
                $download_zip_button->setCaption($this->lng->txt('download'), false);
                $this->ctrl->setParameter($this, 'pos_pk', $node->getId());
                $download_zip_button->setUrl($this->ctrl->getLinkTarget($this, 'deliverZipFile'));

                $tpl->setVariable('DOWNLOAD_ZIP', $download_zip_button->render());
            }
            $tpl->parseCurrentBlock();
        }
        $this->renderSplitButton($tpl, $action, true, $node, $pageIndex);

        $tpl->setVariable('POST_ANKER', $node->getId());
        $tpl->setVariable('TXT_PERMA_LINK', $this->lng->txt('perma_link'));
        $tpl->setVariable('PERMA_TARGET', '_top');

        $rowCol = ilUtil::switchColor($postIndex, 'tblrow1', 'tblrow2');
        if (($this->is_moderator || $node->isOwner($this->user->getId())) && !$node->isActivated() && !$this->objCurrentTopic->isClosed()) {
            $rowCol = 'ilPostingNeedsActivation';
        } elseif ($this->objProperties->getMarkModeratorPosts()) {
            $isAuthorModerator = ilForum::_isModerator($this->object->getRefId(), $node->getPosAuthorId());
            if ($isAuthorModerator && $node->isAuthorModerator() === null) {
                $rowCol = 'ilModeratorPosting';
            } elseif ($node->isAuthorModerator()) {
                $rowCol = 'ilModeratorPosting';
            }
        }

        if (
            (!in_array($action, ['delete', 'censor']) && !$this->displayConfirmPostActivation()) ||
            $this->objCurrentPost->getId() !== $node->getId()
        ) {
            $tpl->setVariable('ROWCOL', ' ' . $rowCol);
        } else {
            $rowCol = 'tblrowmarked';
        }

        if ($node->isCensored()) {
            if ($action !== 'censor') {
                $tpl->setVariable('TXT_CENSORSHIP_ADVICE', $this->lng->txt('post_censored_comment_by_moderator'));
            }

            $rowCol = 'tblrowmarked';
        }

        $tpl->setVariable('ROWCOL', ' ' . $rowCol);
        $tpl->setVariable('DEPTH', $node->getDepth() - 1);
        if (!$node->isActivated() && ($node->isOwner($this->user->getId()) || $this->is_moderator)) {
            $tpl->setVariable('POST_NOT_ACTIVATED_YET', $this->lng->txt('frm_post_not_activated_yet'));
        }

        $this->ctrl->setParameter($this, 'pos_pk', $node->getId());
        $this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
        $backurl = urlencode($this->ctrl->getLinkTarget($this, 'viewThread', (string) $node->getId()));
        $this->ctrl->clearParameters($this);

        $this->ctrl->setParameter($this, 'backurl', $backurl);
        $this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
        $this->ctrl->setParameter($this, 'user', $node->getDisplayUserId());
        $authorinfo = new ilForumAuthorInformation(
            $node->getPosAuthorId(),
            $node->getDisplayUserId(),
            (string) $node->getUserAlias(),
            (string) $node->getImportName(),
            [
                'href' => $this->ctrl->getLinkTarget($this, 'showUser')
            ]
        );
        $this->ctrl->clearParameters($this);

        if ($authorinfo->hasSuffix()) {
            if (!$authorinfo->isDeleted()) {
                $tpl->setVariable('USR_NAME', $authorinfo->getAlias());
            }
            $tpl->setVariable('AUTHOR', $authorinfo->getSuffix());
        } else {
            if ($authorinfo->getAuthorName(true) && !$this->objProperties->isAnonymized()) {
                $tpl->setVariable('USR_NAME', $authorinfo->getAuthorName(true));
            }
            $tpl->setVariable('AUTHOR', $authorinfo->getLinkedAuthorShortName());
        }

        $tpl->setVariable('USR_IMAGE', $authorinfo->getProfilePicture());
        $tpl->setVariable(
            'USR_ICON_ALT',
            ilLegacyFormElementsUtil::prepareFormOutput($authorinfo->getAuthorShortName())
        );
        $isModerator = ilForum::_isModerator($this->ref_id, $node->getPosAuthorId());
        if ($isModerator && $authorinfo->getAuthor()->getId()) {
            $authorRole = $this->lng->txt('frm_moderator_n');
            if (is_string($authorinfo->getAuthor()->getGender()) && $authorinfo->getAuthor()->getGender() !== '') {
                $authorRole = $this->lng->txt('frm_moderator_' . $authorinfo->getAuthor()->getGender());
            }
            $tpl->setVariable('ROLE', $authorRole);
        }

        if ($node->getUpdateUserId() > 0) {
            $node->setChangeDate($node->getChangeDate());

            $this->ctrl->setParameter($this, 'backurl', $backurl);
            $this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
            $this->ctrl->setParameter($this, 'user', $node->getUpdateUserId());
            $update_user_id = $node->getUpdateUserId();
            if ($node->getDisplayUserId() === 0 && $node->getPosAuthorId() === $node->getUpdateUserId()) {
                $update_user_id = $node->getDisplayUserId();
            }
            $authorinfo = new ilForumAuthorInformation(
                $node->getPosAuthorId(),
                $update_user_id,
                (string) $node->getUserAlias(),
                (string) $node->getImportName(),
                [
                    'href' => $this->ctrl->getLinkTarget($this, 'showUser')
                ]
            );
            $this->ctrl->clearParameters($this);

            $tpl->setVariable(
                'POST_UPDATE_TXT',
                $this->lng->txt('edited_on') . ': ' . $frm->convertDate($node->getChangeDate()) . ' - ' . strtolower($this->lng->txt('by'))
            );
            $tpl->setVariable('UPDATE_AUTHOR', $authorinfo->getLinkedAuthorShortName());
            if ($authorinfo->getAuthorName(true) && !$this->objProperties->isAnonymized() && !$authorinfo->hasSuffix()) {
                $tpl->setVariable('UPDATE_USR_NAME', $authorinfo->getAuthorName(true));
            }
        }

        if ($this->selectedSorting === ilForumProperties::VIEW_TREE
            && $node->getId() !== $this->selected_post_storage->get($node->getThreadId())) {
            $target = $this->uiFactory->symbol()->icon()->custom(
                ilUtil::getImagePath('target.svg'),
                $this->lng->txt('target_select')
            );

            $this->ctrl->setParameter($this, 'pos_pk', $node->getId());
            $this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());

            $tpl->setVariable(
                'TARGET',
                $this->uiRenderer->render(
                    $this->uiFactory->link()->bulky(
                        $target,
                        $this->lng->txt('select'),
                        new \ILIAS\Data\URI(
                            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTarget($this, 'selectPost', (string) $node->getId())
                        )
                    )
                )
            );
        }

        $node->setMessage($frm->prepareText($node->getMessage()));

        if ($this->user->isAnonymous() || $node->isPostRead()) {
            $tpl->setVariable('SUBJECT', $node->getSubject());
        } else {
            $this->ctrl->setParameter($this, 'pos_pk', $node->getId());
            $this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
            $this->ctrl->setParameter($this, 'page', $pageIndex);
            $this->ctrl->setParameter(
                $this,
                'orderby',
                $this->getOrderByParam()
            );
            $this->ctrl->setParameter($this, 'viewmode', $this->selectedSorting);
            $mark_post_target = $this->ctrl->getLinkTarget($this, 'markPostRead', (string) $node->getId());

            $tpl->setVariable(
                'SUBJECT',
                "<a href=\"" . $mark_post_target . "\"><b>" . $node->getSubject() . "</b></a>"
            );
        }

        $tpl->setVariable('POST_DATE', $frm->convertDate($node->getCreateDate()));

        if (!$node->isCensored() || ($this->objCurrentPost->getId() === $node->getId() && $action === 'censor')) {
            $spanClass = "";
            if (ilForum::_isModerator($this->ref_id, $node->getDisplayUserId())) {
                $spanClass = 'moderator';
            }

            // possible bugfix for mantis #8223
            if ($node->getMessage() === strip_tags($node->getMessage())) {
                // We can be sure, that there are not html tags
                $node->setMessage(nl2br($node->getMessage()));
            }

            if ($spanClass !== '') {
                $tpl->setVariable(
                    'POST',
                    "<span class=\"" . $spanClass . "\">" .
                    ilRTE::_replaceMediaObjectImageSrc($node->getMessage(), 1) .
                    "</span>"
                );
            } else {
                $tpl->setVariable('POST', ilRTE::_replaceMediaObjectImageSrc($node->getMessage(), 1));
            }
        } else {
            $tpl->setVariable('POST', "<span class=\"moderator\">" . nl2br($node->getCensorshipComment()) . "</span>");
        }

        $tpl->parseCurrentBlock();
    }

    protected function selectPostObject(): void
    {
        $thr_pk = (int) $this->httpRequest->getQueryParams()['thr_pk'];
        $pos_pk = (int) $this->httpRequest->getQueryParams()['pos_pk'];

        $this->selected_post_storage->set(
            $thr_pk,
            $pos_pk
        );

        $this->viewThreadObject();
    }

    /**
     * @param ilObject|ilObjForum $new_object
     */
    protected function afterSave(ilObject $new_object): void
    {
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('frm_added'), true);
        $this->ctrl->setParameter($this, 'ref_id', $new_object->getRefId());
        $this->ctrl->redirect($this, 'createThread');
    }

    protected function getTabs(): void
    {
        $this->ilHelp->setScreenIdComponent("frm");

        $this->ctrl->setParameter($this, 'ref_id', $this->ref_id);

        $active = [
            '',
            'showThreads',
            'view',
            'markAllRead',
            'enableForumNotification',
            'disableForumNotification',
            'moveThreads',
            'performMoveThreads',
            'cancelMoveThreads',
            'performThreadsAction',
            'createThread',
            'addThread',
            'showUser',
            'confirmDeleteThreads',
            'merge',
            'mergeThreads',
            'performMergeThreads'
        ];

        $force_active = false;
        if (in_array($this->ctrl->getCmd(), $active, true)) {
            $force_active = true;
        }

        if ($this->access->checkAccess(
            'read',
            '',
            $this->ref_id
        )) {
            $this->tabs_gui->addTarget(
                self::UI_TAB_ID_THREADS,
                $this->ctrl->getLinkTarget($this, 'showThreads'),
                $this->ctrl->getCmd(),
                $this::class,
                '',
                $force_active
            );
        }

        if ($this->access->checkAccess('visible', '', $this->ref_id) || $this->access->checkAccess(
            'read',
            '',
            $this->ref_id
        )) {
            $cmdClass = '';
            if ($this->http->wrapper()->query()->has('cmdClass')) {
                $cmdClass = $this->http->wrapper()->query()->retrieve(
                    'cmdClass',
                    $this->refinery->kindlyTo()->string()
                );
            }

            $force_active = $this->ctrl->getNextClass() === 'ilinfoscreengui' || strtolower($cmdClass) === 'ilnotegui';
            $this->tabs_gui->addTarget(
                self::UI_TAB_ID_INFO,
                $this->ctrl->getLinkTargetByClass([__CLASS__, ilInfoScreenGUI::class], 'showSummary'),
                ['showSummary', 'infoScreen'],
                '',
                '',
                $force_active
            );
        }

        if ($this->access->checkAccess('write', '', $this->ref_id)) {
            $force_active = $this->ctrl->getCmd() === 'edit';
            $this->tabs_gui->addTarget(
                self::UI_TAB_ID_SETTINGS,
                $this->ctrl->getLinkTarget($this, 'edit'),
                'edit',
                $this::class,
                '',
                $force_active
            );
        }

        if ($this->access->checkAccess('write', '', $this->ref_id)) {
            $this->tabs_gui->addTarget(
                self::UI_TAB_ID_MODERATORS,
                $this->ctrl->getLinkTargetByClass(ilForumModeratorsGUI::class, 'showModerators'),
                'showModerators',
                $this::class
            );
        }

        if (ilLearningProgressAccess::checkAccess($this->object->getRefId())) {
            $this->tabs_gui->addTab(
                'learning_progress',
                $this->lng->txt('learning_progress'),
                $this->ctrl->getLinkTargetByClass(ilLearningProgressGUI::class)
            );
        }

        if ($this->settings->get('enable_fora_statistics', '0')) {
            $hasStatisticsAccess = $this->access->checkAccess('write', '', $this->ref_id);
            if (!$hasStatisticsAccess) {
                $hasStatisticsAccess = (
                    $this->objProperties->isStatisticEnabled() &&
                    $this->access->checkAccess('read', '', $this->ref_id)
                );
            }

            if ($hasStatisticsAccess) {
                $force_active = $this->ctrl->getCmd() === 'showStatistics';
                $this->tabs_gui->addTarget(
                    self::UI_TAB_ID_STATS,
                    $this->ctrl->getLinkTarget($this, 'showStatistics'),
                    'showStatistics',
                    $this::class,
                    '',
                    $force_active
                );
            }
        }

        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                self::UI_TAB_ID_EXPORT,
                $this->ctrl->getLinkTargetByClass(ilExportGUI::class, ''),
                '',
                'ilexportgui'
            );
        }

        if ($this->access->checkAccess('edit_permission', '', $this->ref_id)) {
            $this->tabs_gui->addTarget(
                self::UI_TAB_ID_PERMISSIONS,
                $this->ctrl->getLinkTargetByClass([$this::class, ilPermissionGUI::class], 'perm'),
                ['perm', 'info', 'owner'],
                'ilpermissiongui'
            );
        }
    }

    public function showStatisticsObject(): void
    {
        if (!$this->settings->get('enable_fora_statistics', '0')) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!$this->objProperties->isStatisticEnabled()) {
            if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('frm_statistics_disabled_for_participants'));
            } else {
                $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
            }
        }

        $this->object->Forum->setForumId($this->object->getId());

        $tbl = new ilForumStatisticsTableGUI(
            $this,
            'showStatistics',
            $this->object,
            $this->user,
            ilLearningProgressAccess::checkAccess($this->object->getRefId()),
            $this->access->checkRbacOrPositionPermissionAccess(
                'read_learning_progress',
                'read_learning_progress',
                $this->object->getRefId()
            )
        );
        $tbl->setId('il_frm_statistic_table_' . $this->object->getRefId());
        $tbl->setTitle(
            $this->lng->txt('statistic'),
            'icon_usr.svg',
            $this->lng->txt('obj_' . $this->object->getType())
        );

        $data = $this->object->Forum->getUserStatistics($this->objProperties->isPostActivationEnabled());
        $result = [];
        $counter = 0;
        foreach ($data as $row) {
            $result[$counter]['usr_id'] = $row['usr_id'];
            $result[$counter]['ranking'] = $row['num_postings'];
            $result[$counter]['login'] = $row['login'];
            $result[$counter]['lastname'] = $row['lastname'];
            $result[$counter]['firstname'] = $row['firstname'];

            ++$counter;
        }
        $tbl->setData($result);

        $this->tpl->setContent($tbl->getHTML());
    }

    public static function _goto($a_target, $a_thread = 0, $a_posting = 0): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        $ilErr = $DIC['ilErr'];

        $a_target = is_numeric($a_target) ? (int) $a_target : 0;
        $a_thread = is_numeric($a_thread) ? (int) $a_thread : 0;
        if ($ilAccess->checkAccess('read', '', $a_target)) {
            if ($a_thread !== 0) {
                $objTopic = new ilForumTopic($a_thread);
                if ($objTopic->getFrmObjId() &&
                    $objTopic->getFrmObjId() !== ilObject::_lookupObjectId($a_target)) {
                    $ref_ids = ilObject::_getAllReferences($objTopic->getFrmObjId());
                    foreach ($ref_ids as $ref_id) {
                        if ($ilAccess->checkAccess('read', '', $ref_id)) {
                            $new_ref_id = $ref_id;
                            break;
                        }
                    }

                    if (isset($new_ref_id) && $new_ref_id !== $a_target) {
                        $DIC->ctrl()->redirectToURL(
                            ILIAS_HTTP_PATH . '/goto.php?target=frm_' . $new_ref_id . '_' . $a_thread . '_' . $a_posting
                        );
                    }
                }

                $DIC->ctrl()->setParameterByClass(__CLASS__, 'ref_id', (string) ((int) $a_target));
                if (is_numeric($a_thread)) {
                    $DIC->ctrl()->setParameterByClass(__CLASS__, 'thr_pk', (string) ((int) $a_thread));
                }
                if (is_numeric($a_posting)) {
                    $DIC->ctrl()->setParameterByClass(__CLASS__, 'pos_pk', (string) ((int) $a_posting));
                }
                $DIC->ctrl()->redirectByClass(
                    [ilRepositoryGUI::class, self::class],
                    'viewThread',
                    is_numeric($a_posting) ? (string) ((int) $a_posting) : ''
                );
            } else {
                $DIC->ctrl()->setParameterByClass(self::class, 'ref_id', $a_target);
                $DIC->ctrl()->redirectByClass([ilRepositoryGUI::class, self::class,], '');
                $DIC->http()->close();
            }
        } elseif ($ilAccess->checkAccess('visible', '', $a_target)) {
            $DIC->ctrl()->setParameterByClass(ilInfoScreenGUI::class, 'ref_id', $a_target);
            $DIC->ctrl()->redirectByClass(
                [
                    ilRepositoryGUI::class,
                    self::class,
                    ilInfoScreenGUI::class
                ],
                'showSummary'
            );
        } elseif ($ilAccess->checkAccess('read', '', ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('info', sprintf(
                $lng->txt('msg_no_perm_read_item'),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            $DIC->http()->close();
        }

        $ilErr->raiseError($lng->txt('msg_no_perm_read'), $ilErr->FATAL);
    }

    public function performDeleteThreadsObject(): void
    {
        $threadIds = $this->retrieveThreadIds();
        if ($threadIds === []) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_at_least_one_thread'), true);
            $this->ctrl->redirect($this, 'showThreads');
        }

        if (!$this->is_moderator) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $forumObj = new ilObjForum($this->object->getRefId());
        $this->objProperties->setObjId($forumObj->getId());

        $frm = new ilForum();

        $success_message = "forums_thread_deleted";
        if (count($threadIds) > 1) {
            $success_message = "forums_threads_deleted";
        }

        $threads = [];
        array_walk($threadIds, function (int $threadId) use (&$threads): void {
            $thread = new ilForumTopic($threadId);
            $this->ensureThreadBelongsToForum($this->object->getId(), $thread);

            $threads[] = $thread;
        });

        $frm->setForumId($forumObj->getId());
        $frm->setForumRefId($forumObj->getRefId());
        foreach ($threads as $thread) {
            $first_node = $frm->getFirstPostNode($thread->getId());
            if (isset($first_node['pos_pk']) && (int) $first_node['pos_pk']) {
                $frm->deletePost((int) $first_node['pos_pk']);
                $this->tpl->setOnScreenMessage('info', $this->lng->txt($success_message), true);
            }
        }
        $this->ctrl->redirect($this, 'showThreads');
    }

    public function confirmDeleteThreads(): void
    {
        $thread_ids = $this->retrieveThreadIds();
        if ($thread_ids === []) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_at_least_one_thread'));
            $this->ctrl->redirect($this, 'showThreads');
        }

        if (!$this->is_moderator) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        /** @var ilForumTopic[] $threads */
        $threads = [];
        array_walk($thread_ids, function (int $threadId) use (&$threads): void {
            $thread = new ilForumTopic($threadId);
            $this->ensureThreadBelongsToForum($this->object->getId(), $thread);

            $threads[] = $thread;
        });

        $c_gui = new ilConfirmationGUI();

        $c_gui->setFormAction($this->ctrl->getFormAction($this, 'performDeleteThreads'));
        $c_gui->setHeaderText($this->lng->txt('frm_sure_delete_threads'));
        $c_gui->setCancel($this->lng->txt('cancel'), 'showThreads');
        $c_gui->setConfirm($this->lng->txt('confirm'), 'performDeleteThreads');

        foreach ($threads as $thread) {
            $c_gui->addItem('thread_ids[]', (string) $thread->getId(), $thread->getSubject());
        }

        $this->confirmation_gui_html = $c_gui->getHTML();

        $this->hideToolbar(true);
        $this->tpl->setContent($c_gui->getHTML());
    }

    protected function confirmDeleteThreadDraftsObject(): void
    {
        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $draftIds = array_filter((array) ($this->httpRequest->getParsedBody()['draft_ids'] ?? []));
        if ($draftIds === []) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_at_least_one_thread'));
            $this->showThreadsObject();
            return;
        }

        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this, 'deleteThreadDrafts'));
        $confirmation->setHeaderText($this->lng->txt('sure_delete_drafts'));
        $confirmation->setCancel($this->lng->txt('cancel'), 'showThreads');
        $confirmation->setConfirm($this->lng->txt('confirm'), 'deleteThreadDrafts');
        $instances = ilForumPostDraft::getDraftInstancesByUserId($this->user->getId());
        foreach ($draftIds as $draftId) {
            if (array_key_exists($draftId, $instances)) {
                $confirmation->addItem('draft_ids[]', (string) $draftId, $instances[$draftId]->getPostSubject());
            }
        }

        $this->tpl->setContent($confirmation->getHTML());
    }

    public function prepareThreadScreen(ilObjForum $a_forum_obj): void
    {
        $this->ilHelp->setScreenIdComponent("frm");

        $this->tpl->loadStandardTemplate();

        $this->tpl->setTitleIcon(ilObject::_getIcon(0, "big", "frm"));

        $ref_id = $this->retrieveRefId();

        $this->tabs_gui->setBackTarget(
            $this->lng->txt('frm_all_threads'),
            'ilias.php?baseClass=ilRepositoryGUI&amp;ref_id=' . $ref_id
        );

        /** @var ilForum $frm */
        $frm = $a_forum_obj->Forum;
        $frm->setForumId($a_forum_obj->getId());
    }

    public function performPostActivationObject(): void
    {
        if (!$this->is_moderator) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->ensureThreadBelongsToForum($this->object->getId(), $this->objCurrentPost->getThread());

        $this->objCurrentPost->activatePost();
        $GLOBALS['ilAppEventHandler']->raise(
            'Modules/Forum',
            'activatedPost',
            [
                'object' => $this->object,
                'ref_id' => $this->object->getRefId(),
                'post' => $this->objCurrentPost
            ]
        );
        $this->tpl->setOnScreenMessage('info', $this->lng->txt('forums_post_was_activated'), true);

        $this->viewThreadObject();
    }

    private function deletePostingObject(): void
    {
        if (
            !$this->user->isAnonymous() &&
            !$this->objCurrentTopic->isClosed() && (
                $this->is_moderator ||
                ($this->objCurrentPost->isOwner($this->user->getId()) && !$this->objCurrentPost->hasReplies())
            )
        ) {
            $this->ensureThreadBelongsToForum($this->object->getId(), $this->objCurrentPost->getThread());

            $oForumObjects = $this->getForumObjects();
            $forumObj = $oForumObjects['forumObj'];

            $frm = new ilForum();
            $frm->setForumId($forumObj->getId());
            $frm->setForumRefId($forumObj->getRefId());
            $dead_thr = $frm->deletePost($this->objCurrentPost->getId());

            // if complete thread was deleted ...
            if ($dead_thr === $this->objCurrentTopic->getId()) {
                $frm->setMDB2WhereCondition('top_frm_fk = %s ', ['integer'], [$forumObj->getId()]);
                $topicData = $frm->getOneTopic();
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('forums_post_deleted'), true);
                if ($topicData->getTopNumThreads() > 0) {
                    $this->ctrl->redirect($this, 'showThreads');
                } else {
                    $this->ctrl->redirect($this, 'createThread');
                }
            }
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('forums_post_deleted'), true);
            $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
            $this->ctrl->redirect($this, 'viewThread');
        }

        $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
    }

    private function deletePostingDraftObject(): void
    {
        $this->deleteSelectedDraft();
    }

    private function revokeCensorshipObject(): void
    {
        $this->handleCensorship(true);
    }

    private function addCensorshipObject(): void
    {
        $this->handleCensorship();
    }

    private function getModalActions(): string
    {
        $modalString = '';
        foreach ($this->modalActionsContainer as $modal) {
            $modalString .= $this->uiRenderer->render($modal);
        }

        return $modalString;
    }

    private function handleCensorship(bool $wasRevoked = false): void
    {
        $message = '';
        if ($this->is_moderator && !$this->objCurrentTopic->isClosed()) {
            if ($this->http->wrapper()->post()->has('formData')) {
                $formData = $this->http->wrapper()->post()->retrieve(
                    'formData',
                    $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
                );
                $message = $this->handleFormInput($formData['cens_message']);
            }

            if ($message === '' && $this->http->wrapper()->post()->has('cens_message')) {
                $cens_message = $this->http->wrapper()->post()->retrieve(
                    'cens_message',
                    $this->refinery->kindlyTo()->string()
                );
                $message = $this->handleFormInput($cens_message);
            }
            $this->ensureThreadBelongsToForum($this->object->getId(), $this->objCurrentPost->getThread());

            $oForumObjects = $this->getForumObjects();
            $frm = $oForumObjects['frm'];

            if ($wasRevoked) {
                $frm->postCensorship($this->object, $message, $this->objCurrentPost->getId());
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('frm_censorship_revoked'));
            } else {
                $frm->postCensorship($this->object, $message, $this->objCurrentPost->getId(), 1);
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('frm_censorship_applied'));
            }

            $this->viewThreadObject();
            return;
        }

        $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
    }

    public function askForPostActivationObject(): void
    {
        if (!$this->is_moderator) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->setDisplayConfirmPostActivation(true);

        $this->viewThreadObject();
    }

    public function setDisplayConfirmPostActivation(bool $status = false): void
    {
        $this->display_confirm_post_activation = $status;
    }

    public function displayConfirmPostActivation(): bool
    {
        return $this->display_confirm_post_activation;
    }

    protected function toggleThreadNotificationObject(): void
    {
        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->ensureThreadBelongsToForum($this->object->getId(), $this->objCurrentTopic);

        if ($this->objCurrentTopic->isNotificationEnabled($this->user->getId())) {
            $this->objCurrentTopic->disableNotification($this->user->getId());
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('forums_notification_disabled'));
        } else {
            $this->objCurrentTopic->enableNotification($this->user->getId());
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('forums_notification_enabled'));
        }

        $this->viewThreadObject();
    }

    protected function toggleStickinessObject(): void
    {
        if (!$this->is_moderator) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->ensureThreadBelongsToForum($this->object->getId(), $this->objCurrentTopic);

        if ($this->objCurrentTopic->isSticky()) {
            $this->objCurrentTopic->unmakeSticky();
        } else {
            $this->objCurrentTopic->makeSticky();
        }

        $this->viewThreadObject();
    }

    public function cancelPostObject(): void
    {
        $draft_id = 0;
        if ($this->http->wrapper()->post()->has('draft_id')) {
            $draft_id = $this->http->wrapper()->post()->retrieve(
                'draft_id',
                $this->refinery->kindlyTo()->int()
            );
        }

        $this->requestAction = '';
        if ($draft_id > 0) {
            $draft = ilForumPostDraft::newInstanceByDraftId($draft_id);
            $draft->deleteDraftsByDraftIds([$draft_id]);
        }

        $this->viewThreadObject();
    }

    public function cancelDraftObject(): void
    {
        $draft_id = $this->retrieveDraftId();

        $this->requestAction = '';
        if ($draft_id > 0 && ilForumPostDraft::isAutoSavePostDraftAllowed()) {
            $history_obj = new ilForumDraftsHistory();
            $history_obj->getFirstAutosaveByDraftId($draft_id);
            $draft = ilForumPostDraft::newInstanceByDraftId($draft_id);
            $draft->setPostSubject($history_obj->getPostSubject());
            $draft->setPostMessage($history_obj->getPostMessage());

            ilForumUtil::moveMediaObjects(
                $history_obj->getPostMessage(),
                ilForumDraftsHistory::MEDIAOBJECT_TYPE,
                $history_obj->getHistoryId(),
                ilForumPostDraft::MEDIAOBJECT_TYPE,
                $draft->getDraftId()
            );

            $draft->updateDraft();

            $history_obj->deleteHistoryByDraftIds([$draft->getDraftId()]);
        }
        $this->ctrl->clearParameters($this);
        $this->viewThreadObject();
    }

    public function getActivationFormHTML(): string
    {
        $form_tpl = new ilTemplate('tpl.frm_activation_post_form.html', true, true, 'Modules/Forum');
        $this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
        $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
        $this->ctrl->setParameter(
            $this,
            'orderby',
            $this->getOrderByParam()
        );
        $form_tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this, 'performPostActivation'));
        $form_tpl->setVariable('SPACER', '<hr noshade="noshade" width="100%" size="1" align="center" />');
        $form_tpl->setVariable('ANCHOR', $this->objCurrentPost->getId());
        $form_tpl->setVariable('TXT_ACT', $this->lng->txt('activate_post_txt'));
        $form_tpl->setVariable('CONFIRM_BUTTON', $this->lng->txt('activate_only_current'));
        $form_tpl->setVariable('CMD_CONFIRM', 'performPostActivation');
        $form_tpl->setVariable('CANCEL_BUTTON', $this->lng->txt('cancel'));
        $form_tpl->setVariable('CMD_CANCEL', 'viewThread');
        $this->ctrl->clearParameters($this);

        return $form_tpl->get();
    }

    public function getCensorshipFormHTML(): string
    {
        $frm = $this->object->Forum;
        $form_tpl = new ilTemplate('tpl.frm_censorship_post_form.html', true, true, 'Modules/Forum');

        $form_tpl->setVariable('ANCHOR', $this->objCurrentPost->getId());
        $form_tpl->setVariable('SPACER', '<hr noshade="noshade" width="100%" size="1" align="center" />');
        $this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
        $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
        $this->ctrl->setParameter(
            $this,
            'orderby',
            $this->getOrderByParam()
        );
        $form_tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this, 'viewThread'));
        $this->ctrl->clearParameters($this);
        $form_tpl->setVariable('TXT_CENS_MESSAGE', $this->lng->txt('forums_the_post'));
        $form_tpl->setVariable('TXT_CENS_COMMENT', $this->lng->txt('forums_censor_comment') . ':');
        $form_tpl->setVariable('CENS_MESSAGE', $frm->prepareText($this->objCurrentPost->getCensorshipComment(), 2));

        if ($this->objCurrentPost->isCensored()) {
            $form_tpl->setVariable('TXT_CENS', $this->lng->txt('forums_info_censor2_post'));
            $form_tpl->setVariable('YES_BUTTON', $this->lng->txt('confirm'));
            $form_tpl->setVariable('NO_BUTTON', $this->lng->txt('cancel'));
            $form_tpl->setVariable('CMD_REVOKE_CENSORSHIP', 'revokeCensorship');
            $form_tpl->setVariable('CMD_CANCEL_REVOKE_CENSORSHIP', 'viewThread');
        } else {
            $form_tpl->setVariable('TXT_CENS', $this->lng->txt('forums_info_censor_post'));
            $form_tpl->setVariable('CANCEL_BUTTON', $this->lng->txt('cancel'));
            $form_tpl->setVariable('CONFIRM_BUTTON', $this->lng->txt('confirm'));
            $form_tpl->setVariable('CMD_ADD_CENSORSHIP', 'addCensorship');
            $form_tpl->setVariable('CMD_CANCEL_ADD_CENSORSHIP', 'viewThread');
        }

        return $form_tpl->get();
    }

    private function initReplyEditForm(): void
    {
        $isReply = in_array($this->requestAction, ['showreply', 'ready_showreply']);
        $isDraft = in_array($this->requestAction, ['publishDraft', 'editdraft']);

        $draft_id = $this->retrieveDraftId();

        // init objects
        $oForumObjects = $this->getForumObjects();
        $frm = $oForumObjects['frm'];
        $oFDForum = $oForumObjects['file_obj'];

        $this->replyEditForm = new ilPropertyFormGUI();
        $this->replyEditForm->setId('id_showreply');
        $this->replyEditForm->setTableWidth('100%');
        $cancel_cmd = 'cancelPost';
        if (in_array($this->requestAction, ['showreply', 'ready_showreply'])) {
            $this->ctrl->setParameter($this, 'action', 'ready_showreply');
        } elseif (in_array($this->requestAction, ['showdraft', 'editdraft'])) {
            $this->ctrl->setParameter($this, 'action', $this->requestAction);
            $this->ctrl->setParameter($this, 'draft_id', $draft_id);
        } else {
            $this->ctrl->setParameter($this, 'action', 'ready_showedit');
        }

        $this->ctrl->setParameter($this, 'page', (int) ($this->httpRequest->getQueryParams()['page'] ?? 0));
        $this->ctrl->setParameter(
            $this,
            'orderby',
            $this->getOrderByParam()
        );
        $this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
        $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
        if ($this->isTopLevelReplyCommand()) {
            $this->replyEditForm->setFormAction(
                $this->ctrl->getFormAction($this, 'saveTopLevelPost', 'frm_page_bottom')
            );
        } elseif (in_array($this->requestAction, ['publishDraft', 'editdraft'])) {
            $this->replyEditForm->setFormAction(
                $this->ctrl->getFormAction($this, 'publishDraft', (string) $this->objCurrentPost->getId())
            );
        } else {
            $this->replyEditForm->setFormAction(
                $this->ctrl->getFormAction($this, 'savePost', (string) $this->objCurrentPost->getId())
            );
        }
        $this->ctrl->clearParameters($this);

        if ($isReply) {
            $this->replyEditForm->setTitle($this->lng->txt('forums_your_reply'));
        } elseif ($isDraft) {
            $this->replyEditForm->setTitle($this->lng->txt('forums_edit_draft'));
        } else {
            $this->replyEditForm->setTitle($this->lng->txt('forums_edit_post'));
        }

        if (
            $this->isWritingWithPseudonymAllowed() &&
            in_array($this->requestAction, ['showreply', 'ready_showreply', 'editdraft'])
        ) {
            $oAnonymousNameGUI = new ilTextInputGUI($this->lng->txt('forums_your_name'), 'alias');
            $oAnonymousNameGUI->setMaxLength(64);
            $oAnonymousNameGUI->setInfo(sprintf($this->lng->txt('forums_use_alias'), $this->lng->txt('forums_anonymous')));

            $this->replyEditForm->addItem($oAnonymousNameGUI);
        }

        $oSubjectGUI = new ilTextInputGUI($this->lng->txt('forums_subject'), 'subject');
        $oSubjectGUI->setMaxLength(255);
        $oSubjectGUI->setRequired(true);

        if ($this->objProperties->getSubjectSetting() === 'empty_subject') {
            $oSubjectGUI->setInfo($this->lng->txt('enter_new_subject'));
        }

        $this->replyEditForm->addItem($oSubjectGUI);

        $oPostGUI = new ilTextAreaInputGUI(
            $isReply ? $this->lng->txt('forums_your_reply') : $this->lng->txt('forums_edit_post'),
            'message'
        );
        $oPostGUI->setRequired(true);
        $oPostGUI->setRows(15);
        $oPostGUI->setUseRte(true);
        $oPostGUI->addPlugin('latex');
        $oPostGUI->addButton('latex');
        $oPostGUI->addButton('pastelatex');

        $quotingAllowed = (
            !$this->isTopLevelReplyCommand() && (
                ($isReply && $this->objCurrentPost->getDepth() >= 2) ||
                (!$isDraft && !$isReply && $this->objCurrentPost->getDepth() > 2) ||
                ($isDraft && $this->objCurrentPost->getDepth() >= 2)
            )
        );
        if ($quotingAllowed) {
            $oPostGUI->addPlugin('ilfrmquote');
            $oPostGUI->addButton('ilFrmQuoteAjaxCall');
        }

        $oPostGUI->removePlugin('advlink');
        $oPostGUI->setRTERootBlockElement('');
        $oPostGUI->usePurifier(true);
        $oPostGUI->disableButtons([
            'charmap',
            'undo',
            'redo',
            'justifyleft',
            'justifycenter',
            'justifyright',
            'justifyfull',
            'anchor',
            'fullscreen',
            'cut',
            'copy',
            'paste',
            'pastetext',
            'formatselect'
        ]);

        if (in_array($this->requestAction, ['showreply', 'ready_showreply', 'showdraft', 'editdraft'])) {
            $oPostGUI->setRTESupport(
                $this->user->getId(),
                'frm~',
                'frm_post',
                'tpl.tinymce_frm_post.js',
                false,
                '5.6.0'
            );
        } else {
            $oPostGUI->setRTESupport(
                $this->objCurrentPost->getId(),
                'frm',
                'frm_post',
                'tpl.tinymce_frm_post.js',
                false,
                '5.6.0'
            );
        }

        $oPostGUI->setPurifier(ilHtmlPurifierFactory::getInstanceByType('frm_post'));

        $this->replyEditForm->addItem($oPostGUI);

        $umail = new ilMail($this->user->getId());
        if (
            !$this->objProperties->isAnonymized() &&
            $this->rbac->system()->checkAccess('internal_mail', $umail->getMailObjectReferenceId()) &&
            !$frm->isThreadNotificationEnabled($this->user->getId(), $this->objCurrentPost->getThreadId())
        ) {
            $oNotificationGUI = new ilCheckboxInputGUI($this->lng->txt('forum_direct_notification'), 'notify');
            $oNotificationGUI->setInfo($this->lng->txt('forum_notify_me'));

            $this->replyEditForm->addItem($oNotificationGUI);
        }

        if ($this->objProperties->isFileUploadAllowed()) {
            $oFileUploadGUI = new ilFileWizardInputGUI($this->lng->txt('forums_attachments_add'), 'userfile');
            $oFileUploadGUI->setSuffixes(['png', 'jpg']);
            $oFileUploadGUI->setFilenames([0 => '']);
            $this->replyEditForm->addItem($oFileUploadGUI);
        }

        $attachments_of_node = $oFDForum->getFilesOfPost();
        if (count($attachments_of_node) && in_array($this->requestAction, ['showedit', 'ready_showedit'])) {
            $oExistingAttachmentsGUI = new ilCheckboxGroupInputGUI($this->lng->txt('forums_delete_file'), 'del_file');
            foreach ($oFDForum->getFilesOfPost() as $file) {
                $oExistingAttachmentsGUI->addOption(new ilCheckboxOption($file['name'], $file['md5']));
            }
            $this->replyEditForm->addItem($oExistingAttachmentsGUI);
        }

        if (ilForumPostDraft::isAutoSavePostDraftAllowed()) {
            if (in_array($this->requestAction, ['showdraft', 'editdraft'])) {
                $draftInfoGUI = new ilNonEditableValueGUI('', 'autosave_info', true);
                $draftInfoGUI->setValue(sprintf(
                    $this->lng->txt('autosave_draft_info'),
                    ilForumPostDraft::lookupAutosaveInterval()
                ));
                $this->replyEditForm->addItem($draftInfoGUI);
            } elseif (!in_array($this->requestAction, ['showedit', 'ready_showedit'])) {
                $draftInfoGUI = new ilNonEditableValueGUI('', 'autosave_info', true);
                $draftInfoGUI->setValue(sprintf(
                    $this->lng->txt('autosave_post_draft_info'),
                    ilForumPostDraft::lookupAutosaveInterval()
                ));
                $this->replyEditForm->addItem($draftInfoGUI);
            }
        }

        $selected_draft_id = $draft_id;
        $draftObj = new ilForumPostDraft(
            $this->user->getId(),
            $this->objCurrentPost->getId(),
            $selected_draft_id
        );
        if ($draftObj->getDraftId() > 0) {
            $oFDForumDrafts = new ilFileDataForumDrafts(0, $draftObj->getDraftId());
            if ($oFDForumDrafts->getFilesOfPost() !== []) {
                $oExistingAttachmentsGUI = new ilCheckboxGroupInputGUI(
                    $this->lng->txt('forums_delete_file'),
                    'del_file'
                );
                foreach ($oFDForumDrafts->getFilesOfPost() as $file) {
                    $oExistingAttachmentsGUI->addOption(new ilCheckboxOption($file['name'], $file['md5']));
                }
                $this->replyEditForm->addItem($oExistingAttachmentsGUI);
            }
        }

        if ($this->isTopLevelReplyCommand()) {
            $this->replyEditForm->addCommandButton('saveTopLevelPost', $this->lng->txt('create'));
        } elseif ($this->requestAction === 'editdraft' && ilForumPostDraft::isSavePostDraftAllowed()) {
            $this->replyEditForm->addCommandButton('publishDraft', $this->lng->txt('publish'));
        } else {
            $this->replyEditForm->addCommandButton('savePost', $this->lng->txt('save'));
        }
        $hidden_draft_id = new ilHiddenInputGUI('draft_id');
        $auto_save_draft_id = $this->retrieveDraftId();

        $hidden_draft_id->setValue((string) $auto_save_draft_id);
        $this->replyEditForm->addItem($hidden_draft_id);

        if (in_array($this->requestAction, ['showreply', 'ready_showreply', 'editdraft'])) {
            $rtestring = ilRTE::_getRTEClassname();
            $show_rte = 0;
            if ($this->http->wrapper()->post()->has('show_rte')) {
                $show_rte = $this->http->wrapper()->post()->retrieve(
                    'show_rte',
                    $this->refinery->kindlyTo()->int()
                );
            }

            if ($show_rte) {
                ilObjAdvancedEditing::_setRichTextEditorUserState($show_rte);
            }

            if ((strtolower($rtestring) !== 'iltinymce' || !ilObjAdvancedEditing::_getRichTextEditorUserState()) &&
                $quotingAllowed) {
                $this->replyEditForm->addCommandButton('quotePost', $this->lng->txt('forum_add_quote'));
            }

            if (
                !$this->user->isAnonymous() &&
                in_array($this->requestAction, ['editdraft', 'showreply', 'ready_showreply']) &&
                ilForumPostDraft::isSavePostDraftAllowed()
            ) {
                if (ilForumPostDraft::isAutoSavePostDraftAllowed()) {
                    $this->decorateWithAutosave($this->replyEditForm);
                }

                if ($this->requestAction === 'editdraft') {
                    $this->replyEditForm->addCommandButton('updateDraft', $this->lng->txt('save_message'));
                } elseif ($this->isTopLevelReplyCommand()) {
                    $this->replyEditForm->addCommandButton('saveTopLevelDraft', $this->lng->txt('save_message'));
                } else {
                    $this->replyEditForm->addCommandButton('saveAsDraft', $this->lng->txt('save_message'));
                }

                $cancel_cmd = 'cancelDraft';
            }
        }
        $this->replyEditForm->addCommandButton($cancel_cmd, $this->lng->txt('cancel'));
    }

    private function getReplyEditForm(): ilPropertyFormGUI
    {
        if (null === $this->replyEditForm) {
            $this->initReplyEditForm();
        }

        return $this->replyEditForm;
    }

    public function createTopLevelPostObject(): void
    {
        $draft_obj = null;
        $draft_id = $this->retrieveDraftId();

        if ($draft_id > 0 && !$this->user->isAnonymous()
            && ilForumPostDraft::isSavePostDraftAllowed()) {
            $draft_obj = new ilForumPostDraft(
                $this->user->getId(),
                $this->objCurrentPost->getId(),
                $draft_id
            );
        }

        if ($draft_obj instanceof ilForumPostDraft && $draft_obj->getDraftId() > 0) {
            $this->ctrl->setParameter($this, 'action', 'editdraft');
            $this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
            $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
            $this->ctrl->setParameter($this, 'draft_id', $draft_obj->getDraftId());
            $this->ctrl->setParameter($this, 'page', 0);
            $this->ctrl->setParameter(
                $this,
                'orderby',
                $this->getOrderByParam()
            );
            $this->ctrl->redirect($this, 'editDraft');
        } else {
            $this->viewThreadObject();
        }
    }

    public function saveTopLevelPostObject(): void
    {
        $this->savePostObject();
    }

    public function publishSelectedDraftObject(): void
    {
        $draft_id = $this->retrieveDraftId();
        if ($draft_id > 0) {
            $this->publishDraftObject(false);
        }
    }

    public function publishDraftObject(bool $use_replyform = true): void
    {
        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!$this->access->checkAccess('add_reply', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if ($this->objCurrentTopic->getId() === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('frm_action_not_possible_thr_deleted'), true);
            $this->ctrl->redirect($this);
        }

        if ($this->objCurrentTopic->isClosed()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('frm_action_not_possible_thr_closed'), true);
            $this->ctrl->redirect($this);
        }

        if ($this->objCurrentPost->getId() === 0) {
            $this->requestAction = '';
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('frm_action_not_possible_parent_deleted'));
            $this->viewThreadObject();
            return;
        }

        $post_id = $this->objCurrentPost->getId();

        $draft_id = $this->retrieveDraftId();
        $draft_obj = new ilForumPostDraft($this->user->getId(), $post_id, $draft_id);

        if ($use_replyform) {
            $oReplyEditForm = $this->getReplyEditForm();
            if (!$oReplyEditForm->checkInput()) {
                $oReplyEditForm->setValuesByPost();
                $this->viewThreadObject();
                return;
            }
            $post_subject = $oReplyEditForm->getInput('subject');
            $post_message = $oReplyEditForm->getInput('message');
            $mob_direction = 0;
        } else {
            $post_subject = $draft_obj->getPostSubject();
            $post_message = $draft_obj->getPostMessage();
            $mob_direction = 1;
        }

        if ($draft_obj->getDraftId() > 0) {
            $oForumObjects = $this->getForumObjects();
            $frm = $oForumObjects['frm'];
            $frm->setMDB2WhereCondition(' top_frm_fk = %s ', ['integer'], [$frm->getForumId()]);

            // reply: new post
            $status = true;
            $send_activation_mail = false;

            if ($this->objProperties->isPostActivationEnabled()) {
                if (!$this->is_moderator) {
                    $status = false;
                    $send_activation_mail = true;
                } elseif ($this->objCurrentPost->isAnyParentDeactivated()) {
                    $status = false;
                }
            }

            $newPost = $frm->generatePost(
                $draft_obj->getForumId(),
                $draft_obj->getThreadId(),
                $this->user->getId(),
                $draft_obj->getPostDisplayUserId(),
                ilRTE::_replaceMediaObjectImageSrc($post_message, $mob_direction),
                $draft_obj->getPostId(),
                $draft_obj->isNotificationEnabled(),
                $this->handleFormInput($post_subject, false),
                $draft_obj->getPostUserAlias(),
                '',
                $status,
                $send_activation_mail
            );

            $this->object->markPostRead(
                $this->user->getId(),
                $this->objCurrentTopic->getId(),
                $this->objCurrentPost->getId()
            );

            $uploadedObjects = ilObjMediaObject::_getMobsOfObject('frm~:html', $this->user->getId());

            foreach ($uploadedObjects as $mob) {
                ilObjMediaObject::_removeUsage($mob, 'frm~:html', $this->user->getId());
                ilObjMediaObject::_saveUsage($mob, 'frm:html', $newPost);
            }
            ilForumUtil::saveMediaObjects($post_message, 'frm:html', $newPost, $mob_direction);

            if ($this->objProperties->isFileUploadAllowed()) {
                $file = $_FILES['userfile'] ?? [];
                if (is_array($file) && !empty($file)) {
                    $tmp_file_obj = new ilFileDataForum($this->object->getId(), $newPost);
                    $tmp_file_obj->storeUploadedFile($file);
                }

                //move files of draft to posts directory
                $oFDForum = new ilFileDataForum($this->object->getId(), $newPost);
                $oFDForumDrafts = new ilFileDataForumDrafts($this->object->getId(), $draft_obj->getDraftId());

                $oFDForumDrafts->moveFilesOfDraft($oFDForum->getForumPath(), $newPost);
                $oFDForumDrafts->delete();
            }

            if (ilForumPostDraft::isSavePostDraftAllowed()) {
                $GLOBALS['ilAppEventHandler']->raise(
                    'Modules/Forum',
                    'publishedDraft',
                    [
                        'draftObj' => $draft_obj,
                        'obj_id' => $this->object->getId(),
                        'is_file_upload_allowed' => $this->objProperties->isFileUploadAllowed()
                    ]
                );
            }
            $draft_obj->deleteDraft();

            $GLOBALS['ilAppEventHandler']->raise(
                'Modules/Forum',
                'createdPost',
                [
                    'object' => $this->object,
                    'ref_id' => $this->object->getRefId(),
                    'post' => new ilForumPost($newPost),
                    'notify_moderators' => $send_activation_mail
                ]
            );

            $message = '';
            if (!$this->is_moderator && !$status) {
                $message .= $this->lng->txt('forums_post_needs_to_be_activated');
            } else {
                $message .= $this->lng->txt('forums_post_new_entry');
            }

            $thr_pk = $this->retrieveThrPk();

            $frm_session_values = ilSession::get('frm');
            if (is_array($frm_session_values)) {
                $frm_session_values[$thr_pk]['openTreeNodes'][] = $this->objCurrentPost->getId();
            }
            ilSession::set('frm', $frm_session_values);

            $this->ctrl->clearParameters($this);
            $this->tpl->setOnScreenMessage('success', $message, true);
            $this->ctrl->setParameter($this, 'pos_pk', $newPost);
            $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());

            $this->ctrl->redirect($this, 'viewThread');
        }
    }

    public function savePostObject(): void
    {
        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if ($this->objCurrentTopic->getId() === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('frm_action_not_possible_thr_deleted'), true);
            $this->ctrl->redirect($this);
        }

        if ($this->objCurrentTopic->isClosed()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('frm_action_not_possible_thr_closed'), true);
            $this->ctrl->redirect($this);
        }

        $this->ensureThreadBelongsToForum($this->object->getId(), $this->objCurrentTopic);
        $del_file = [];
        if ($this->http->wrapper()->post()->has('del_file')) {
            $del_file = $this->http->wrapper()->post()->retrieve(
                'del_file',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
            );
        }

        $oReplyEditForm = $this->getReplyEditForm();
        if ($oReplyEditForm->checkInput()) {
            if ($this->objCurrentPost->getId() === 0) {
                $this->requestAction = '';
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('frm_action_not_possible_parent_deleted'), true);
                $this->viewThreadObject();
                return;
            }

            // init objects
            $oForumObjects = $this->getForumObjects();
            $forumObj = $oForumObjects['forumObj'];
            $frm = $oForumObjects['frm'];
            $frm->setMDB2WhereCondition(' top_frm_fk = %s ', ['integer'], [$frm->getForumId()]);
            $topicData = $frm->getOneTopic();

            $ref_id = $this->retrieveRefId();
            $post_draft_id = 0;
            if ($this->http->wrapper()->post()->has('draft_id')) {
                $post_draft_id = $this->http->wrapper()->post()->retrieve(
                    'draft_id',
                    $this->refinery->kindlyTo()->int()
                );
            }
            // Generating new posting
            if ($this->requestAction === 'ready_showreply') {
                if (!$this->access->checkAccess('add_reply', '', $ref_id)) {
                    $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
                }

                // reply: new post
                $status = true;
                $send_activation_mail = false;

                if ($this->objProperties->isPostActivationEnabled()) {
                    if (!$this->is_moderator) {
                        $status = false;
                        $send_activation_mail = true;
                    } elseif ($this->objCurrentPost->isAnyParentDeactivated()) {
                        $status = false;
                    }
                }

                if ($this->isWritingWithPseudonymAllowed()) {
                    if ((string) $oReplyEditForm->getInput('alias') === '') {
                        $user_alias = $this->lng->txt('forums_anonymous');
                    } else {
                        $user_alias = $oReplyEditForm->getInput('alias');
                    }
                    $display_user_id = 0;
                } else {
                    $user_alias = $this->user->getLogin();
                    $display_user_id = $this->user->getId();
                }

                $newPost = $frm->generatePost(
                    $topicData->getTopPk(),
                    $this->objCurrentTopic->getId(),
                    $this->user->getId(),
                    $display_user_id,
                    ilRTE::_replaceMediaObjectImageSrc($oReplyEditForm->getInput('message')),
                    $this->objCurrentPost->getId(),
                    (bool) $oReplyEditForm->getInput('notify'),
                    $this->handleFormInput($oReplyEditForm->getInput('subject'), false),
                    $user_alias,
                    '',
                    $status,
                    $send_activation_mail
                );

                if (ilForumPostDraft::isSavePostDraftAllowed()) {
                    $draft_id = 0;
                    if (ilForumPostDraft::isAutoSavePostDraftAllowed()) {
                        $draft_id = $post_draft_id; // info aus dem autosave?
                    }
                    $draft_obj = new ilForumPostDraft($this->user->getId(), $this->objCurrentPost->getId(), $draft_id);
                    $draft_obj->deleteDraft();
                }

                // mantis #8115: Mark parent as read
                $this->object->markPostRead(
                    $this->user->getId(),
                    $this->objCurrentTopic->getId(),
                    $this->objCurrentPost->getId()
                );

                // copy temporary media objects (frm~)
                ilForumUtil::moveMediaObjects(
                    $oReplyEditForm->getInput('message'),
                    'frm~:html',
                    $this->user->getId(),
                    'frm:html',
                    $newPost
                );

                if ($this->objProperties->isFileUploadAllowed()) {
                    $oFDForum = new ilFileDataForum($forumObj->getId(), $newPost);
                    $file = $_FILES['userfile'];
                    if (is_array($file) && !empty($file)) {
                        $oFDForum->storeUploadedFile($file);
                    }
                }

                $GLOBALS['ilAppEventHandler']->raise(
                    'Modules/Forum',
                    'createdPost',
                    [
                        'object' => $this->object,
                        'ref_id' => $this->object->getRefId(),
                        'post' => new ilForumPost($newPost),
                        'notify_moderators' => $send_activation_mail
                    ]
                );

                $message = '';
                if (!$this->is_moderator && !$status) {
                    $message .= $this->lng->txt('forums_post_needs_to_be_activated');
                } else {
                    $message .= $this->lng->txt('forums_post_new_entry');
                }

                $this->tpl->setOnScreenMessage('success', $message, true);
                $this->ctrl->clearParameters($this);
                $this->ctrl->setParameter($this, 'post_created_below', $this->objCurrentPost->getId());
                $this->ctrl->setParameter($this, 'pos_pk', $newPost);
                $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
            } else {
                if ((!$this->is_moderator &&
                        !$this->objCurrentPost->isOwner($this->user->getId())) || $this->objCurrentPost->isCensored() ||
                    $this->user->isAnonymous()) {
                    $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
                }

                $this->ensureThreadBelongsToForum($this->object->getId(), $this->objCurrentPost->getThread());

                $oldMediaObjects = ilObjMediaObject::_getMobsOfObject('frm:html', $this->objCurrentPost->getId());
                $curMediaObjects = ilRTE::_getMediaObjects($oReplyEditForm->getInput('message'));
                foreach ($oldMediaObjects as $oldMob) {
                    $found = false;
                    foreach ($curMediaObjects as $curMob) {
                        if ($oldMob === $curMob) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found && ilObjMediaObject::_exists($oldMob)) {
                        ilObjMediaObject::_removeUsage($oldMob, 'frm:html', $this->objCurrentPost->getId());
                        $mob_obj = new ilObjMediaObject($oldMob);
                        $mob_obj->delete();
                    }
                }

                // save old activation status for send_notification decision
                $old_status_was_active = $this->objCurrentPost->isActivated();

                // if active post has been edited posting mus be activated again by moderator
                $status = true;
                $send_activation_mail = false;

                if ($this->objProperties->isPostActivationEnabled()) {
                    if (!$this->is_moderator) {
                        $status = false;
                        $send_activation_mail = true;
                    } elseif ($this->objCurrentPost->isAnyParentDeactivated()) {
                        $status = false;
                    }
                }
                $this->objCurrentPost->setStatus($status);

                $this->objCurrentPost->setSubject($this->handleFormInput($oReplyEditForm->getInput('subject'), false));
                $this->objCurrentPost->setMessage(ilRTE::_replaceMediaObjectImageSrc(
                    $oReplyEditForm->getInput('message')
                ));
                $this->objCurrentPost->setNotification((bool) $oReplyEditForm->getInput('notify'));
                $this->objCurrentPost->setChangeDate(date('Y-m-d H:i:s'));
                $this->objCurrentPost->setUpdateUserId($this->user->getId());

                if ($this->objCurrentPost->update()) {
                    $this->objCurrentPost->reload();

                    // Change news item accordingly
                    // note: $this->objCurrentPost->getForumId() does not give us the forum ID here (why?)
                    $news_id = ilNewsItem::getFirstNewsIdForContext(
                        $forumObj->getId(),
                        'frm',
                        $this->objCurrentPost->getId(),
                        'pos'
                    );
                    if ($news_id > 0) {
                        $news_item = new ilNewsItem($news_id);
                        $news_item->setTitle($this->objCurrentPost->getSubject());
                        $news_item->setContent(
                            ilRTE::_replaceMediaObjectImageSrc($frm->prepareText(
                                $this->objCurrentPost->getMessage()
                            ), 1)
                        );

                        if ($this->objCurrentPost->getMessage() !== strip_tags($this->objCurrentPost->getMessage())) {
                            $news_item->setContentHtml(true);
                        } else {
                            $news_item->setContentHtml(false);
                        }
                        $news_item->update();
                    }

                    $oFDForum = $oForumObjects['file_obj'];

                    $file2delete = $oReplyEditForm->getInput('del_file');
                    if (is_array($file2delete) && count($file2delete)) {
                        $oFDForum->unlinkFilesByMD5Filenames($file2delete);
                    }

                    if ($this->objProperties->isFileUploadAllowed()) {
                        $file = $_FILES['userfile'];
                        if (is_array($file) && !empty($file)) {
                            $oFDForum->storeUploadedFile($file);
                        }
                    }

                    $GLOBALS['ilAppEventHandler']->raise(
                        'Modules/Forum',
                        'updatedPost',
                        [
                            'ref_id' => $this->object->getRefId(),
                            'post' => $this->objCurrentPost,
                            'notify_moderators' => $send_activation_mail,
                            'old_status_was_active' => $old_status_was_active
                        ]
                    );

                    $this->tpl->setOnScreenMessage('success', $this->lng->txt('forums_post_modified'), true);
                }

                $this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
                $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
                $this->ctrl->setParameter($this, 'viewmode', $this->selectedSorting);
            }
            $this->ctrl->redirect($this, 'viewThread');
        } else {
            $this->requestAction = substr($this->requestAction, 6);
        }
        $this->viewThreadObject();
    }

    private function hideToolbar($a_flag = null)
    {
        if (null === $a_flag) {
            return $this->hideToolbar;
        }

        $this->hideToolbar = $a_flag;
        return $this;
    }

    public function quotePostObject(): void
    {
        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $del_file = [];
        if ($this->http->wrapper()->post()->has('del_file')) {
            $del_file = $this->http->wrapper()->post()->retrieve(
                'del_file',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
            );
        }

        if ($this->objCurrentTopic->isClosed()) {
            $this->requestAction = '';
            $this->viewThreadObject();
            return;
        }

        $oReplyEditForm = $this->getReplyEditForm();

        $oReplyEditForm->getItemByPostVar('subject')->setRequired(false);
        $oReplyEditForm->getItemByPostVar('message')->setRequired(false);

        $oReplyEditForm->checkInput();

        $oReplyEditForm->getItemByPostVar('subject')->setRequired(true);
        $oReplyEditForm->getItemByPostVar('message')->setRequired(true);

        $this->requestAction = 'showreply';

        $this->viewThreadObject();
    }

    public function getQuotationHTMLAsynchObject(): void
    {
        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->ensureThreadBelongsToForum($this->object->getId(), $this->objCurrentPost->getThread());

        $oForumObjects = $this->getForumObjects();
        $frm = $oForumObjects['frm'];

        $authorinfo = new ilForumAuthorInformation(
            $this->objCurrentPost->getPosAuthorId(),
            $this->objCurrentPost->getDisplayUserId(),
            (string) $this->objCurrentPost->getUserAlias(),
            (string) $this->objCurrentPost->getImportName()
        );

        $html = ilRTE::_replaceMediaObjectImageSrc($frm->prepareText(
            $this->objCurrentPost->getMessage(),
            1,
            $authorinfo->getAuthorName()
        ), 1);

        $this->http->saveResponse($this->http->response()->withBody(
            \ILIAS\Filesystem\Stream\Streams::ofString($html)
        ));
        $this->http->sendResponse();
        $this->http->close();
    }

    /**
     * @return array{forumObj: ilObjForum, frm: ilForum, file_obj: ilFileDataForum}
     */
    private function getForumObjects(): array
    {
        if (null === $this->forumObjects) {
            $forumObj = $this->object;
            $file_obj = new ilFileDataForum($forumObj->getId(), $this->objCurrentPost->getId());
            $frm = $forumObj->Forum;
            $frm->setForumId($forumObj->getId());
            $frm->setForumRefId($forumObj->getRefId());

            $this->forumObjects['forumObj'] = $forumObj;
            $this->forumObjects['frm'] = $frm;
            $this->forumObjects['file_obj'] = $file_obj;
        }

        return $this->forumObjects;
    }

    public function checkUsersViewMode(): void
    {
        $this->selectedSorting = $this->objProperties->getDefaultView();

        if (in_array((int) ilSession::get('viewmode'), [
            ilForumProperties::VIEW_TREE,
            ilForumProperties::VIEW_DATE_ASC,
            ilForumProperties::VIEW_DATE_DESC
        ], true)) {
            $this->selectedSorting = ilSession::get('viewmode');
        }

        if (
            isset($this->httpRequest->getQueryParams()['viewmode']) &&
            (int) $this->httpRequest->getQueryParams()['viewmode'] !== $this->selectedSorting
        ) {
            $this->selectedSorting = (int) $this->httpRequest->getQueryParams()['viewmode'];
        }

        if (!in_array($this->selectedSorting, [
            ilForumProperties::VIEW_TREE,
            ilForumProperties::VIEW_DATE_ASC,
            ilForumProperties::VIEW_DATE_DESC
        ], true)) {
            $this->selectedSorting = $this->objProperties->getDefaultView();
        }

        ilSession::set('viewmode', $this->selectedSorting);
    }

    public function resetLimitedViewObject(): void
    {
        $this->selected_post_storage->set($this->objCurrentTopic->getId(), 0);
        $this->ctrl->redirect($this, 'viewThread');
    }

    public function viewThreadObject(): void
    {
        $ref_id = $this->retrieveRefId();
        $thr_pk = $this->retrieveThrPk();

        $bottom_toolbar = clone $this->toolbar;
        $bottom_toolbar_split_button_items = [];

        // quick and dirty: check for treeview
        $thread_control_session_values = ilSession::get('thread_control');
        if (is_array($thread_control_session_values)) {
            if (!isset($thread_control_session_values['old'])) {
                $thread_control_session_values['old'] = $thr_pk;
                $thread_control_session_values['new'] = $thr_pk;
                ilSession::set('thread_control', $thread_control_session_values);
            } elseif (isset($thread_control_session_values['old']) && $thr_pk !== $thread_control_session_values['old']) {
                $thread_control_session_values['new'] = $thr_pk;
                ilSession::set('thread_control', $thread_control_session_values);
            }
        }

        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $oForumObjects = $this->getForumObjects();
        $forumObj = $oForumObjects['forumObj'];
        $frm = $oForumObjects['frm'];
        $file_obj = $oForumObjects['file_obj'];

        $selected_draft_id = (int) ($this->httpRequest->getQueryParams()['draft_id'] ?? 0);
        if (isset($this->httpRequest->getQueryParams()['file'])) {
            $file_obj_for_delivery = $file_obj;
            if ($selected_draft_id > 0 && ilForumPostDraft::isSavePostDraftAllowed()) {
                $file_obj_for_delivery = new ilFileDataForumDrafts($forumObj->getId(), $selected_draft_id);
            }
            $file_obj_for_delivery->deliverFile(ilUtil::stripSlashes($this->httpRequest->getQueryParams()['file']));
        }

        $pageIndex = 0;
        if (isset($this->httpRequest->getQueryParams()['page'])) {
            $pageIndex = max((int) $this->httpRequest->getQueryParams()['page'], $pageIndex);
        }

        if ($this->selected_post_storage->get($this->objCurrentTopic->getId()) > 0) {
            $firstNodeInThread = new ilForumPost(
                $this->selected_post_storage->get($this->objCurrentTopic->getId()),
                $this->is_moderator,
                false
            );
        } else {
            $firstNodeInThread = $this->objCurrentTopic->getPostRootNode();
        }

        $toolContext = $this->globalScreen
            ->tool()
            ->context()
            ->current();

        $additionalDataExists = $toolContext->getAdditionalData()->exists(ForumGlobalScreenToolsProvider::SHOW_FORUM_THREADS_TOOL);
        if (!$additionalDataExists && $this->selectedSorting === ilForumProperties::VIEW_TREE) {
            $toolContext
                ->addAdditionalData(ForumGlobalScreenToolsProvider::SHOW_FORUM_THREADS_TOOL, true)
                ->addAdditionalData(ForumGlobalScreenToolsProvider::REF_ID, $this->ref_id)
                ->addAdditionalData(ForumGlobalScreenToolsProvider::FORUM_THEAD, $this->objCurrentTopic)
                ->addAdditionalData(ForumGlobalScreenToolsProvider::FORUM_THREAD_ROOT, $firstNodeInThread)
                ->addAdditionalData(ForumGlobalScreenToolsProvider::FORUM_BASE_CONTROLLER, $this)
                ->addAdditionalData(ForumGlobalScreenToolsProvider::PAGE, $pageIndex);
        }

        if ($this->objCurrentTopic->getId() === 0) {
            $this->ctrl->redirect($this, 'showThreads');
        }

        $this->ensureThreadBelongsToForum($this->object->getId(), $this->objCurrentTopic);

        // Set context for login
        $append = '_' . $this->objCurrentTopic->getId() .
            ($this->objCurrentPost->getId() !== 0 ? '_' . $this->objCurrentPost->getId() : '');
        $this->tpl->setLoginTargetPar('frm_' . $ref_id . $append);

        // delete temporary media object (not in case a user adds media objects and wants to save an invalid form)
        if (!in_array($this->requestAction, ['showreply', 'showedit'])) {
            try {
                $mobs = ilObjMediaObject::_getMobsOfObject('frm~:html', $this->user->getId());
                foreach ($mobs as $mob) {
                    if (ilObjMediaObject::_exists($mob)) {
                        ilObjMediaObject::_removeUsage($mob, 'frm~:html', $this->user->getId());
                        $mob_obj = new ilObjMediaObject($mob);
                        $mob_obj->delete();
                    }
                }
            } catch (Exception) {
            }
        }

        if (!$this->getCreationMode() && $this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->ilNavigationHistory->addItem(
                $this->object->getRefId(),
                ilLink::_getLink($this->object->getRefId(), 'frm'),
                'frm'
            );
        }

        $forumObj->updateLastAccess($this->user->getId(), $this->objCurrentTopic->getId());

        $this->prepareThreadScreen($forumObj);

        $threadContentTemplate = new ilTemplate(
            'tpl.forums_threads_view.html',
            true,
            true,
            'Modules/Forum'
        );

        if (isset($this->httpRequest->getQueryParams()['anchor'])) {
            $threadContentTemplate->setVariable('JUMP2ANCHOR_ID', (int) $this->httpRequest->getQueryParams()['anchor']);
        }
        if ($this->selectedSorting === ilForumProperties::VIEW_TREE) {
            $orderField = 'frm_posts_tree.rgt';
            $this->objCurrentTopic->setOrderDirection('DESC');
            $threadContentTemplate->setVariable('LIST_TYPE', $this->viewModeOptions[$this->selectedSorting]);
        } else {
            $orderField = 'frm_posts.pos_date';
            $this->objCurrentTopic->setOrderDirection(
                $this->selectedSorting === ilForumProperties::VIEW_DATE_DESC ? 'DESC' : 'ASC'
            );
            $threadContentTemplate->setVariable('LIST_TYPE', $this->sortationOptions[$this->selectedSorting]);
        }

        $numberOfPostings = 0;

        $frm->setMDB2WhereCondition('top_frm_fk = %s ', ['integer'], [$frm->getForumId()]);

        ilChangeEvent::_recordReadEvent(
            $this->object->getType(),
            $this->object->getRefId(),
            $this->object->getId(),
            $this->user->getId()
        );

        if ($firstNodeInThread) {
            $this->objCurrentTopic->updateVisits();

            $this->tpl->setTitle($this->lng->txt('forums_thread') . " \"" . $this->objCurrentTopic->getSubject() . "\"");

            $this->locator->addRepositoryItems();
            $this->locator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "_top");
            $this->tpl->setLocator();

            if (
                !$this->user->isAnonymous() &&
                $forumObj->getCountUnread($this->user->getId(), $this->objCurrentTopic->getId(), true)
            ) {
                $this->ctrl->setParameter($this, 'mark_read', '1');
                $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());

                $mark_thr_read_button = ilLinkButton::getInstance();
                $mark_thr_read_button->setCaption('forums_mark_read');
                $mark_thr_read_button->setUrl($this->ctrl->getLinkTarget($this, 'viewThread'));

                $bottom_toolbar_split_button_items[] = $mark_thr_read_button;

                $this->ctrl->clearParameters($this);
            }

            $this->ctrl->setParameterByClass(ilForumExportGUI::class, 'print_thread', $this->objCurrentTopic->getId());
            $this->ctrl->setParameterByClass(
                ilForumExportGUI::class,
                'thr_top_fk',
                $this->objCurrentTopic->getForumId()
            );

            $print_thr_button = ilLinkButton::getInstance();
            $print_thr_button->setCaption('forums_print_thread');
            $print_thr_button->setUrl($this->ctrl->getLinkTargetByClass(ilForumExportGUI::class, 'printThread'));

            $bottom_toolbar_split_button_items[] = $print_thr_button;

            $this->ctrl->clearParametersByClass(ilForumExportGUI::class);

            $this->addHeaderAction();

            if (isset($this->httpRequest->getQueryParams()['mark_read'])) {
                $forumObj->markThreadRead($this->user->getId(), $this->objCurrentTopic->getId());
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('forums_thread_marked'), true);
            }

            $this->objCurrentTopic->setOrderField($orderField);
            $subtree_nodes = $this->objCurrentTopic->getPostTree($firstNodeInThread);

            if (
                $firstNodeInThread instanceof ilForumPost &&
                !$this->isTopLevelReplyCommand() &&
                !$this->objCurrentTopic->isClosed() &&
                $this->access->checkAccess('add_reply', '', $ref_id)
            ) {
                $reply_button = ilLinkButton::getInstance();
                $reply_button->setPrimary(true);
                $reply_button->setCaption('add_new_answer');
                $this->ctrl->setParameter($this, 'action', 'showreply');
                $this->ctrl->setParameter($this, 'pos_pk', $firstNodeInThread->getId());
                $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
                $this->ctrl->setParameter(
                    $this,
                    'page',
                    (int) ($this->httpRequest->getQueryParams()['page'] ?? 0)
                );
                $this->ctrl->setParameter(
                    $this,
                    'orderby',
                    $this->getOrderByParam()
                );

                $reply_button->setUrl($this->ctrl->getLinkTarget($this, 'createTopLevelPost', 'frm_page_bottom'));

                $this->ctrl->clearParameters($this);
                array_unshift($bottom_toolbar_split_button_items, $reply_button);
            }

            // no posts
            if ($firstNodeInThread->getId() === 0 && !$numberOfPostings = count($subtree_nodes)) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('forums_no_posts_available'));
            }

            $pageSize = $frm->getPageHits();
            $postIndex = 0;
            if ($numberOfPostings > $pageSize) {
                $this->ctrl->setParameter($this, 'ref_id', $this->object->getRefId());
                $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
                $this->ctrl->setParameter(
                    $this,
                    'orderby',
                    $this->getOrderByParam()
                );
                $paginationUrl = $this->ctrl->getLinkTarget($this, 'viewThread', '');
                $this->ctrl->clearParameters($this);

                $pagination = $this->uiFactory->viewControl()
                                              ->pagination()
                                              ->withTargetURL($paginationUrl, 'page')
                                              ->withTotalEntries($numberOfPostings)
                                              ->withPageSize($pageSize)
                                              ->withMaxPaginationButtons(10)
                                              ->withCurrentPage($pageIndex);

                $threadContentTemplate->setVariable('THREAD_MENU', $this->uiRenderer->render(
                    $pagination
                ));
                $threadContentTemplate->setVariable('THREAD_MENU_BOTTOM', $this->uiRenderer->render(
                    $pagination
                ));
            }

            $doRenderDrafts = ilForumPostDraft::isSavePostDraftAllowed() && !$this->user->isAnonymous();
            $draftsObjects = [];
            if ($doRenderDrafts) {
                $draftsObjects = ilForumPostDraft::getSortedDrafts(
                    $this->user->getId(),
                    $this->objCurrentTopic->getId(),
                    $this->selectedSorting
                );
            }

            $pagedPostings = array_slice($subtree_nodes, $pageIndex * $pageSize, $pageSize);

            $this->ensureValidPageForCurrentPosting($subtree_nodes, $pagedPostings, $pageSize, $firstNodeInThread);

            if (
                $doRenderDrafts && 0 === $pageIndex &&
                $this->selectedSorting === ilForumProperties::VIEW_DATE_DESC
            ) {
                foreach ($draftsObjects as $draft) {
                    $referencePosting = array_values(array_filter(
                        $subtree_nodes,
                        static function (ilForumPost $post) use ($draft): bool {
                            return $draft->getPostId() === $post->getId();
                        }
                    ))[0] ?? $firstNodeInThread;

                    $this->renderDraftContent(
                        $threadContentTemplate,
                        $this->requestAction,
                        $referencePosting,
                        [$draft]
                    );
                }
            }

            foreach ($pagedPostings as $node) {
                $this->ctrl->clearParameters($this);

                if (!$this->isTopLevelReplyCommand() && $this->objCurrentPost->getId() === $node->getId() &&
                    ($this->is_moderator || $node->isActivated() || $node->isOwner($this->user->getId()))) {
                    if (!$this->objCurrentTopic->isClosed() && in_array($this->requestAction, [
                            'showreply',
                            'showedit',
                        ])) {
                        $this->renderPostingForm($threadContentTemplate, $frm, $node, $this->requestAction);
                    } elseif ($this->requestAction === 'censor' &&
                        !$this->objCurrentTopic->isClosed() && $this->is_moderator) {
                        $threadContentTemplate->setVariable('FORM', $this->getCensorshipFormHTML());
                    } elseif (!$this->objCurrentTopic->isClosed() &&
                        $this->displayConfirmPostActivation() && $this->is_moderator) {
                        $threadContentTemplate->setVariable('FORM', $this->getActivationFormHTML());
                    }
                }

                $this->renderPostContent($threadContentTemplate, $node, $this->requestAction, $pageIndex, $postIndex);
                if ($doRenderDrafts && $this->selectedSorting === ilForumProperties::VIEW_TREE) {
                    $this->renderDraftContent(
                        $threadContentTemplate,
                        $this->requestAction,
                        $node,
                        $draftsObjects[$node->getId()] ?? []
                    );
                }

                $postIndex++;
            }

            if (
                $this->selectedSorting === ilForumProperties::VIEW_DATE_ASC &&
                $doRenderDrafts && $pageIndex === max(0, (int) (ceil($numberOfPostings / $pageSize) - 1))
            ) {
                foreach ($draftsObjects as $draft) {
                    $referencePosting = array_values(array_filter(
                        $subtree_nodes,
                        static function (ilForumPost $post) use ($draft): bool {
                            return $draft->getPostId() === $post->getId();
                        }
                    ))[0] ?? $firstNodeInThread;

                    $this->renderDraftContent(
                        $threadContentTemplate,
                        $this->requestAction,
                        $referencePosting,
                        [$draft]
                    );
                }
            }

            if (
                $firstNodeInThread instanceof ilForumPost && $doRenderDrafts &&
                $this->selectedSorting === ilForumProperties::VIEW_TREE
            ) {
                $this->renderDraftContent(
                    $threadContentTemplate,
                    $this->requestAction,
                    $firstNodeInThread,
                    $draftsObjects[$firstNodeInThread->getId()] ?? []
                );
            }

            if (
                $firstNodeInThread instanceof ilForumPost &&
                !$this->objCurrentTopic->isClosed() &&
                in_array($this->ctrl->getCmd(), ['createTopLevelPost', 'saveTopLevelPost', 'saveTopLevelDraft'], true) &&
                $this->access->checkAccess('add_reply', '', $ref_id)) {
                // Important: Don't separate the following two lines (very fragile code ...)
                $this->objCurrentPost->setId($firstNodeInThread->getId());
                $form = $this->getReplyEditForm();

                if (in_array($this->ctrl->getCmd(), ['saveTopLevelPost', 'saveTopLevelDraft'])) {
                    $form->setValuesByPost();
                }
                $this->ctrl->setParameter($this, 'pos_pk', $firstNodeInThread->getId());
                $this->ctrl->setParameter($this, 'thr_pk', $firstNodeInThread->getThreadId());
                $jsTpl = new ilTemplate('tpl.forum_post_quoation_ajax_handler.js', true, true, 'Modules/Forum');
                $jsTpl->setVariable(
                    'IL_FRM_QUOTE_CALLBACK_SRC',
                    $this->ctrl->getLinkTarget($this, 'getQuotationHTMLAsynch', '', true)
                );
                $this->ctrl->clearParameters($this);
                $this->tpl->addOnLoadCode($jsTpl->get());
                $threadContentTemplate->setVariable('BOTTOM_FORM', $form->getHTML());
            }
        } else {
            $threadContentTemplate->setCurrentBlock('posts_no');
            $threadContentTemplate->setVariable(
                'TXT_MSG_NO_POSTS_AVAILABLE',
                $this->lng->txt('forums_posts_not_available')
            );
            $threadContentTemplate->parseCurrentBlock();
        }

        if ($bottom_toolbar_split_button_items !== []) {
            $bottom_split_button = ilSplitButtonGUI::getInstance();
            $i = 0;
            foreach ($bottom_toolbar_split_button_items as $item) {
                if ($i === 0) {
                    $bottom_split_button->setDefaultButton($item);
                } else {
                    $bottom_split_button->addMenuItem(new ilButtonToSplitButtonMenuItemAdapter($item));
                }

                ++$i;
            }
            $bottom_toolbar->addStickyItem($bottom_split_button);
            $this->toolbar->addStickyItem($bottom_split_button);
            $bottom_toolbar->addSeparator();
        }

        $to_top_button = ilLinkButton::getInstance();
        $to_top_button->setCaption('top_of_page');
        $to_top_button->setUrl('#frm_page_top');
        $bottom_toolbar->addButtonInstance($to_top_button);
        if ($numberOfPostings > 0) {
            $threadContentTemplate->setVariable('TOOLBAR_BOTTOM', $bottom_toolbar->getHTML());
        }

        $this->renderViewModeControl($this->selectedSorting);
        if ($this->selectedSorting !== ilForumProperties::VIEW_TREE) {
            $this->renderSortationControl($this->selectedSorting);
        }

        $this->tpl->setPermanentLink(
            $this->object->getType(),
            $this->object->getRefId(),
            '_' . $this->objCurrentTopic->getId(),
            '_top'
        );

        $this->tpl->addOnLoadCode('$(".ilFrmPostContent img").each(function() {
			var $elm = $(this);
			$elm.css({
				maxWidth: $elm.attr("width") + "px",
				maxHeight: $elm.attr("height")  + "px"
			});
			$elm.removeAttr("width");
			$elm.removeAttr("height");
		});');

        if ($this->selectedSorting === ilForumProperties::VIEW_TREE && ($this->selected_post_storage->get($thr_pk) > 0)) {
            $info = $this->getResetLimitedViewInfo();
        }

        $this->tpl->setContent(($info ?? '') . $threadContentTemplate->get() . $this->getModalActions());
    }

    private function renderViewModeControl(int $currentViewMode): void
    {
        if ($currentViewMode === 3) {
            $currentViewMode = 2;
        }
        $translationKeys = [];
        foreach ($this->viewModeOptions as $sortingConstantKey => $languageKey) {
            $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
            $this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
            $this->ctrl->setParameter($this, 'viewmode', $sortingConstantKey);

            $translationKeys[$this->lng->txt($languageKey)] = $this->ctrl->getLinkTarget(
                $this,
                'viewThread',
                ''
            );

            $this->ctrl->clearParameters($this);
        }

        if ($currentViewMode > ilForumProperties::VIEW_DATE_ASC) {
            $currentViewMode = ilForumProperties::VIEW_DATE_ASC;
        }

        $sortViewControl = $this->uiFactory
            ->viewControl()
            ->mode($translationKeys, $this->lng->txt($this->viewModeOptions[$currentViewMode]))
            ->withActive($this->lng->txt($this->viewModeOptions[$currentViewMode]));
        $this->toolbar->addComponent($sortViewControl);
    }

    private function renderSortationControl(int $currentSorting): void
    {
        $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
        $this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
        $target = $this->ctrl->getLinkTarget(
            $this,
            'viewThread',
            ''
        );

        $translatedSortationOptions = array_map(function ($value): string {
            return $this->lng->txt($value);
        }, $this->sortationOptions);

        $sortingDirectionViewControl = $this->uiFactory
            ->viewControl()
            ->sortation($translatedSortationOptions)
            ->withLabel($this->lng->txt($this->sortationOptions[$currentSorting]))
            ->withTargetURL($target, 'viewmode');
        $this->toolbar->addComponent($sortingDirectionViewControl);
    }

    private function getModifiedReOnSubject(bool $on_reply = false): string
    {
        $modified_subject = '';
        $subject = $this->objCurrentPost->getSubject();
        $re_txt = $this->lng->txt('post_reply');

        $re_txt_with_num = str_replace(':', '(', $re_txt);
        $search_length = strlen($re_txt_with_num);
        $comp = substr_compare($re_txt_with_num, substr($subject, 0, $search_length), 0, $search_length);

        if ($comp === 0) {
            $modified_subject = $subject;
            if ($on_reply) {
                // i.e. $subject = "Re(12):"
                $str_pos_start = strpos($subject, '(');
                $str_pos_end = strpos($subject, ')');

                $length = ((int) $str_pos_end - (int) $str_pos_start);
                $str_pos_start++;
                $txt_number = substr($subject, $str_pos_start, $length - 1);

                if (is_numeric($txt_number)) {
                    $re_count = (int) $txt_number + 1;
                    $modified_subject = substr($subject, 0, $str_pos_start) . $re_count . substr(
                        $subject,
                        $str_pos_end
                    );
                }
            }
        } else {
            $re_count = substr_count($subject, $re_txt);
            if ($re_count >= 1 && $on_reply) {
                $subject = str_replace($re_txt, '', $subject);

                // i.e. $subject = "Re: Re: Re: ... " -> "Re(4):"
                $re_count++;
                $modified_subject = sprintf($this->lng->txt('post_reply_count'), $re_count) . ' ' . trim($subject);
            } elseif ($re_count >= 1 && !$on_reply) {
                // possibility to modify the subject only for output
                // i.e. $subject = "Re: Re: Re: ... " -> "Re(3):"
                $modified_subject = sprintf($this->lng->txt('post_reply_count'), $re_count) . ' ' . trim($subject);
            } elseif ($re_count === 0) {
                // the first reply to a thread
                $modified_subject = $this->lng->txt('post_reply') . ' ' . $this->objCurrentPost->getSubject();
            }
        }
        return $modified_subject;
    }

    public function showUserObject(): void
    {
        $user_id = 0;
        if ($this->http->wrapper()->query()->has('user')) {
            $user_id = $this->http->wrapper()->query()->retrieve(
                'user',
                $this->refinery->kindlyTo()->int()
            );
        }

        $ref_id = $this->retrieveRefId();
        $backurl = '';
        if ($this->http->wrapper()->query()->has('backurl')) {
            $backurl = $this->http->wrapper()->query()->retrieve(
                'backurl',
                $this->refinery->kindlyTo()->string()
            );
        }

        $profile_gui = new ilPublicUserProfileGUI($user_id);
        $add = $this->getUserProfileAdditional($ref_id, $user_id);
        $profile_gui->setAdditional($add);
        $profile_gui->setBackUrl(ilUtil::stripSlashes($backurl));
        $this->tpl->setContent($this->ctrl->getHTML($profile_gui));
    }

    protected function getUserProfileAdditional(int $a_forum_ref_id, int $a_user_id): array
    {
        if (!$this->access->checkAccess('read', '', $a_forum_ref_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        /** @var ilObjForum $ref_obj */
        $ref_obj = ilObjectFactory::getInstanceByRefId($a_forum_ref_id);
        if ($ref_obj->getType() === 'frm') {
            $forumObj = new ilObjForum($a_forum_ref_id);
            $frm = $forumObj->Forum;
            $frm->setForumId($forumObj->getId());
            $frm->setForumRefId($forumObj->getRefId());
        } else {
            $frm = new ilForum();
        }

        if ($this->access->checkAccess('moderate_frm', '', $a_forum_ref_id)) {
            $numPosts = $frm->countUserArticles($a_user_id);
        } else {
            $numPosts = $frm->countActiveUserArticles($a_user_id);
        }

        return [$this->lng->txt('forums_posts') => $numPosts];
    }

    public function performThreadsActionObject(): void
    {
        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        ilSession::set('threads2move', []);

        $thread_ids = $this->retrieveThreadIds();
        $cmd = $this->ctrl->getCmd();
        $message = null;

        if ($thread_ids !== []) {
            if ($cmd === 'move') {
                if ($this->is_moderator) {
                    ilSession::set('threads2move', $thread_ids);
                    $this->moveThreadsObject();
                }
            } elseif ($cmd === 'enable_notifications' && (int) $this->settings->get('forum_notification', '0') !== 0) {
                foreach ($thread_ids as $thread_id) {
                    $tmp_obj = new ilForumTopic($thread_id);
                    $this->ensureThreadBelongsToForum($this->object->getId(), $tmp_obj);
                    $tmp_obj->enableNotification($this->user->getId());
                }

                $this->ctrl->redirect($this, 'showThreads');
            } elseif ($cmd === 'disable_notifications' && (int) $this->settings->get('forum_notification', '0') !== 0) {
                foreach ($thread_ids as $thread_id) {
                    $tmp_obj = new ilForumTopic($thread_id);
                    $this->ensureThreadBelongsToForum($this->object->getId(), $tmp_obj);
                    $tmp_obj->disableNotification($this->user->getId());
                }

                $this->ctrl->redirect($this, 'showThreads');
            } elseif ($cmd === 'close') {
                if ($this->is_moderator) {
                    foreach ($thread_ids as $thread_id) {
                        $tmp_obj = new ilForumTopic($thread_id);
                        $this->ensureThreadBelongsToForum($this->object->getId(), $tmp_obj);
                        $tmp_obj->close();
                    }
                }
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('selected_threads_closed'), true);
                $this->ctrl->redirect($this, 'showThreads');
            } elseif ($cmd === 'reopen') {
                if ($this->is_moderator) {
                    foreach ($thread_ids as $thread_id) {
                        $tmp_obj = new ilForumTopic($thread_id);
                        $this->ensureThreadBelongsToForum($this->object->getId(), $tmp_obj);
                        $tmp_obj->reopen();
                    }
                }

                $this->tpl->setOnScreenMessage('success', $this->lng->txt('selected_threads_reopened'), true);
                $this->ctrl->redirect($this, 'showThreads');
            } elseif ($cmd === 'makesticky') {
                if ($this->is_moderator) {
                    $message = $this->lng->txt('sel_threads_make_sticky');

                    foreach ($thread_ids as $thread_id) {
                        $tmp_obj = new ilForumTopic($thread_id);
                        $this->ensureThreadBelongsToForum($this->object->getId(), $tmp_obj);
                        $makeSticky = $tmp_obj->makeSticky();
                        if (!$makeSticky) {
                            $message = $this->lng->txt('sel_threads_already_sticky');
                        }
                    }
                }
                if ($message !== null) {
                    $this->tpl->setOnScreenMessage('info', $message, true);
                }
                $this->ctrl->redirect($this, 'showThreads');
            } elseif ($cmd === 'unmakesticky') {
                if ($this->is_moderator) {
                    $message = $this->lng->txt('sel_threads_make_unsticky');
                    foreach ($thread_ids as $thread_id) {
                        $tmp_obj = new ilForumTopic($thread_id);
                        $this->ensureThreadBelongsToForum($this->object->getId(), $tmp_obj);
                        $unmakeSticky = $tmp_obj->unmakeSticky();
                        if (!$unmakeSticky) {
                            $message = $this->lng->txt('sel_threads_already_unsticky');
                        }
                    }
                }

                if ($message !== null) {
                    $this->tpl->setOnScreenMessage('info', $message, true);
                }
                $this->ctrl->redirect($this, 'showThreads');
            } elseif ($cmd === 'editThread') {
                if ($this->is_moderator) {
                    $count = count($thread_ids);
                    if ($count !== 1) {
                        $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_max_one_thread'), true);
                        $this->ctrl->redirect($this, 'showThreads');
                    } else {
                        $this->editThreadObject(current($thread_ids));
                        return;
                    }
                }

                $this->ctrl->redirect($this, 'showThreads');
            } elseif ($cmd === 'html') {
                $this->ctrl->setCmd('exportHTML');
                $this->ctrl->setCmdClass('ilForumExportGUI');
                $this->executeCommand();
            } elseif ($cmd === 'confirmDeleteThreads') {
                $this->confirmDeleteThreads();
            } elseif ($cmd === 'mergeThreads') {
                $this->mergeThreadsObject();
            } else {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('topics_please_select_one_action'), true);
                $this->ctrl->redirect($this, 'showThreads');
            }
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_at_least_one_thread'), true);
            $this->ctrl->redirect($this, 'showThreads');
        }
    }

    public function performMoveThreadsObject(): void
    {
        if (!$this->is_moderator) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $frm_ref_id = null;
        if ($this->http->wrapper()->post()->has('frm_ref_id')) {
            $frm_ref_id = $this->http->wrapper()->post()->retrieve(
                'frm_ref_id',
                $this->refinery->kindlyTo()->int()
            );
        } else {
            $this->error->raiseError('Please select a forum', $this->error->MESSAGE);
        }

        $threads2move = ilSession::get('threads2move');
        if (!is_array($threads2move) || $threads2move === []) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_at_least_one_thread'), true);
            $this->ctrl->redirect($this, 'showThreads');
        }

        if (!$this->access->checkAccess('read', '', (int) $frm_ref_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $threads = [];
        array_walk($threads2move, function (int $threadId) use (&$threads): void {
            $thread = new ilForumTopic($threadId);
            $this->ensureThreadBelongsToForum($this->object->getId(), $thread);

            $threads[] = $threadId;
        });

        if (isset($frm_ref_id) && (int) $frm_ref_id) {
            $errorMessages = $this->object->Forum->moveThreads(
                (array) (ilSession::get('threads2move') ?? []),
                $this->object,
                $this->ilObjDataCache->lookupObjId((int) $frm_ref_id)
            );

            if ([] !== $errorMessages) {
                $this->tpl->setOnScreenMessage('failure', implode("<br><br>", $errorMessages), true);
                $this->ctrl->redirect($this, 'showThreads');
            }

            ilSession::set('threads2move', []);
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('threads_moved_successfully'), true);
            $this->ctrl->redirect($this, 'showThreads');
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('no_forum_selected'));
            $this->moveThreadsObject();
        }
    }

    public function cancelMoveThreadsObject(): void
    {
        ilSession::set('threads2move', []);
        $this->ctrl->redirect($this, 'showThreads');
    }

    public function moveThreadsObject(): bool
    {
        if (!$this->is_moderator) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $frm_ref_id = null;
        if ($this->http->wrapper()->post()->has('frm_ref_id')) {
            $frm_ref_id = $this->http->wrapper()->post()->retrieve(
                'frm_ref_id',
                $this->refinery->kindlyTo()->int()
            );
        }

        $threads2move = ilSession::get('threads2move');
        if (!is_array($threads2move) || $threads2move === []) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_at_least_one_thread'), true);
            $this->ctrl->redirect($this, 'showThreads');
        }

        $threads = [];
        $isModerator = $this->is_moderator;
        array_walk($threads2move, function (int $threadId) use (&$threads, $isModerator): void {
            $thread = new ilForumTopic($threadId, $isModerator);
            $this->ensureThreadBelongsToForum($this->object->getId(), $thread);

            $threads[] = $thread;
        });

        $exp = new ilForumMoveTopicsExplorer($this, 'moveThreads');
        $exp->setPathOpen($this->object->getRefId());
        $exp->setNodeSelected(isset($frm_ref_id) && (int) $frm_ref_id ? (int) $frm_ref_id : 0);
        $exp->setCurrentFrmRefId($this->object->getRefId());
        $exp->setHighlightedNode((string) $this->object->getRefId());
        if (!$exp->handleCommand()) {
            $moveThreadTemplate = new ilTemplate(
                'tpl.forums_threads_move.html',
                true,
                true,
                'Modules/Forum'
            );

            if (!$this->hideToolbar()) {
                $this->toolbar->addButton($this->lng->txt('back'), $this->ctrl->getLinkTarget($this));
            }

            $tblThr = new ilTable2GUI($this);
            $tblThr->setId('il_frm_thread_move_table_' . $this->object->getRefId());
            $tblThr->setTitle($this->lng->txt('move_chosen_topics'));
            $tblThr->addColumn($this->lng->txt('subject'), 'top_name', '100%');
            $tblThr->disable('header');
            $tblThr->disable('footer');
            $tblThr->disable('linkbar');
            $tblThr->disable('sort');
            $tblThr->disable('linkbar');
            $tblThr->setLimit(PHP_INT_MAX);
            $tblThr->setRowTemplate('tpl.forums_threads_move_thr_row.html', 'Modules/Forum');
            $tblThr->setDefaultOrderField('is_sticky');
            $counter = 0;
            $result = [];
            foreach ($threads as $thread) {
                $result[$counter]['num'] = $counter + 1;
                $result[$counter]['thr_subject'] = $thread->getSubject();
                ++$counter;
            }
            $tblThr->setData($result);
            $moveThreadTemplate->setVariable('THREADS_TABLE', $tblThr->getHTML());

            $moveThreadTemplate->setVariable('FRM_SELECTION_TREE', $exp->getHTML());
            $moveThreadTemplate->setVariable('CMD_SUBMIT', 'performMoveThreads');
            $moveThreadTemplate->setVariable('TXT_SUBMIT', $this->lng->txt('move'));
            $moveThreadTemplate->setVariable('FORMACTION', $this->ctrl->getFormAction($this, 'performMoveThreads'));

            $this->tpl->setContent($moveThreadTemplate->get());
        }

        return true;
    }

    private function isWritingWithPseudonymAllowed(): bool
    {
        return (
            $this->objProperties->isAnonymized() &&
            (!$this->is_moderator || !$this->objProperties->getMarkModeratorPosts())
        );
    }

    protected function deleteThreadDraftsObject(): void
    {
        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $draftIds = array_filter((array) ($this->httpRequest->getParsedBody()['draft_ids'] ?? []));
        if ([] === $draftIds) {
            $draftIds = array_filter([(int) ($this->httpRequest->getQueryParams()['draft_id'] ?? 0)]);
        }

        $instances = ilForumPostDraft::getDraftInstancesByUserId($this->user->getId());
        $checkedDraftIds = [];
        foreach ($draftIds as $draftId) {
            if (array_key_exists($draftId, $instances)) {
                $checkedDraftIds[] = $draftId;
                $draft = $instances[$draftId];

                $this->deleteMobsOfDraft($draft->getDraftId(), $draft->getPostMessage());

                $draftFileData = new ilFileDataForumDrafts(0, $draft->getDraftId());
                $draftFileData->delete();

                $GLOBALS['ilAppEventHandler']->raise(
                    'Modules/Forum',
                    'deletedDraft',
                    [
                        'draftObj' => $draft,
                        'obj_id' => $this->object->getId(),
                        'is_file_upload_allowed' => $this->objProperties->isFileUploadAllowed(),
                    ]
                );

                $draft->deleteDraft();
            }
        }

        if (count($checkedDraftIds) > 1) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('delete_drafts_successfully'), true);
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('delete_draft_successfully'), true);
        }
        $this->ctrl->redirect($this, 'showThreads');
    }

    private function buildThreadForm(bool $isDraft = false): ilForumThreadFormGUI
    {
        $draftId = (int) ($this->httpRequest->getQueryParams()['draft_id'] ?? 0);
        $allowNotification = !$this->objProperties->isAnonymized();

        $mail = new ilMail($this->user->getId());
        if (!$this->rbac->system()->checkAccess('internal_mail', $mail->getMailObjectReferenceId())) {
            $allowNotification = false;
        }

        $default_form = new ilForumThreadFormGUI(
            $this,
            $this->objProperties,
            $this->isWritingWithPseudonymAllowed(),
            $allowNotification,
            $isDraft,
            $draftId
        );

        $default_form->addInputItem(ilForumThreadFormGUI::ALIAS_INPUT);
        $default_form->addInputItem(ilForumThreadFormGUI::SUBJECT_INPUT);
        $default_form->addInputItem(ilForumThreadFormGUI::MESSAGE_INPUT);
        $default_form->addInputItem(ilForumThreadFormGUI::FILE_UPLOAD_INPUT);
        $default_form->addInputItem(ilForumThreadFormGUI::ALLOW_NOTIFICATION_INPUT);

        $default_form->generateDefaultForm();

        $this->decorateWithAutosave($default_form);

        return $default_form;
    }

    private function buildMinimalThreadForm(bool $isDraft = false): ilForumThreadFormGUI
    {
        $draftId = (int) ($this->httpRequest->getQueryParams()['draft_id'] ?? 0);
        $allowNotification = !$this->objProperties->isAnonymized();

        $mail = new ilMail($this->user->getId());
        if (!$this->rbac->system()->checkAccess('internal_mail', $mail->getMailObjectReferenceId())) {
            $allowNotification = false;
        }

        $minimal_form = new ilForumThreadFormGUI(
            $this,
            $this->objProperties,
            $this->isWritingWithPseudonymAllowed(),
            $allowNotification,
            $isDraft,
            $draftId
        );

        $minimal_form->addInputItem(ilForumThreadFormGUI::ALIAS_INPUT);
        $minimal_form->addInputItem(ilForumThreadFormGUI::SUBJECT_INPUT);

        $minimal_form->generateMinimalForm();

        return $minimal_form;
    }

    private function createThreadObject(): void
    {
        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!$this->access->checkAccess('add_thread', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $tpl = new ilTemplate('tpl.create_thread_form.html', true, true, 'Modules/Forum');

        $accordion = new ilAccordionGUI();
        $accordion->setId('acc_' . $this->obj_id);
        $accordion->setBehaviour(ilAccordionGUI::FIRST_OPEN);

        $accordion->addItem($this->lng->txt('new_thread_with_post'), $this->buildThreadForm()->getHTML());
        $accordion->addItem($this->lng->txt('empty_thread'), $this->buildMinimalThreadForm()->getHTML());

        $tpl->setVariable('CREATE_FORM', $accordion->getHTML());
        $tpl->parseCurrentBlock();

        $this->tpl->setContent($tpl->get());
    }

    /**
     * Refactored thread creation to method, refactoring to a separate class should be done in next refactoring steps
     */
    private function createThread(ilForumPostDraft $draft, bool $createFromDraft = false): void
    {
        if (
            !$this->access->checkAccess('add_thread', '', $this->object->getRefId()) ||
            !$this->access->checkAccess('read', '', $this->object->getRefId())
        ) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $frm = $this->object->Forum;
        $frm->setForumId($this->object->getId());
        $frm->setForumRefId($this->object->getRefId());
        $frm->setMDB2WhereCondition('top_frm_fk = %s ', ['integer'], [$frm->getForumId()]);
        $topicData = $frm->getOneTopic();

        $form = $this->buildThreadForm($createFromDraft);
        $minimal_form = $this->buildMinimalThreadForm($createFromDraft);

        if ($form->checkInput()) {
            $userIdForDisplayPurposes = $this->user->getId();
            if ($this->isWritingWithPseudonymAllowed()) {
                $userIdForDisplayPurposes = 0;
            }

            $status = true;
            if (
                ($this->objProperties->isPostActivationEnabled() && !$this->is_moderator) ||
                $this->objCurrentPost->isAnyParentDeactivated()
            ) {
                $status = false;
            }

            if ($createFromDraft) {
                $newThread = new ilForumTopic(0, true, true);
                $newThread->setForumId($topicData->getTopPk());
                $newThread->setThrAuthorId($draft->getPostAuthorId());
                $newThread->setDisplayUserId($draft->getPostDisplayUserId());
                $newThread->setSubject($this->handleFormInput($form->getInput('subject'), false));
                $newThread->setUserAlias($draft->getPostUserAlias());

                $newPost = $frm->generateThread(
                    $newThread,
                    ilRTE::_replaceMediaObjectImageSrc($form->getInput('message')),
                    $draft->isNotificationEnabled(),
                    $draft->isPostNotificationEnabled(),
                    $status
                );
            } else {
                $userAlias = ilForumUtil::getPublicUserAlias(
                    $form->getInput('alias'),
                    $this->objProperties->isAnonymized()
                );
                $newThread = new ilForumTopic(0, true, true);
                $newThread->setForumId($topicData->getTopPk());
                $newThread->setThrAuthorId($this->user->getId());
                $newThread->setDisplayUserId($userIdForDisplayPurposes);
                $newThread->setSubject($this->handleFormInput($form->getInput('subject'), false));
                $newThread->setUserAlias($userAlias);

                $newPost = $frm->generateThread(
                    $newThread,
                    ilRTE::_replaceMediaObjectImageSrc($form->getInput('message')),
                    $form->getItemByPostVar('notify') && $form->getInput('notify'),
                    false, // #19980
                    $status
                );
            }

            if ($this->objProperties->isFileUploadAllowed()) {
                $file = $_FILES['userfile'];
                if (is_array($file) && !empty($file)) {
                    $fileData = new ilFileDataForum($this->object->getId(), $newPost);
                    $fileData->storeUploadedFile($file);
                }
            }

            $frm->setDbTable('frm_data');
            $frm->setMDB2WhereCondition('top_pk = %s ', ['integer'], [$topicData->getTopPk()]);
            $frm->updateVisits($topicData->getTopPk());

            if ($createFromDraft) {
                $mediaObjects = ilObjMediaObject::_getMobsOfObject('frm~:html', $this->user->getId());
            } else {
                $mediaObjects = ilRTE::_getMediaObjects($form->getInput('message'));
            }
            foreach ($mediaObjects as $mob) {
                if (ilObjMediaObject::_exists($mob)) {
                    ilObjMediaObject::_removeUsage($mob, 'frm~:html', $this->user->getId());
                    ilObjMediaObject::_saveUsage($mob, 'frm:html', $newPost);
                }
            }

            if ($draft->getDraftId() > 0) {
                $draftHistory = new ilForumDraftsHistory();
                $draftHistory->deleteHistoryByDraftIds([$draft->getDraftId()]);
                if ($this->objProperties->isFileUploadAllowed()) {
                    $forumFileData = new ilFileDataForum($this->object->getId(), $newPost);
                    $draftFileData = new ilFileDataForumDrafts($this->object->getId(), $draft->getDraftId());
                    $draftFileData->moveFilesOfDraft($forumFileData->getForumPath(), $newPost);
                }
                $draft->deleteDraft();
            }

            $GLOBALS['ilAppEventHandler']->raise(
                'Modules/Forum',
                'createdPost',
                [
                    'object' => $this->object,
                    'ref_id' => $this->object->getRefId(),
                    'post' => new ilForumPost($newPost),
                    'notify_moderators' => !$status
                ]
            );

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('forums_thread_new_entry'), true);
            $this->ctrl->redirect($this);
        }

        $form->setValuesByPost();
        if (!$this->objProperties->isAnonymized()) {
            $form->getItemByPostVar('alias')->setValue($this->user->getLogin());
        }

        $accordion = new ilAccordionGUI();
        $accordion->setId('acc_' . $this->obj_id);
        $accordion->setBehaviour(ilAccordionGUI::FIRST_OPEN);
        $accordion->addItem($this->lng->txt('new_thread_with_post'), $form->getHTML());
        $accordion->addItem($this->lng->txt('empty_thread'), $minimal_form->getHTML());

        $this->tpl->setContent($accordion->getHTML());
    }

    /**
     * Refactored thread creation to method, refactoring to a separate class should be done in next refactoring steps
     */
    private function createEmptyThread(bool $createFromDraft = false): void
    {
        if (
            !$this->access->checkAccess('add_thread', '', $this->object->getRefId()) ||
            !$this->access->checkAccess('read', '', $this->object->getRefId())
        ) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $frm = $this->object->Forum;
        $frm->setForumId($this->object->getId());
        $frm->setForumRefId($this->object->getRefId());
        $frm->setMDB2WhereCondition('top_frm_fk = %s ', ['integer'], [$frm->getForumId()]);
        $topicData = $frm->getOneTopic();

        $form = $this->buildThreadForm($createFromDraft);
        $minimal_form = $this->buildMinimalThreadForm($createFromDraft);

        if ($minimal_form->checkInput()) {
            $userIdForDisplayPurposes = $this->user->getId();
            if ($this->isWritingWithPseudonymAllowed()) {
                $userIdForDisplayPurposes = 0;
            }

            $status = true;
            if (
                ($this->objProperties->isPostActivationEnabled() && !$this->is_moderator) ||
                $this->objCurrentPost->isAnyParentDeactivated()
            ) {
                $status = false;
            }

            $userAlias = ilForumUtil::getPublicUserAlias(
                $minimal_form->getInput('alias'),
                $this->objProperties->isAnonymized()
            );
            $newThread = new ilForumTopic(0, true, true);
            $newThread->setForumId($topicData->getTopPk());
            $newThread->setThrAuthorId($this->user->getId());
            $newThread->setDisplayUserId($userIdForDisplayPurposes);
            $newThread->setSubject($this->handleFormInput($minimal_form->getInput('subject'), false));
            $newThread->setUserAlias($userAlias);

            $frm->generateThread(
                $newThread,
                '',
                false,
                false, // #19980
                $status,
                false
            );

            $frm->setDbTable('frm_data');
            $frm->setMDB2WhereCondition('top_pk = %s ', ['integer'], [$topicData->getTopPk()]);
            $frm->updateVisits($topicData->getTopPk());

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('forums_thread_new_entry'), true);
            $this->ctrl->redirect($this);
        }

        $form->setValuesByPost();

        if (!$this->objProperties->isAnonymized()) {
            $form->getItemByPostVar('alias')->setValue($this->user->getLogin());
        }

        $accordion = new ilAccordionGUI();
        $accordion->setId('acc_' . $this->obj_id);
        $accordion->setBehaviour(ilAccordionGUI::FIRST_OPEN);
        $accordion->addItem($this->lng->txt('new_thread_with_post'), $form->getHTML());
        $accordion->addItem($this->lng->txt('empty_thread'), $minimal_form->getHTML());

        $this->tpl->setContent($accordion->getHTML());
    }

    protected function publishThreadDraftObject(): void
    {
        if (!ilForumPostDraft::isSavePostDraftAllowed()) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $draftId = (int) ($this->httpRequest->getQueryParams()['draft_id'] ?? 0);
        $draft = ilForumPostDraft::newInstanceByDraftId($draftId);

        if ($draft->getDraftId() <= 0) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->createThread($draft, true);
    }

    protected function addThreadObject(): void
    {
        $draft = new ilForumPostDraft();
        if (ilForumPostDraft::isSavePostDraftAllowed()) {
            $draftId = (int) ($this->httpRequest->getParsedBody()['draft_id'] ?? 0);
            if ($draftId > 0) {
                $draft = ilForumPostDraft::newInstanceByDraftId($draftId);
            }
        }

        $this->createThread($draft);
    }

    protected function addEmptyThreadObject(): void
    {
        $draft = new ilForumPostDraft();
        if (ilForumPostDraft::isSavePostDraftAllowed()) {
            $draftId = (int) ($this->httpRequest->getParsedBody()['draft_id'] ?? 0);
            if ($draftId > 0) {
                $draft = ilForumPostDraft::newInstanceByDraftId($draftId);
            }
        }

        $this->createEmptyThread();
    }

    protected function enableForumNotificationObject(): void
    {
        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $frm = $this->object->Forum;
        $frm->setForumId($this->object->getId());
        $frm->enableForumNotification($this->user->getId());

        if ($this->objCurrentTopic->getId() > 0) {
            $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('forums_forum_notification_enabled'), true);
            $this->ctrl->redirect($this, 'viewThread');
        }

        $this->tpl->setOnScreenMessage('info', $this->lng->txt('forums_forum_notification_enabled'));
        $this->showThreadsObject();
    }

    protected function disableForumNotificationObject(): void
    {
        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $frm = $this->object->Forum;
        $frm->setForumId($this->object->getId());
        $frm->disableForumNotification($this->user->getId());

        if ($this->objCurrentTopic->getId() > 0) {
            $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('forums_forum_notification_disabled'), true);
            $this->ctrl->redirect($this, 'viewThread');
        }

        $this->tpl->setOnScreenMessage('info', $this->lng->txt('forums_forum_notification_disabled'));
        $this->showThreadsObject();
    }

    public function setColumnSettings(ilColumnGUI $column_gui): void
    {
        $column_gui->setBlockProperty('news', 'title', $this->lng->txt('frm_latest_postings'));
        $column_gui->setBlockProperty('news', 'prevent_aggregation', '1');
        $column_gui->setRepositoryMode(true);

        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $news_set = new ilSetting('news');
            if ($news_set->get('enable_rss_for_internal')) {
                $column_gui->setBlockProperty('news', 'settings', '1');
                $column_gui->setBlockProperty('news', 'public_notifications_option', '1');
            }
        }
    }

    protected function addLocatorItems(): void
    {
        if ($this->object instanceof ilObjForum) {
            $this->locator->addItem(
                $this->object->getTitle(),
                $this->ctrl->getLinkTarget($this),
                '',
                $this->object->getRefId()
            );
        }
    }

    public function handleFormInput(string $a_text, bool $a_stripslashes = true): string
    {
        $a_text = str_replace(["<", ">"], ["&lt;", "&gt;"], $a_text);
        if ($a_stripslashes) {
            $a_text = ilUtil::stripSlashes($a_text);
        }

        return $a_text;
    }

    public function prepareFormOutput(string $a_text): string
    {
        $a_text = str_replace(["&lt;", "&gt;"], ["<", ">"], $a_text);

        return ilLegacyFormElementsUtil::prepareFormOutput($a_text);
    }

    protected function infoScreen(): void
    {
        if (
            !$this->access->checkAccess('visible', '', $this->object->getRefId()) &&
            !$this->access->checkAccess('read', '', $this->object->getRefId())
        ) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
        }

        $info = new ilInfoScreenGUI($this);
        $info->enablePrivateNotes();
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
        $this->ctrl->forwardCommand($info);
    }

    protected function markPostUnreadObject(): void
    {
        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if ($this->objCurrentPost->getId() > 0) {
            $this->ensureThreadBelongsToForum($this->object->getId(), $this->objCurrentPost->getThread());

            $this->object->markPostUnread($this->user->getId(), $this->objCurrentPost->getId());
        }
        $this->viewThreadObject();
    }

    protected function markPostReadObject(): void
    {
        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if ($this->objCurrentTopic->getId() > 0 && $this->objCurrentPost->getId() > 0) {
            $this->ensureThreadBelongsToForum($this->object->getId(), $this->objCurrentPost->getThread());

            $this->object->markPostRead(
                $this->user->getId(),
                $this->objCurrentTopic->getId(),
                $this->objCurrentPost->getId()
            );
        }

        $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
        $this->ctrl->redirect($this, 'viewThread');
    }

    protected function initHeaderAction(?string $sub_type = null, ?int $sub_id = null): ?ilObjectListGUI
    {
        $lg = parent::initHeaderAction();

        if (!($lg instanceof ilObjForumListGUI) || !((bool) $this->settings->get('forum_notification', '0'))) {
            return $lg;
        }

        if ($this->user->isAnonymous() || !$this->access->checkAccess('read', '', $this->object->getRefId())) {
            return $lg;
        }

        $frm = $this->object->Forum;
        $frm->setForumId($this->object->getId());
        $frm->setForumRefId($this->object->getRefId());
        $frm->setMDB2Wherecondition('top_frm_fk = %s ', ['integer'], [$frm->getForumId()]);

        $isForumNotificationEnabled = $frm->isForumNotificationEnabled($this->user->getId());
        $userMayDisableNotifications = $this->isUserAllowedToDeactivateNotification();

        if ($this->objCurrentTopic->getId() > 0) {
            $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
        }

        if ($this->isParentObjectCrsOrGrp()) {
            // special behaviour for CRS/GRP-Forum notification!!
            if ($isForumNotificationEnabled && $userMayDisableNotifications) {
                $lg->addCustomCommand(
                    $this->ctrl->getLinkTarget($this, 'disableForumNotification'),
                    'forums_disable_forum_notification'
                );
            } elseif (!$isForumNotificationEnabled) {
                $lg->addCustomCommand(
                    $this->ctrl->getLinkTarget($this, 'enableForumNotification'),
                    'forums_enable_forum_notification'
                );
            }
        } elseif ($isForumNotificationEnabled) {
            $lg->addCustomCommand(
                $this->ctrl->getLinkTarget($this, 'disableForumNotification'),
                'forums_disable_forum_notification'
            );
        } else {
            $lg->addCustomCommand(
                $this->ctrl->getLinkTarget($this, 'enableForumNotification'),
                'forums_enable_forum_notification'
            );
        }

        $ref_id = $this->retrieveRefId();
        if ($isForumNotificationEnabled && $userMayDisableNotifications) {
            $frm_noti = new ilForumNotification($ref_id);
            $frm_noti->setUserId($this->user->getId());
            $interested_events = $frm_noti->readInterestedEvents();

            $events_form_builder = $this->eventsFormBuilder([
                'hidden_value' => '',
                'notify_modified' => (bool) ($interested_events & ilForumNotificationEvents::UPDATED),
                'notify_censored' => (bool) ($interested_events & ilForumNotificationEvents::CENSORED),
                'notify_uncensored' => (bool) ($interested_events & ilForumNotificationEvents::UNCENSORED),
                'notify_post_deleted' => (bool) ($interested_events & ilForumNotificationEvents::POST_DELETED),
                'notify_thread_deleted' => (bool) ($interested_events & ilForumNotificationEvents::THREAD_DELETED),
            ]);

            $notificationsModal = $this->uiFactory->modal()->roundtrip(
                $this->lng->txt('notification_settings'),
                $events_form_builder->build()
            )->withActionButtons([
                $this->uiFactory
                    ->button()
                    ->primary($this->lng->txt('save'), '#')
                    ->withOnLoadCode(function (string $id): string {
                        return "
                            $('#$id').closest('.modal').find('form').addClass('ilForumNotificationSettingsForm');
                            $('#$id').closest('.modal').find('form .il-standard-form-header, .il-standard-form-footer').remove();
                            $('#$id').click(function() { $(this).closest('.modal').find('form').submit(); return false; });
                        ";
                    })
            ]);

            $showNotificationSettingsBtn = $this->uiFactory->button()
                                                           ->shy($this->lng->txt('notification_settings'), '#')
                                                           ->withOnClick(
                                                               $notificationsModal->getShowSignal()
                                                           );

            $lg->addCustomCommandButton($showNotificationSettingsBtn, $notificationsModal);
        }

        $isThreadNotificationEnabled = false;
        if ($this->objCurrentTopic->getId() > 0) {
            $isThreadNotificationEnabled = $this->objCurrentTopic->isNotificationEnabled($this->user->getId());
            if ($isThreadNotificationEnabled) {
                $lg->addCustomCommand(
                    $this->ctrl->getLinkTarget($this, 'toggleThreadNotification'),
                    'forums_disable_notification'
                );
            } else {
                $lg->addCustomCommand(
                    $this->ctrl->getLinkTarget($this, 'toggleThreadNotification'),
                    'forums_enable_notification'
                );
            }
        }
        $this->ctrl->setParameter($this, 'thr_pk', '');

        if ($isForumNotificationEnabled || $isThreadNotificationEnabled) {
            $lg->addHeaderIcon(
                'not_icon',
                ilUtil::getImagePath('notification_on.svg'),
                $this->lng->txt('frm_notification_activated')
            );
        } else {
            $lg->addHeaderIcon(
                'not_icon',
                ilUtil::getImagePath('notification_off.svg'),
                $this->lng->txt('frm_notification_deactivated')
            );
        }

        return $lg;
    }

    /**
     * @param null|array<string, mixed> $predefined_values
     * @throws ilCtrlException
     */
    private function eventsFormBuilder(?array $predefined_values = null): ilForumNotificationEventsFormGUI
    {
        if ($this->objCurrentTopic->getId() > 0) {
            $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
        }

        return new ilForumNotificationEventsFormGUI(
            $this->ctrl->getFormAction($this, 'saveUserNotificationSettings'),
            $predefined_values,
            $this->uiFactory,
            $this->lng
        );
    }

    public function saveUserNotificationSettingsObject(): void
    {
        $events_form_builder = $this->eventsFormBuilder();

        if ($this->httpRequest->getMethod() === 'POST') {
            $form = $events_form_builder->build()->withRequest($this->httpRequest);
            $formData = $form->getData();

            $interested_events = ilForumNotificationEvents::DEACTIVATED;

            foreach ($events_form_builder->getValidEvents() as $event) {
                $interested_events += isset($formData[$event]) && $formData[$event] ? $events_form_builder->getValueForEvent(
                    $event
                ) : 0;
            }

            $frm_noti = new ilForumNotification($this->object->getRefId());
            $frm_noti->setUserId($this->user->getId());
            $frm_noti->setInterestedEvents($interested_events);
            $frm_noti->updateInterestedEvents();
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);

        if ($this->objCurrentTopic->getId() > 0) {
            $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
            $this->ctrl->redirect($this, 'viewThread');
        }

        $this->ctrl->redirect($this, 'showThreads');
    }

    public function isUserAllowedToDeactivateNotification(): bool
    {
        if ($this->objProperties->getNotificationType() === 'default') {
            return true;
        }

        if (!$this->objProperties->isUserToggleNoti()) {
            return true;
        }

        $ref_id = $this->retrieveRefId();
        if ($this->isParentObjectCrsOrGrp()) {
            $frm_noti = new ilForumNotification($ref_id);
            $frm_noti->setUserId($this->user->getId());

            return !$frm_noti->isUserToggleNotification();
        }

        return false;
    }

    public function isParentObjectCrsOrGrp(): bool
    {
        $grpRefId = $this->tree->checkForParentType($this->object->getRefId(), 'grp');
        $crsRefId = $this->tree->checkForParentType($this->object->getRefId(), 'crs');

        return ($grpRefId > 0 || $crsRefId > 0);
    }

    protected function saveThreadSortingObject(): void
    {
        if (!$this->is_moderator) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $threadIdToSortValueMap = (array) ($this->httpRequest->getParsedBody()['thread_sorting'] ?? []);

        array_walk($threadIdToSortValueMap, function ($sortValue, $threadId): void {
            $this->ensureThreadBelongsToForum($this->object->getId(), new ilForumTopic((int) $threadId));
        });

        foreach ($threadIdToSortValueMap as $threadId => $sortValue) {
            $sortValue = str_replace(',', '.', $sortValue);
            $sortValue = ((float) $sortValue) * 100;
            $this->object->setThreadSorting((int) $threadId, (int) $sortValue);
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this, 'showThreads');
    }

    public function mergeThreadsObject(): void
    {
        if (!$this->is_moderator) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $threadIdToMerge = (int) ($this->httpRequest->getQueryParams()['merge_thread_id'] ?? 0);
        if ($threadIdToMerge <= 0) {
            $threadIds = array_values(
                array_filter(array_map('intval', (array) ($this->httpRequest->getParsedBody()['thread_ids'] ?? [])))
            );
            if (1 === count($threadIds)) {
                $threadIdToMerge = current($threadIds);
            } else {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_one'));
                $this->showThreadsObject();
                return;
            }
        }

        $frm = $this->object->Forum;
        $frm->setForumId($this->object->getId());
        $frm->setForumRefId($this->object->getRefId());

        $threadToMerge = new ilForumTopic($threadIdToMerge);

        if (ilForum::_lookupObjIdForForumId($threadToMerge->getForumId()) !== $frm->getForumId()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('not_allowed_to_merge_into_another_forum'));
            $this->showThreadsObject();
            return;
        }

        $frm->setMDB2Wherecondition('top_frm_fk = %s ', ['integer'], [$frm->getForumId()]);

        $threadsTemplate = new ilTemplate(
            'tpl.forums_threads_liste.html',
            true,
            true,
            'Modules/Forum'
        );

        $topicData = $frm->getOneTopic();
        if ($topicData->getTopPk() > 0) {
            $this->ctrl->setParameter($this, 'merge_thread_id', $threadIdToMerge);
            $tbl = new ilForumTopicTableGUI(
                $this,
                'mergeThreads',
                (int) $this->httpRequest->getQueryParams()['ref_id'],
                $topicData,
                $this->is_moderator,
                (int) $this->settings->get('forum_overview', '0')
            );
            $tbl->setSelectedThread($threadToMerge);
            $tbl->setMapper($frm)->fetchData();
            $tbl->init();
            $threadsTemplate->setVariable('THREADS_TABLE', $tbl->getHTML());
            $this->tpl->setContent($threadsTemplate->get());
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->showThreadsObject();
        }
    }

    public function confirmMergeThreadsObject(): void
    {
        if (!$this->is_moderator) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $sourceThreadId = (int) ($this->httpRequest->getQueryParams()['merge_thread_id'] ?? 0);
        $targetThreadIds = array_values(
            array_filter(array_map('intval', (array) ($this->httpRequest->getParsedBody()['thread_ids'] ?? [])))
        );

        if ($sourceThreadId <= 0 || 1 !== count($targetThreadIds)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->mergeThreadsObject();
            return;
        }

        $targetThreadId = current($targetThreadIds);
        if ($sourceThreadId === $targetThreadId) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('error_same_thread_ids'));
            $this->showThreadsObject();
            return;
        }

        if (ilForumTopic::lookupForumIdByTopicId($sourceThreadId) !== ilForumTopic::lookupForumIdByTopicId($targetThreadId)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('not_allowed_to_merge_into_another_forum'));
            $this->ctrl->clearParameters($this);
            $this->showThreadsObject();
            return;
        }

        if (ilForumTopic::lookupCreationDate($sourceThreadId) < ilForumTopic::lookupCreationDate($targetThreadId)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('switch_threads_for_merge'));
        }

        $this->ensureThreadBelongsToForum($this->object->getId(), new ilForumTopic($sourceThreadId));
        $this->ensureThreadBelongsToForum($this->object->getId(), new ilForumTopic($targetThreadId));

        $c_gui = new ilConfirmationGUI();

        $c_gui->setFormAction($this->ctrl->getFormAction($this, 'performMergeThreads'));
        $c_gui->setHeaderText($this->lng->txt('frm_sure_merge_threads'));
        $c_gui->setCancel($this->lng->txt('cancel'), 'showThreads');
        $c_gui->setConfirm($this->lng->txt('confirm'), 'performMergeThreads');

        $c_gui->addItem(
            'thread_ids[]',
            (string) $sourceThreadId,
            sprintf($this->lng->txt('frm_merge_src'), ilForumTopic::lookupTitle($sourceThreadId))
        );
        $c_gui->addItem(
            'thread_ids[]',
            (string) $targetThreadId,
            sprintf($this->lng->txt('frm_merge_target'), ilForumTopic::lookupTitle($targetThreadId))
        );
        $this->tpl->setContent($c_gui->getHTML());
    }

    public function performMergeThreadsObject(): void
    {
        if (!$this->is_moderator) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $threadIds = array_values(
            array_filter(array_map('intval', (array) ($this->httpRequest->getParsedBody()['thread_ids'] ?? [])))
        );
        if (2 !== count($threadIds)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->showThreadsObject();
            return;
        }

        if ((int) $threadIds[0] === (int) $threadIds[1]) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('error_same_thread_ids'));
            $this->showThreadsObject();
            return;
        }

        try {
            $frm = new ilForum();
            $frm->setForumId($this->object->getId());
            $frm->setForumRefId($this->object->getRefId());

            $this->ensureThreadBelongsToForum($this->object->getId(), new ilForumTopic((int) $threadIds[0]));
            $this->ensureThreadBelongsToForum($this->object->getId(), new ilForumTopic((int) $threadIds[1]));

            $frm->mergeThreads((int) $threadIds[0], (int) $threadIds[1]);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('merged_threads_successfully'));
        } catch (ilException $e) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt($e->getMessage()));
        }

        $this->showThreadsObject();
    }

    protected function setSideBlocks(): void
    {
        $content = $this->getRightColumnHTML();
        if (!$this->ctrl->isAsynch()) {
            $content = implode('', [
                ilRepositoryObjectSearchGUI::getSearchBlockHTML($this->lng->txt('frm_search')),
                $content,
            ]);
        }
        $this->tpl->setRightContent($content);
    }

    protected function deliverDraftZipFileObject(): void
    {
        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $draftId = $this->httpRequest->getQueryParams()['draft_id'] ?? 0;
        $draft = ilForumPostDraft::newInstanceByDraftId((int) $draftId);
        if ($draft->getPostAuthorId() === $this->user->getId()) {
            $fileData = new ilFileDataForumDrafts(0, $draft->getDraftId());
            if (!$fileData->deliverZipFile()) {
                $this->ctrl->redirect($this);
            }
        }
    }

    protected function deliverZipFileObject(): void
    {
        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->ensureThreadBelongsToForum($this->object->getId(), $this->objCurrentPost->getThread());

        $fileData = new ilFileDataForum($this->object->getId(), $this->objCurrentPost->getId());
        if (!$fileData->deliverZipFile()) {
            $this->ctrl->redirect($this);
        }
    }

    /**
     * @param ilPropertyFormGUI|null $form
     */
    protected function editThreadDraftObject(ilPropertyFormGUI $form = null): void
    {
        if (
            !ilForumPostDraft::isSavePostDraftAllowed() ||
            !$this->access->checkAccess('add_thread', '', $this->object->getRefId()) ||
            !$this->access->checkAccess('read', '', $this->object->getRefId())
        ) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $frm = $this->object->Forum;
        $frm->setForumId($this->object->getId());
        $frm->setForumRefId($this->object->getRefId());

        $draft = new ilForumPostDraft();
        $draftId = (int) ($this->httpRequest->getQueryParams()['draft_id'] ?? 0);
        if ($draftId > 0) {
            $draft = ilForumPostDraft::newInstanceByDraftId($draftId);
        }

        $historyCheck = (int) ($this->httpRequest->getQueryParams()['hist_check'] ?? 1);
        if (!($form instanceof ilPropertyFormGUI) && $historyCheck > 0) {
            $this->doHistoryCheck($draft->getDraftId());
        }

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->buildThreadForm(true);
            $form->setValuesByArray([
                'alias' => $draft->getPostUserAlias(),
                'subject' => $draft->getPostSubject(),
                'message' => ilRTE::_replaceMediaObjectImageSrc($frm->prepareText($draft->getPostMessage(), 2), 1),
                'notify' => $draft->isNotificationEnabled(),
                'userfile' => '',
                'del_file' => [],
                'draft_id' => $draftId
            ]);
        } else {
            $this->ctrl->setParameter($this, 'draft_id', $draftId);
        }

        $this->tpl->setContent($form->getHTML() . $this->modal_history);
    }

    protected function restoreFromHistoryObject(): void
    {
        $historyId = (int) ($this->httpRequest->getQueryParams()['history_id'] ?? 0);
        $history = new ilForumDraftsHistory($historyId);

        $draft = $history->rollbackAutosave();
        if ($draft->getThreadId() === 0 && $draft->getPostId() === 0) {
            $this->ctrl->setParameter($this, 'draft_id', $history->getDraftId());
            $this->ctrl->redirect($this, 'editThreadDraft');
        }

        $this->ctrl->clearParameters($this);
        $this->ctrl->setParameter($this, 'pos_pk', $draft->getPostId());
        $this->ctrl->setParameter($this, 'thr_pk', $draft->getThreadId());
        $this->ctrl->setParameter($this, 'draft_id', $draft->getDraftId());
        $this->ctrl->setParameter($this, 'action', 'editdraft');

        ilForumPostDraft::createDraftBackup($draft->getDraftId());

        $this->ctrl->redirect($this, 'viewThread');
    }

    protected function saveThreadAsDraftObject(): void
    {
        if (
            !ilForumPostDraft::isSavePostDraftAllowed() ||
            !$this->access->checkAccess('add_thread', '', $this->object->getRefId()) ||
            !$this->access->checkAccess('read', '', $this->object->getRefId())
        ) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $autoSavedDraftId = (int) ($this->httpRequest->getParsedBody()['draft_id'] ?? 0);
        if ($autoSavedDraftId <= 0) {
            $autoSavedDraftId = (int) ($this->httpRequest->getQueryParams()['draft_id'] ?? 0);
        }

        $frm = $this->object->Forum;
        $frm->setForumId($this->object->getId());
        $frm->setForumRefId($this->object->getRefId());
        $frm->setMDB2WhereCondition('top_frm_fk = %s ', ['integer'], [$frm->getForumId()]);
        $topicData = $frm->getOneTopic();

        $form = $this->buildThreadForm();
        if ($form->checkInput()) {
            if (0 === $autoSavedDraftId) {
                $draft = new ilForumPostDraft();
            } else {
                $draft = ilForumPostDraft::newInstanceByDraftId($autoSavedDraftId);
            }

            $draft->setForumId($topicData->getTopPk());
            $draft->setThreadId(0);
            $draft->setPostId(0);
            $draft->setPostSubject($this->handleFormInput($form->getInput('subject'), false));
            $draft->setPostMessage(ilRTE::_replaceMediaObjectImageSrc($form->getInput('message')));
            $userAlias = ilForumUtil::getPublicUserAlias(
                $form->getInput('alias'),
                $this->objProperties->isAnonymized()
            );
            $draft->setPostUserAlias($userAlias);
            $draft->setNotificationStatus((bool) $form->getInput('notify'));
            $draft->setPostAuthorId($this->user->getId());
            $draft->setPostDisplayUserId(($this->objProperties->isAnonymized() ? 0 : $this->user->getId()));

            if (0 === $autoSavedDraftId) {
                $draftId = $draft->saveDraft();
            } else {
                $draft->updateDraft();
                $draftId = $draft->getDraftId();
            }

            $GLOBALS['ilAppEventHandler']->raise(
                'Modules/Forum',
                'savedAsDraft',
                [
                    'draftObj' => $draft,
                    'obj_id' => $this->object->getId(),
                    'is_file_upload_allowed' => $this->objProperties->isFileUploadAllowed(),
                ]
            );

            ilForumUtil::moveMediaObjects($form->getInput('message'), 'frm~d:html', $draftId, 'frm~d:html', $draftId);

            $draftFileData = new ilFileDataForumDrafts($this->object->getId(), $draftId);

            $files2delete = $form->getInput('del_file');
            if (is_array($files2delete) && $files2delete !== []) {
                $draftFileData->unlinkFilesByMD5Filenames($files2delete);
            }

            if ($this->objProperties->isFileUploadAllowed()) {
                $file = $_FILES['userfile'];
                if (is_array($file) && !empty($file)) {
                    $draftFileData->storeUploadedFile($file);
                }
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('save_draft_successfully'), true);
            $this->ctrl->clearParameters($this);
            $this->ctrl->redirect($this, 'showThreads');
        }

        $this->requestAction = substr($this->requestAction, 6);
        $form->setValuesByPost();
        $this->ctrl->setParameter($this, 'draft_id', $autoSavedDraftId);
        $this->tpl->setContent($form->getHTML());
    }

    protected function updateThreadDraftObject(): void
    {
        if (
            !ilForumPostDraft::isSavePostDraftAllowed() ||
            !$this->access->checkAccess('add_thread', '', $this->object->getRefId()) ||
            !$this->access->checkAccess('read', '', $this->object->getRefId())
        ) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $draftId = (int) ($this->httpRequest->getQueryParams()['draft_id'] ?? 0);
        if ($draftId <= 0 || !$this->checkDraftAccess($draftId)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $form = $this->buildThreadForm(true);
        if ($form->checkInput()) {
            $userAlias = ilForumUtil::getPublicUserAlias(
                $form->getInput('alias'),
                $this->objProperties->isAnonymized()
            );

            $draft = ilForumPostDraft::newInstanceByDraftId($draftId);
            $draft->setPostSubject($this->handleFormInput($form->getInput('subject'), false));
            $draft->setPostMessage(ilRTE::_replaceMediaObjectImageSrc($form->getInput('message')));
            $draft->setPostUserAlias($userAlias);
            $draft->setNotificationStatus((bool) $form->getInput('notify'));
            $draft->setPostAuthorId($this->user->getId());
            $draft->setPostDisplayUserId(($this->objProperties->isAnonymized() ? 0 : $this->user->getId()));
            $draft->updateDraft();

            $GLOBALS['ilAppEventHandler']->raise(
                'Modules/Forum',
                'updatedDraft',
                [
                    'draftObj' => $draft,
                    'obj_id' => $this->object->getId(),
                    'is_file_upload_allowed' => $this->objProperties->isFileUploadAllowed(),
                ]
            );

            ilForumUtil::moveMediaObjects(
                $form->getInput('message'),
                'frm~d:html',
                $draft->getDraftId(),
                'frm~d:html',
                $draft->getDraftId()
            );

            $draftFileData = new ilFileDataForumDrafts($this->object->getId(), $draft->getDraftId());

            $files2delete = $form->getInput('del_file');
            if (is_array($files2delete) && $files2delete !== []) {
                $draftFileData->unlinkFilesByMD5Filenames($files2delete);
            }

            if ($this->objProperties->isFileUploadAllowed()) {
                $file = $_FILES['userfile'];
                if (is_array($file) && !empty($file)) {
                    $draftFileData->storeUploadedFile($file);
                }
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('save_draft_successfully'), true);
            $this->ctrl->clearParameters($this);
            $this->ctrl->redirect($this, 'showThreads');
        }

        $form->setValuesByPost();
        $this->ctrl->setParameter($this, 'hist_check', 0);
        $this->ctrl->setParameter($this, 'draft_id', $draftId);
        $this->editThreadDraftObject($form);
    }

    public function saveTopLevelDraftObject(): void
    {
        $this->saveAsDraftObject();
    }

    public function saveAsDraftObject(): void
    {
        $ref_id = $this->retrieveRefId();
        $thr_pk = $this->retrieveThrPk();

        $del_file = [];
        if ($this->http->wrapper()->post()->has('del_file')) {
            $del_file = $this->http->wrapper()->post()->retrieve(
                'del_file',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
            );
        }
        $draft_id = null;
        if ($this->http->wrapper()->post()->has('draft_id')) {
            $draft_id = $this->http->wrapper()->post()->retrieve(
                'draft_id',
                $this->refinery->kindlyTo()->string()
            );
        }

        if ($this->objCurrentTopic->getId() === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('frm_action_not_possible_thr_deleted'), true);
            $this->ctrl->redirect($this);
        }

        if ($this->objCurrentTopic->isClosed()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('frm_action_not_possible_thr_closed'), true);
            $this->ctrl->redirect($this);
        }

        $autosave_draft_id = 0;
        if (isset($draft_id) && ilForumPostDraft::isAutoSavePostDraftAllowed()) {
            $autosave_draft_id = (int) $draft_id;
        }
        $oReplyEditForm = $this->getReplyEditForm();
        if ($oReplyEditForm->checkInput()) {
            if ($this->objCurrentPost->getId() === 0) {
                $this->requestAction = '';
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('frm_action_not_possible_parent_deleted'), true);
                $this->viewThreadObject();
                return;
            }

            $oForumObjects = $this->getForumObjects();
            $frm = $oForumObjects['frm'];
            $frm->setMDB2WhereCondition(' top_frm_fk = %s ', ['integer'], [$frm->getForumId()]);
            $topicData = $frm->getOneTopic();

            if ($this->requestAction === 'ready_showreply') {
                if (!$this->access->checkAccess('add_reply', '', $ref_id)) {
                    $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
                }

                $user_alias = ilForumUtil::getPublicUserAlias(
                    $oReplyEditForm->getInput('alias'),
                    $this->objProperties->isAnonymized()
                );

                if ($autosave_draft_id === 0) {
                    $draftObj = new ilForumPostDraft();
                } else {
                    $draftObj = ilForumPostDraft::newInstanceByDraftId($autosave_draft_id);
                }
                $draftObj->setForumId($topicData->getTopPk());
                $draftObj->setThreadId($this->objCurrentTopic->getId());
                $draftObj->setPostId($this->objCurrentPost->getId());

                $draftObj->setPostSubject($this->handleFormInput($oReplyEditForm->getInput('subject'), false));
                $draftObj->setPostMessage(ilRTE::_replaceMediaObjectImageSrc($oReplyEditForm->getInput('message')));
                $draftObj->setPostUserAlias($user_alias);
                $draftObj->setNotificationStatus((bool) $oReplyEditForm->getInput('notify'));
                $draftObj->setPostNotificationStatus((bool) $oReplyEditForm->getInput('notify_post'));

                $draftObj->setPostAuthorId($this->user->getId());
                $draftObj->setPostDisplayUserId(($this->objProperties->isAnonymized() ? 0 : $this->user->getId()));

                if ($autosave_draft_id === 0) {
                    $draft_id = $draftObj->saveDraft();
                } else {
                    $draftObj->updateDraft();
                    $draft_id = $draftObj->getDraftId();
                }

                if (ilForumPostDraft::isSavePostDraftAllowed()) {
                    $GLOBALS['ilAppEventHandler']->raise(
                        'Modules/Forum',
                        'savedAsDraft',
                        [
                            'draftObj' => $draftObj,
                            'obj_id' => $this->object->getId(),
                            'is_file_upload_allowed' => $this->objProperties->isFileUploadAllowed()
                        ]
                    );
                }

                if ($this->objProperties->isFileUploadAllowed()) {
                    $file = $_FILES['userfile'];
                    if (is_array($file) && !empty($file)) {
                        $oFDForumDrafts = new ilFileDataForumDrafts($this->object->getId(), $draftObj->getDraftId());
                        $oFDForumDrafts->storeUploadedFile($file);
                    }
                }

                // copy temporary media objects (frm~)
                ilForumUtil::moveMediaObjects(
                    $oReplyEditForm->getInput('message'),
                    'frm~d:html',
                    $draft_id,
                    'frm~d:html',
                    $draft_id
                );

                $frm_session_values = ilSession::get('frm');
                if (is_array($frm_session_values)) {
                    $frm_session_values[$thr_pk]['openTreeNodes'][] = $this->objCurrentPost->getId();
                }
                ilSession::set('frm', $frm_session_values);

                $this->tpl->setOnScreenMessage('success', $this->lng->txt('save_draft_successfully'), true);
                $this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
                $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
                $this->ctrl->redirect($this, 'viewThread');
            }
        } else {
            $oReplyEditForm->setValuesByPost();
            $this->requestAction = substr($this->requestAction, 6);
        }
        $this->viewThreadObject();
    }

    protected function editDraftObject(): void
    {
        if (ilForumPostDraft::isAutoSavePostDraftAllowed()) {
            $draftId = $this->retrieveDraftId();
            if ($this->checkDraftAccess($draftId)) {
                $this->doHistoryCheck($draftId);
            }
        }

        $this->viewThreadObject();
    }

    public function updateDraftObject(): void
    {
        $ref_id = $this->retrieveRefId();
        $draft_id = $this->retrieveDraftId();
        $thr_pk = $this->retrieveThrPk();

        if ($this->objCurrentTopic->getId() === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('frm_action_not_possible_thr_deleted'), true);
            $this->ctrl->redirect($this);
        }

        if ($this->objCurrentTopic->isClosed()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('frm_action_not_possible_thr_closed'), true);
            $this->ctrl->redirect($this);
        }

        if ($this->objCurrentPost->getId() === 0) {
            $this->requestAction = '';
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('frm_action_not_possible_parent_deleted'));
            $this->viewThreadObject();
            return;
        }

        $del_file = [];
        if ($this->http->wrapper()->post()->has('del_file')) {
            $del_file = $this->http->wrapper()->post()->retrieve(
                'del_file',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
            );
        }

        $oReplyEditForm = $this->getReplyEditForm();
        if ($oReplyEditForm->checkInput()) {
            // init objects
            $oForumObjects = $this->getForumObjects();
            $forumObj = $oForumObjects['forumObj'];

            if (!$this->user->isAnonymous() && in_array($this->requestAction, ['showdraft', 'editdraft'])) {
                if (!$this->access->checkAccess('add_reply', '', $ref_id)) {
                    $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
                }
                if (!$this->checkDraftAccess($draft_id)) {
                    $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
                }

                $user_alias = ilForumUtil::getPublicUserAlias(
                    $oReplyEditForm->getInput('alias'),
                    $this->objProperties->isAnonymized()
                );

                // generateDraft
                $update_draft = new ilForumPostDraft(
                    $this->user->getId(),
                    $this->objCurrentPost->getId(),
                    $draft_id
                );

                $update_draft->setPostSubject($this->handleFormInput($oReplyEditForm->getInput('subject'), false));
                $update_draft->setPostMessage(ilRTE::_replaceMediaObjectImageSrc(
                    $oReplyEditForm->getInput('message')
                ));
                $update_draft->setPostUserAlias($user_alias);
                $update_draft->setNotificationStatus((bool) $oReplyEditForm->getInput('notify'));
                $update_draft->setUpdateUserId($this->user->getId());
                $update_draft->setPostAuthorId($this->user->getId());
                $update_draft->setPostDisplayUserId(($this->objProperties->isAnonymized() ? 0 : $this->user->getId()));

                $update_draft->updateDraft();

                if (ilForumPostDraft::isSavePostDraftAllowed()) {
                    $GLOBALS['ilAppEventHandler']->raise(
                        'Modules/Forum',
                        'updatedDraft',
                        [
                            'draftObj' => $update_draft,
                            'obj_id' => $this->object->getId(),
                            'is_file_upload_allowed' => $this->objProperties->isFileUploadAllowed()
                        ]
                    );
                }

                $uploadedObjects = ilObjMediaObject::_getMobsOfObject('frm~:html', $this->user->getId());

                foreach ($uploadedObjects as $mob) {
                    ilObjMediaObject::_removeUsage($mob, 'frm~:html', $this->user->getId());
                    ilObjMediaObject::_saveUsage($mob, 'frm~d:html', $update_draft->getDraftId());
                }
                ilForumUtil::saveMediaObjects(
                    $oReplyEditForm->getInput('message'),
                    'frm~d:html',
                    $update_draft->getDraftId()
                );

                $oFDForumDrafts = new ilFileDataForumDrafts($forumObj->getId(), $update_draft->getDraftId());

                $file2delete = $oReplyEditForm->getInput('del_file');
                if (is_array($file2delete) && count($file2delete)) {
                    $oFDForumDrafts->unlinkFilesByMD5Filenames($file2delete);
                }

                if ($this->objProperties->isFileUploadAllowed()) {
                    $file = $_FILES['userfile'];
                    if (is_array($file) && !empty($file)) {
                        $oFDForumDrafts->storeUploadedFile($file);
                    }
                }

                $frm_session_values = ilSession::get('frm');
                if (is_array($frm_session_values)) {
                    $frm_session_values[$thr_pk]['openTreeNodes'][] = $this->objCurrentPost->getId();
                }
                ilSession::set('frm', $frm_session_values);
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('save_draft_successfully'), true);
                $this->ctrl->clearParameters($this);
                $this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
                $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
                $this->ctrl->setParameter($this, 'draft_id', $update_draft->getDraftId());
            }
        } else {
            $this->ctrl->clearParameters($this);
            $this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
            $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
            $this->ctrl->setParameter($this, 'draft_id', $draft_id);
            $this->ctrl->setParameter($this, 'action', 'editdraft');
            $oReplyEditForm->setValuesByPost();
            $this->viewThreadObject();
            return;
        }
        $this->ctrl->clearParameters($this);
        $this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
        $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
        $this->ctrl->redirect($this, 'viewThread');
    }

    protected function deleteMobsOfDraft(int $draft_id, string $message): void
    {
        // remove usage of deleted media objects
        $oldMediaObjects = ilObjMediaObject::_getMobsOfObject('frm~d:html', $draft_id);
        $curMediaObjects = ilRTE::_getMediaObjects($message);
        foreach ($oldMediaObjects as $oldMob) {
            $found = false;
            foreach ($curMediaObjects as $curMob) {
                if ($oldMob === $curMob) {
                    $found = true;
                    break;
                }
            }
            if (!$found && ilObjMediaObject::_exists($oldMob)) {
                ilObjMediaObject::_removeUsage($oldMob, 'frm~d:html', $draft_id);
                $mob_obj = new ilObjMediaObject($oldMob);
                $mob_obj->delete();
            }
        }
    }

    protected function deleteSelectedDraft(ilForumPostDraft $draft_obj = null): void
    {
        $ref_id = $this->retrieveRefId();
        $draft_id = $this->retrieveDraftId();

        if (
            !$this->access->checkAccess('add_reply', '', $ref_id) ||
            $this->user->isAnonymous() ||
            ($draft_obj instanceof ilForumPostDraft && $this->user->getId() !== $draft_obj->getPostAuthorId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $post_id = $this->objCurrentPost->getId();
        if (!($draft_obj instanceof ilForumPostDraft)) {
            $draft_id_to_delete = $draft_id;
            $draft_obj = new ilForumPostDraft($this->user->getId(), $post_id, $draft_id_to_delete);

            if (!$draft_obj->getDraftId() || ($draft_obj->getDraftId() !== $draft_id_to_delete)) {
                $this->ctrl->clearParameters($this);
                $this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
                $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
                $this->ctrl->redirect($this, 'viewThread');
            }
        }

        $this->deleteMobsOfDraft($draft_obj->getDraftId(), $draft_obj->getPostMessage());

        $objFileDataForumDrafts = new ilFileDataForumDrafts(0, $draft_obj->getDraftId());
        $objFileDataForumDrafts->delete();

        if (ilForumPostDraft::isSavePostDraftAllowed()) {
            $GLOBALS['ilAppEventHandler']->raise(
                'Modules/Forum',
                'deletedDraft',
                [
                    'draftObj' => $draft_obj,
                    'obj_id' => $this->object->getId(),
                    'is_file_upload_allowed' => $this->objProperties->isFileUploadAllowed()
                ]
            );
        }
        $draft_obj->deleteDraft();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('delete_draft_successfully'), true);
        $this->ctrl->clearParameters($this);
        $this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
        $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
        $this->ctrl->redirect($this, 'viewThread');
    }

    protected function autosaveDraftAsyncObject(): void
    {
        if (
            $this->requestAction !== 'ready_showreply' &&
            $this->access->checkAccess('read', '', $this->object->getRefId()) &&
            $this->access->checkAccess('add_reply', '', $this->object->getRefId())
        ) {
            $action = new ilForumAutoSaveAsyncDraftAction(
                $this->user,
                $this->getReplyEditForm(),
                $this->objProperties,
                $this->objCurrentTopic,
                $this->objCurrentPost,
                function (string $message): string {
                    return $this->handleFormInput($message);
                },
                $this->retrieveDraftId(),
                ilObjForum::lookupForumIdByRefId($this->ref_id),
                ilUtil::stripSlashes($this->requestAction)
            );

            $this->http->saveResponse($this->http->response()->withBody(
                \ILIAS\Filesystem\Stream\Streams::ofString(json_encode(
                    $action->executeAndGetResponseObject(),
                    JSON_THROW_ON_ERROR
                ))
            ));
        }

        $this->http->sendResponse();
        $this->http->close();
    }

    protected function autosaveThreadDraftAsyncObject(): void
    {
        if (
            $this->requestAction !== 'ready_showreply' &&
            $this->access->checkAccess('read', '', $this->object->getRefId()) &&
            $this->access->checkAccess('add_thread', '', $this->object->getRefId())
        ) {
            $action = new ilForumAutoSaveAsyncDraftAction(
                $this->user,
                $this->buildThreadForm(),
                $this->objProperties,
                $this->objCurrentTopic,
                $this->objCurrentPost,
                function (string $message): string {
                    return $this->handleFormInput($message, false);
                },
                $this->retrieveDraftId(),
                ilObjForum::lookupForumIdByRefId($this->ref_id),
                ilUtil::stripSlashes($this->requestAction)
            );

            $this->http->saveResponse($this->http->response()->withBody(
                \ILIAS\Filesystem\Stream\Streams::ofString(json_encode(
                    $action->executeAndGetResponseObject(),
                    JSON_THROW_ON_ERROR
                ))
            ));
        }

        $this->http->sendResponse();
        $this->http->close();
    }

    private function renderSplitButton(
        ilTemplate $tpl,
        string $action,
        bool $is_post,
        ilForumPost $node,
        int $pageIndex = 0,
        ilForumPostDraft $draft = null
    ): void {
        $draft_id = $this->retrieveDraftId();

        $actions = [];
        if ($is_post) {
            if (($this->objCurrentPost->getId() !== $node->getId() || (
                !in_array($action, ['showreply', 'showedit', 'censor', 'delete'], true) &&
                !$this->displayConfirmPostActivation()
            )) && ($this->is_moderator || $node->isActivated() || $node->isOwner($this->user->getId()))) {
                if ($this->is_moderator && !$this->objCurrentTopic->isClosed() && !$node->isActivated()) {
                    $this->ctrl->setParameter($this, 'pos_pk', $node->getId());
                    $this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
                    $this->ctrl->setParameter($this, 'page', $pageIndex);
                    $this->ctrl->setParameter(
                        $this,
                        'orderby',
                        $this->getOrderByParam()
                    );
                    $actions['activate_post'] = $this->ctrl->getLinkTarget(
                        $this,
                        'askForPostActivation',
                        (string) $node->getId()
                    );
                    $this->ctrl->clearParameters($this);
                }
                if (
                    !$this->objCurrentTopic->isClosed() && $node->isActivated() && !$node->isCensored() &&
                    $this->access->checkAccess('add_reply', '', $this->object->getRefId())
                ) {
                    $this->ctrl->setParameter($this, 'action', 'showreply');
                    $this->ctrl->setParameter($this, 'pos_pk', $node->getId());
                    $this->ctrl->setParameter($this, 'page', $pageIndex);
                    $this->ctrl->setParameter(
                        $this,
                        'orderby',
                        $this->getOrderByParam()
                    );
                    $this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
                    $actions['reply_to_postings'] = $this->ctrl->getLinkTarget(
                        $this,
                        'viewThread',
                        'reply_' . $node->getId()
                    );
                    $this->ctrl->clearParameters($this);
                }
                if (
                    !$this->objCurrentTopic->isClosed() &&
                    !$node->isCensored() &&
                    !$this->user->isAnonymous() &&
                    ($node->isOwner($this->user->getId()) || $this->is_moderator)
                ) {
                    $this->ctrl->setParameter($this, 'action', 'showedit');
                    $this->ctrl->setParameter($this, 'pos_pk', $node->getId());
                    $this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
                    $this->ctrl->setParameter($this, 'page', $pageIndex);
                    $this->ctrl->setParameter(
                        $this,
                        'orderby',
                        $this->getOrderByParam()
                    );
                    $actions['edit'] = $this->ctrl->getLinkTarget($this, 'viewThread', (string) $node->getId());
                    $this->ctrl->clearParameters($this);
                }
                if (!$this->user->isAnonymous()) {
                    $this->ctrl->setParameter($this, 'pos_pk', $node->getId());
                    $this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
                    $this->ctrl->setParameter($this, 'page', $pageIndex);
                    $this->ctrl->setParameter(
                        $this,
                        'orderby',
                        $this->getOrderByParam()
                    );
                    $this->ctrl->setParameter($this, 'viewmode', $this->selectedSorting);

                    $read_undread_txt = 'frm_mark_as_read';
                    $read_undread_cmd = 'markPostRead';
                    if ($node->isPostRead()) {
                        $read_undread_txt = 'frm_mark_as_unread';
                        $read_undread_cmd = 'markPostUnread';
                    }
                    $actions[$read_undread_txt] = $this->ctrl->getLinkTarget(
                        $this,
                        $read_undread_cmd,
                        (string) $node->getId()
                    );

                    $this->ctrl->clearParameters($this);
                }
                if (!$node->isCensored()) {
                    $this->ctrl->setParameterByClass(ilForumExportGUI::class, 'print_post', $node->getId());
                    $this->ctrl->setParameterByClass(ilForumExportGUI::class, 'top_pk', $node->getForumId());
                    $this->ctrl->setParameterByClass(ilForumExportGUI::class, 'thr_pk', $node->getThreadId());

                    $actions['print'] = $this->ctrl->getLinkTargetByClass(ilForumExportGUI::class, 'printPost');

                    $this->ctrl->clearParameters($this);
                }
                if (
                    !$this->objCurrentTopic->isClosed() &&
                    !$this->user->isAnonymous() &&
                    ($this->is_moderator || ($node->isOwner($this->user->getId()) && !$node->hasReplies()))
                ) {
                    $this->ctrl->setParameter($this, 'pos_pk', $node->getId());
                    $this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
                    $this->ctrl->setParameter($this, 'page', $pageIndex);
                    $this->ctrl->setParameter(
                        $this,
                        'orderby',
                        $this->getOrderByParam()
                    );
                    $actions['delete'] = $this->ctrl->getFormAction($this, 'deletePosting');
                    $this->ctrl->clearParameters($this);
                }
                if ($this->is_moderator && !$this->objCurrentTopic->isClosed()) {
                    $this->ctrl->setParameter($this, 'pos_pk', $node->getId());
                    $this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
                    $this->ctrl->setParameter($this, 'page', $pageIndex);
                    $this->ctrl->setParameter(
                        $this,
                        'orderby',
                        $this->getOrderByParam()
                    );
                    if ($node->isCensored()) {
                        $this->ctrl->setParameter($this, 'action', 'viewThread');
                        $actions['frm_revoke_censorship'] = $this->ctrl->getFormAction($this, 'revokeCensorship');
                    } else {
                        $actions['frm_censorship'] = $this->ctrl->getFormAction($this, 'addCensorship');
                    }
                    $this->ctrl->clearParameters($this);
                }
            }
        } elseif ($draft_id !== $draft->getDraftId() || !in_array($action, ['deletedraft', 'editdraft'])) {
            // get actions for drafts
            $this->ctrl->setParameter($this, 'action', 'publishdraft');
            $this->ctrl->setParameter($this, 'pos_pk', $node->getId());
            $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
            $this->ctrl->setParameter($this, 'page', $pageIndex);
            $this->ctrl->setParameter($this, 'draft_id', $draft->getDraftId());
            $this->ctrl->setParameter(
                $this,
                'orderby',
                $this->getOrderByParam()
            );
            $actions['publish'] = $this->ctrl->getLinkTarget($this, 'publishSelectedDraft', (string) $node->getId());
            $this->ctrl->clearParameters($this);

            $this->ctrl->setParameter($this, 'action', 'editdraft');
            $this->ctrl->setParameter($this, 'pos_pk', $node->getId());
            $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
            $this->ctrl->setParameter($this, 'draft_id', $draft->getDraftId());
            $this->ctrl->setParameter($this, 'page', $pageIndex);
            $this->ctrl->setParameter(
                $this,
                'orderby',
                $this->getOrderByParam()
            );
            $actions['edit'] = $this->ctrl->getLinkTarget($this, 'editDraft', 'draft_edit_' . $draft->getDraftId());
            $this->ctrl->clearParameters($this);

            $this->ctrl->setParameter($this, 'pos_pk', $node->getId());
            $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
            $this->ctrl->setParameter($this, 'draft_id', $draft->getDraftId());
            $this->ctrl->setParameter($this, 'page', $pageIndex);
            $this->ctrl->setParameter(
                $this,
                'orderby',
                $this->getOrderByParam()
            );
            $actions['delete'] = $this->ctrl->getFormAction($this, 'deletePostingDraft');
            $this->ctrl->clearParameters($this);

            if ($draft_id !== 0 && $action === 'editdraft') {
                $actions = [];
            }
        }

        $tpl->setCurrentBlock('posts_row');
        if ($actions !== [] && !$this->objCurrentTopic->isClosed()) {
            $action_button = ilSplitButtonGUI::getInstance();

            $i = 0;
            foreach ($actions as $lng_id => $url) {
                if ($i === 0) {
                    $sb_item = ilLinkButton::getInstance();
                    $sb_item->setCaption($lng_id);
                    $sb_item->setUrl($url);

                    $action_button->setDefaultButton($sb_item);
                    ++$i;
                } else {
                    if ('frm_revoke_censorship' === $lng_id || 'frm_censorship' === $lng_id) {
                        $modalTemplate = new ilTemplate("tpl.forums_censor_modal.html", true, true, 'Modules/Forum');
                        $formID = str_replace('.', '_', uniqid('form', true));
                        $modalTemplate->setVariable('FORM_ID', $formID);

                        if ($node->isCensored()) {
                            $modalTemplate->setVariable('BODY', $this->lng->txt('forums_info_censor2_post'));
                        } else {
                            $modalTemplate->setVariable('BODY', $this->lng->txt('forums_info_censor_post'));
                            $modalTemplate->touchBlock('message');
                        }

                        $modalTemplate->setVariable('FORM_ACTION', $url);

                        $content = $this->uiFactory->legacy($modalTemplate->get());
                        $submitBtn = $this->uiFactory->button()->primary(
                            $this->lng->txt('submit'),
                            '#'
                        )->withOnLoadCode(
                            static function (string $id) use ($formID): string {
                                return "$('#$id').click(function() { $('#$formID').submit(); return false; });";
                            }
                        );
                        $modal = $this->uiFactory->modal()->roundtrip(
                            $this->lng->txt($lng_id),
                            $content
                        )->withActionButtons([$submitBtn]);
                        $sb_item = $this->uiFactory->button()->shy($this->lng->txt($lng_id), '#')->withOnClick(
                            $modal->getShowSignal()
                        );

                        $this->modalActionsContainer[] = $modal;

                        $action_button->addMenuItem(new ilUiLinkToSplitButtonMenuItemAdapter(
                            $sb_item,
                            $this->uiRenderer
                        ));
                        continue;
                    } elseif ('delete' === $lng_id) {
                        $modal = $this->uiFactory->modal()->interruptive(
                            $this->lng->txt($lng_id),
                            str_contains($url, 'deletePostingDraft') ?
                                $this->lng->txt('forums_info_delete_draft') :
                                $this->lng->txt('forums_info_delete_post'),
                            $url
                        )->withActionButtonLabel(
                            str_contains($url, 'deletePostingDraft') ? 'deletePostingDraft' : 'deletePosting'
                        );

                        $deleteAction = $this->uiFactory->button()->shy($this->lng->txt($lng_id), '#')->withOnClick(
                            $modal->getShowSignal()
                        );

                        $this->modalActionsContainer[] = $modal;

                        $action_button->addMenuItem(
                            new ilUiLinkToSplitButtonMenuItemAdapter($deleteAction, $this->uiRenderer)
                        );
                        continue;
                    }

                    $sb_item = ilLinkButton::getInstance();
                    $sb_item->setCaption($lng_id);
                    $sb_item->setUrl($url);

                    $action_button->addMenuItem(new ilButtonToSplitButtonMenuItemAdapter($sb_item));
                }
            }

            $tpl->setVariable('COMMANDS', $action_button->render());
        }
    }

    public function checkDraftAccess(int $draftId): bool
    {
        $draft = ilForumPostDraft::newInstanceByDraftId($draftId);
        if (
            $this->user->isAnonymous() || !$this->access->checkAccess('add_reply', '', $this->object->getRefId()) ||
            $this->user->getId() !== $draft->getPostAuthorId()
        ) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        return true;
    }

    private function doHistoryCheck(int $draftId): void
    {
        if (!$this->checkDraftAccess($draftId)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!ilForumPostDraft::isAutoSavePostDraftAllowed()) {
            return;
        }

        iljQueryUtil::initjQuery();
        $draftsFromHistory = ilForumDraftsHistory::getInstancesByDraftId($draftId);
        if ($draftsFromHistory !== []) {
            $modal = ilModalGUI::getInstance();
            $modal->setHeading($this->lng->txt('restore_draft_from_autosave'));
            $modal->setId('frm_autosave_restore');
            $form_tpl = new ilTemplate('tpl.restore_thread_draft.html', true, true, 'Modules/Forum');

            foreach ($draftsFromHistory as $history_instance) {
                $accordion = new ilAccordionGUI();
                $accordion->setId('acc_' . $history_instance->getHistoryId());

                $form_tpl->setCurrentBlock('list_item');
                $message = ilRTE::_replaceMediaObjectImageSrc($history_instance->getPostMessage(), 1);

                $history_date = ilDatePresentation::formatDate(new ilDateTime(
                    $history_instance->getDraftDate(),
                    IL_CAL_DATETIME
                ));
                $this->ctrl->setParameter($this, 'history_id', $history_instance->getHistoryId());
                $header = $history_date . ' - ' . $history_instance->getPostSubject();
                $accordion->addItem($header, $message . $this->uiRenderer->render(
                    $this->uiFactory->button()->standard(
                        $this->lng->txt('restore'),
                        $this->ctrl->getLinkTarget($this, 'restoreFromHistory')
                    )
                ));

                $form_tpl->setVariable('ACC_AUTO_SAVE', $accordion->getHTML());
                $form_tpl->parseCurrentBlock();
            }

            $form_tpl->setVariable('RESTORE_DATA_EXISTS', 'found_threat_history_to_restore');
            $modal->setBody($form_tpl->get());
            ilModalGUI::initJS();
            $this->modal_history = $modal->getHTML();
        } else {
            ilForumPostDraft::createDraftBackup($draftId);
        }
    }

    private function renderPostingForm(ilTemplate $tpl, ilForum $frm, ilForumPost $node, string $action): void
    {
        $ref_id = $this->retrieveRefId();
        $draft_id = $this->retrieveDraftId();

        if (
            $action === 'showedit' && (
                (!$this->is_moderator && !$node->isOwner($this->user->getId())) ||
                $this->user->isAnonymous() ||
                $node->isCensored()
            )
        ) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        } elseif ($action === 'showreply' && !$this->access->checkAccess('add_reply', '', $ref_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $tpl->setVariable('REPLY_ANKER', 'reply_' . $this->objCurrentPost->getId());
        $oEditReplyForm = $this->getReplyEditForm();
        $subject = '';
        if ($action !== 'editdraft') {
            switch ($this->objProperties->getSubjectSetting()) {
                case 'add_re_to_subject':
                    $subject = $this->getModifiedReOnSubject(true);
                    break;

                case 'preset_subject':
                    $subject = $this->objCurrentPost->getSubject();
                    break;

                case 'empty_subject':
                    $subject = '';
                    break;
            }
        }

        switch ($action) {
            case 'showreply':
                if ($this->ctrl->getCmd() === 'savePost' || $this->ctrl->getCmd() === 'saveAsDraft') {
                    $oEditReplyForm->setValuesByPost();
                } elseif ($this->ctrl->getCmd() === 'quotePost') {
                    $authorinfo = new ilForumAuthorInformation(
                        $node->getPosAuthorId(),
                        $node->getDisplayUserId(),
                        (string) $node->getUserAlias(),
                        (string) $node->getImportName()
                    );

                    $oEditReplyForm->setValuesByPost();
                    $oEditReplyForm->getItemByPostVar('message')->setValue(
                        ilRTE::_replaceMediaObjectImageSrc(
                            $frm->prepareText(
                                $node->getMessage(),
                                1,
                                $authorinfo->getAuthorName()
                            ) . "\n" . $oEditReplyForm->getInput('message'),
                            1
                        )
                    );
                } else {
                    $oEditReplyForm->setValuesByArray([
                        'draft_id' => $draft_id,
                        'alias' => '',
                        'subject' => $subject,
                        'message' => '',
                        'notify' => 0,
                        'userfile' => '',
                        'del_file' => []
                    ]);
                }

                $this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
                $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());

                $jsTpl = new ilTemplate('tpl.forum_post_quoation_ajax_handler.js', true, true, 'Modules/Forum');
                $jsTpl->setVariable(
                    'IL_FRM_QUOTE_CALLBACK_SRC',
                    $this->ctrl->getLinkTarget($this, 'getQuotationHTMLAsynch', '', true)
                );
                $this->ctrl->clearParameters($this);
                $this->tpl->addOnLoadCode($jsTpl->get());
                break;

            case 'showedit':
                if ($this->ctrl->getCmd() === 'savePost') {
                    $oEditReplyForm->setValuesByPost();
                } else {
                    $oEditReplyForm->setValuesByArray([
                        'alias' => '',
                        'subject' => $this->objCurrentPost->getSubject(),
                        'message' => ilRTE::_replaceMediaObjectImageSrc($frm->prepareText(
                            $this->objCurrentPost->getMessage(),
                            2
                        ), 1),
                        'notify' => $this->objCurrentPost->isNotificationEnabled(),
                        'userfile' => '',
                        'del_file' => [],
                        'draft_id' => $draft_id
                    ]);
                }

                $this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getParentId());
                $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
                $jsTpl = new ilTemplate('tpl.forum_post_quoation_ajax_handler.js', true, true, 'Modules/Forum');
                $jsTpl->setVariable(
                    'IL_FRM_QUOTE_CALLBACK_SRC',
                    $this->ctrl->getLinkTarget($this, 'getQuotationHTMLAsynch', '', true)
                );
                $this->ctrl->clearParameters($this);
                $this->tpl->addOnLoadCode($jsTpl->get());
                break;

            case 'editdraft':
                if (in_array($this->ctrl->getCmd(), ['saveDraft', 'updateDraft', 'publishDraft'])) {
                    $oEditReplyForm->setValuesByPost();
                } elseif ($draft_id > 0) {
                    /** * @var object $draftObjects ilForumPost */
                    $draftObject = new ilForumPostDraft(
                        $this->user->getId(),
                        $this->objCurrentPost->getId(),
                        $draft_id
                    );
                    $oEditReplyForm->setValuesByArray([
                        'alias' => $draftObject->getPostUserAlias(),
                        'subject' => $draftObject->getPostSubject(),
                        'message' => ilRTE::_replaceMediaObjectImageSrc($frm->prepareText(
                            $draftObject->getPostMessage(),
                            2
                        ), 1),
                        'notify' => $draftObject->isNotificationEnabled(),
                        'userfile' => '',
                        'del_file' => [],
                        'draft_id' => $draft_id
                    ]);
                }

                $this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
                $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());

                $jsTpl = new ilTemplate('tpl.forum_post_quoation_ajax_handler.js', true, true, 'Modules/Forum');
                $jsTpl->setVariable(
                    'IL_FRM_QUOTE_CALLBACK_SRC',
                    $this->ctrl->getLinkTarget($this, 'getQuotationHTMLAsynch', '', true)
                );
                $this->ctrl->clearParameters($this);
                $this->tpl->addOnLoadCode($jsTpl->get());
                break;
        }

        $this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
        $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
        $this->ctrl->setParameter($this, 'page', (int) ($this->httpRequest->getQueryParams()['page'] ?? 0));
        $this->ctrl->setParameter(
            $this,
            'orderby',
            $this->getOrderByParam()
        );
        $this->ctrl->setParameter(
            $this,
            'action',
            ilUtil::stripSlashes($this->requestAction)
        );
        if ($action !== 'editdraft') {
            $tpl->setVariable('FORM', $oEditReplyForm->getHTML());
        }
        $this->ctrl->clearParameters($this);
    }

    private function getResetLimitedViewInfo(): string
    {
        $this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());

        $buttons = [
            $this->uiFactory->button()->standard(
                $this->lng->txt('reset_limited_view_button'),
                $this->ctrl->getLinkTarget($this, 'resetLimitedView')
            )
        ];

        return $this->uiRenderer->render(
            $this->uiFactory
                ->messageBox()
                ->info($this->lng->txt('reset_limited_view_info'))
                ->withButtons($buttons)
        );
    }

    private function getOrderByParam(): string
    {
        $order_by = '';
        if ($this->http->wrapper()->query()->has('orderby')) {
            $order_by = $this->http->wrapper()->query()->retrieve(
                'orderby',
                $this->refinery->kindlyTo()->string()
            );
        }

        return ilUtil::stripSlashes($order_by);
    }
}
