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

use ILIAS\Data\ObjectId;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

abstract class ilMailSearchObjectGUI implements ilCtrlSecurityInterface
{
    public const string CONTEXT_MAIL = 'mail';
    public const string CONTEXT_WORKSPACE = 'wsp';
    public const string CMD_SHOW_MY_OBJECTS = 'showMyObjects';
    public const string CMD_HANDLE_MAIL_SEARCH_OBJECT_ACTIONS = 'handleMailSearchObjectActions';
    public const string CMD_CANCEL = 'cancel';
    public const string ACTION_MAIL_OBJECTS = 'mailObjects';
    public const string ACTION_MAIL_MEMBERS = 'mailMembers';
    public const string ACTION_SHARE_OBJECTS = 'shareObjects';
    public const string ACTION_SHARE_MEMBERS = 'shareMembers';
    public const string ACTION_SHOW_MEMBERS = 'showMembers';

    private readonly ilTabsGUI $tabs;
    protected readonly GlobalHttpState $http;
    protected readonly Refinery $refinery;
    protected readonly ilGlobalTemplateInterface $tpl;
    protected readonly ilCtrlInterface $ctrl;
    protected readonly ilLanguage $lng;
    protected readonly ilObjUser $user;
    protected readonly ilErrorHandling $error;
    protected readonly ilRbacSystem $rbacsystem;
    protected readonly ilRbacReview $rbacreview;
    protected readonly ilTree $tree;
    protected readonly ilObjectDataCache $cache;
    protected readonly ilFormatMail $umail;
    protected readonly bool $mailing_allowed;
    protected readonly Factory $ui_factory;
    protected readonly Renderer $ui_renderer;
    private ?string $context = null;

    /**
     * @throws ilCtrlException
     */
    public function __construct(
        protected readonly ilPortfolioAccessHandler|ilWorkspaceAccessHandler|null $wsp_access_handler = null,
        protected readonly ?int $wsp_node_id = null,
    ) {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->error = $DIC['ilErr'];
        $this->rbacsystem = $DIC->rbac()->system();
        $this->rbacreview = $DIC->rbac()->review();
        $this->tree = $DIC->repositoryTree();
        $this->cache = $DIC['ilObjDataCache'];
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->tabs = $DIC->tabs();

        $this->ctrl->saveParameter($this, 'mobj_id');
        $this->ctrl->saveParameter($this, 'ref');

        $this->mailing_allowed = $this->rbacsystem->checkAccess(
            'internal_mail',
            new ilMail($this->user->getId())->getMailObjectReferenceId(),
        );

        $this->umail = new ilFormatMail($this->user->getId());

        $this->lng->loadLanguageModule('mail');
    }

    abstract public function getObjectType(): string;

    abstract public function getObjectTypeLabel(): string;

    abstract public function getSearchTableTitle(): string;

    abstract public function doesExposeMembers(ilObject $object): bool;

    /**
     * @return string[] Returns an array like ['il_crs_member_', 'il_crs_tutor', ...]
     */
    abstract protected function getLocalDefaultRolePrefixes(): array;

    public function getUnsafeGetCommands(): array
    {
        return [
            self::CMD_HANDLE_MAIL_SEARCH_OBJECT_ACTIONS,
        ];
    }

    public function getSafePostCommands(): array
    {
        return [];
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): bool
    {
        $forward_class = $this->ctrl->getNextClass($this) ?? '';
        switch (strtolower($forward_class)) {
            case strtolower(ilBuddySystemGUI::class):
                if (!ilBuddySystem::getInstance()->isEnabled()) {
                    $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
                }

                $obj_ids = $this->retrieveObjectIdsFromQuery();
                $this->ctrl->setParameter($this, 'contact_mailinglist_search_action', self::ACTION_SHOW_MEMBERS);
                if ($obj_ids !== []) {
                    $this->ctrl->setParameter(
                        $this,
                        'contact_mailinglist_search_obj_ids',
                        implode(',', $obj_ids)
                    );
                }
                $this->ctrl->setReturn($this, self::CMD_HANDLE_MAIL_SEARCH_OBJECT_ACTIONS);
                $this->ctrl->forwardCommand(new ilBuddySystemGUI());
                break;

            default:
                match ($this->ctrl->getCmd()) {
                    self::CMD_HANDLE_MAIL_SEARCH_OBJECT_ACTIONS => $this->handleMailSearchObjectActions(),
                    self::CMD_CANCEL => $this->cancel(),
                    default => $this->showMyObjects(),
                };
                break;
        }

        return true;
    }

    public function getContext(): string
    {
        if ($this->context === null) {
            $context = $this->http->wrapper()->query()->retrieve(
                'ref',
                $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->string(),
                    $this->refinery->always(self::CONTEXT_MAIL)
                ]),
            );
            $this->context = in_array($context, [self::CONTEXT_MAIL, self::CONTEXT_WORKSPACE], true)
                ? $context
                : self::CONTEXT_MAIL;
        }

        return $this->context;
    }

    /**
     * @param int[] $a_obj_ids
     */
    protected function addPermission(array $a_obj_ids): void
    {
        if ($this->wsp_access_handler === null || $this->wsp_node_id === null) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
        }

        $added = $this->wsp_access_handler->addMissingPermissionForObjects($this->wsp_node_id, $a_obj_ids);

        if ($added) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('wsp_share_success'), true);
        }
        $this->ctrl->redirectByClass(ilWorkspaceAccessGUI::class, 'share');
    }

    private function mailMembers(): void
    {
        $members = [];

        $usr_ids = $this->resolveUserIds(
            $this->retrieveIdsFromQuery('contact_mailinglist_search_members_ids')
        );

        $mail_data = $this->umail->retrieveFromStage();
        foreach ($usr_ids as $usr_id) {
            $login = ilObjUser::_lookupLogin($usr_id);
            if (!$this->umail->existsRecipient($login, (string) $mail_data['rcp_to'])) {
                $members[] = $login;
            }
        }

        $mail_data = $this->umail->appendSearchResult(array_unique($members), 'to');

        $this->umail->persistToStage(
            (int) $mail_data['user_id'],
            $mail_data['rcp_to'],
            $mail_data['rcp_cc'],
            $mail_data['rcp_bcc'],
            $mail_data['m_subject'],
            $mail_data['m_message'],
            $mail_data['attachments'],
            $mail_data['use_placeholders'],
            $mail_data['tpl_ctx_id'],
            $mail_data['tpl_ctx_params']
        );

        $this->ctrl->setParameterByClass(ilMailGUI::class, 'type', ilMailFormGUI::MAIL_FORM_TYPE_SEARCH_RESULT);
        $this->ctrl->redirectByClass(ilMailGUI::class);
    }

    private function cancel(): void
    {
        $view = '';
        if ($this->http->wrapper()->query()->has('view')) {
            $view = $this->http->wrapper()->query()->retrieve('view', $this->refinery->kindlyTo()->string());
        }

        if ($view === 'myobjects' && $this->isDefaultRequestContext()) {
            $this->ctrl->returnToParent($this);
        } else {
            $this->showMyObjects();
        }
    }

    private function showMembers(): void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, self::CMD_SHOW_MY_OBJECTS)
        );

        $obj_ids = $this->retrieveObjectIdsFromQuery();

        if ($obj_ids === []) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_crs'));
            $this->showMyObjects();

            return;
        }

        foreach ($obj_ids as $obj_id) {
            /** @var ilObjGroup|ilObjCourse $object */
            $object = ilObjectFactory::getInstanceByObjId($obj_id);

            $ref_ids = array_keys(ilObject::_getAllReferences($object->getId()));
            $ref_id = $ref_ids[0];
            $object->setRefId($ref_id);

            if (!$this->doesExposeMembers($object)) {
                $this->tpl->setOnScreenMessage(
                    'info',
                    $this->lng->txt('mail_crs_list_members_not_available_for_at_least_one_crs')
                );
                $this->showMyObjects();

                return;
            }
        }

        $this->lng->loadLanguageModule($this->getObjectType());

        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));

        $this->ctrl->setParameter($this, 'view', $this->getObjectType() . '_members');
        if ($obj_ids !== []) {
            $this->ctrl->setParameter($this, 'search_' . $this->getObjectType(), implode(',', $obj_ids));
        }
        $this->tpl->setVariable('ACTION', $this->ctrl->getFormAction($this));
        $this->ctrl->clearParameters($this);
        $this->lng->loadLanguageModule($this->getObjectType());

        $searchTpl = new ilTemplate(
            'tpl.mail_search_template.html',
            true,
            true,
            'components/ILIAS/Contact'
        );

        $table = new MailSearchObjectMembershipsTable(
            $obj_ids,
            $this,
            $this->user->getId(),
            $this->ctrl,
            $this->lng,
            $this->ui_factory,
            $this->http,
            $this->refinery,
            $this->cache
        );

        if ($this->getContext() === self::CONTEXT_MAIL) {
            $table->setMailingAllowed($this->mailing_allowed);
        }

        if (count($obj_ids) > 0) {
            $searchTpl->setVariable('TXT_MARKED_ENTRIES', $this->lng->txt('marked_entries'));
        }

        $searchTpl->setVariable('TABLE', $this->ui_renderer->render($table->getComponent()));
        $this->tpl->setContent($searchTpl->get());

        if ($this->isDefaultRequestContext()) {
            $this->tpl->printToStdout();
        }
    }

    private function showMyObjects(): void
    {
        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));

        $search_tpl = new ilTemplate(
            'tpl.mail_search_template.html',
            true,
            true,
            'components/ILIAS/Contact'
        );

        $this->lng->loadLanguageModule('crs');

        $table = new MailSearchObjectsTable(
            $this->user,
            $this,
            $this->ctrl,
            $this->lng,
            $this->ui_factory,
            $this->http,
            $this->tree,
        );

        if ($this->getContext() === self::CONTEXT_MAIL) {
            $table->setMailingAllowed($this->mailing_allowed);
        }

        if ($table->getNumHiddenMembers() > 0) {
            $search_tpl->setCurrentBlock('caption_block');
            $search_tpl->setVariable(
                'TXT_LIST_MEMBERS_NOT_AVAILABLE',
                $this->lng->txt('mail_crs_list_members_not_available')
            );
            $search_tpl->parseCurrentBlock();
        }

        $search_tpl->setVariable('TXT_MARKED_ENTRIES', $this->lng->txt('marked_entries'));
        $search_tpl->setVariable('TABLE', $this->ui_renderer->render($table->getComponent()));
        $this->tpl->setContent($search_tpl->get());

        if ($this->isDefaultRequestContext()) {
            $this->tpl->printToStdout();
        }
    }

    private function isDefaultRequestContext(): bool
    {
        return $this->getContext() !== self::CONTEXT_WORKSPACE;
    }

    private function isLocalRoleTitle(string $title): bool
    {
        return array_any(
            $this->getLocalDefaultRolePrefixes(),
            static fn(string $local_role_prefix): bool => str_starts_with($title, $local_role_prefix),
        );
    }

    private function shareObjects(): void
    {
        $obj_ids = $this->resolveObjectIds(
            $this->retrieveIdsFromQuery('contact_mailinglist_search_obj_ids')
        );

        if ($obj_ids !== []) {
            $this->addPermission($obj_ids);
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_crs'));
            $this->showMyObjects();
        }
    }

    private function shareMembers(): void
    {
        $usr_ids = $this->resolveUserIds(
            $this->retrieveIdsFromQuery('contact_mailinglist_search_members_ids')
        );

        if ($usr_ids !== []) {
            $this->addPermission(array_unique($usr_ids));
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_one_entry'));
            $this->showMembers();
        }
    }

    private function mailObjects(): void
    {
        $members = [];
        $mail_data = $this->umail->retrieveFromStage();

        $obj_ids = $this->resolveObjectIds(
            $this->retrieveIdsFromQuery('contact_mailinglist_search_obj_ids')
        );

        foreach ($obj_ids as $obj_id) {
            $ref_ids = ilObject::_getAllReferences($obj_id);
            foreach ($ref_ids as $ref_id) {
                $can_send_mails = ilParticipants::canSendMailToMembers(
                    $ref_id,
                    $this->user->getId(),
                    ilMailGlobalServices::getMailObjectRefId()
                );

                if (!$can_send_mails) {
                    continue;
                }

                $roles = $this->rbacreview->getAssignableChildRoles($ref_id);
                foreach ($roles as $role) {
                    if ($this->isLocalRoleTitle($role['title'])) {
                        $recipient = new ilRoleMailboxAddress($role['obj_id'])->value();
                        if (!$this->umail->existsRecipient($recipient, (string) $mail_data['rcp_to'])) {
                            $members[] = $recipient;
                        }
                    }
                }
            }
        }

        $mail_data = $members !== [] ? $this->umail->appendSearchResult(
            array_unique($members),
            'to'
        ) : $this->umail->retrieveFromStage();

        $this->umail->persistToStage(
            (int) $mail_data['user_id'],
            $mail_data['rcp_to'],
            $mail_data['rcp_cc'],
            $mail_data['rcp_bcc'],
            $mail_data['m_subject'],
            $mail_data['m_message'],
            $mail_data['attachments'],
            $mail_data['use_placeholders'],
            $mail_data['tpl_ctx_id'],
            $mail_data['tpl_ctx_params']
        );

        $this->ctrl->setParameterByClass(ilMailGUI::class, 'type', ilMailFormGUI::MAIL_FORM_TYPE_SEARCH_RESULT);
        $this->ctrl->redirectByClass(ilMailGUI::class);
    }

    private function handleMailSearchObjectActions(): void
    {
        $query = $this->http->wrapper()->query();

        if (!$query->has('contact_mailinglist_search_action')) {
            $this->ctrl->redirect($this, self::CMD_SHOW_MY_OBJECTS);
            return;
        }

        $action = $query->retrieve('contact_mailinglist_search_action', $this->refinery->to()->string());

        if (
            in_array($action, [self::ACTION_MAIL_OBJECTS, self::ACTION_MAIL_MEMBERS], true)
            && !$this->isMailActionAllowed()
        ) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
        }

        if (
            in_array($action, [self::ACTION_SHARE_OBJECTS, self::ACTION_SHARE_MEMBERS], true)
            && !$this->isShareActionAllowed()
        ) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
        }

        match ($action) {
            self::ACTION_MAIL_OBJECTS => $this->mailObjects(),
            self::ACTION_MAIL_MEMBERS => $this->mailMembers(),
            self::ACTION_SHARE_OBJECTS => $this->shareObjects(),
            self::ACTION_SHARE_MEMBERS => $this->shareMembers(),
            self::ACTION_SHOW_MEMBERS => $this->showMembers(),
            default => $this->ctrl->redirect($this, self::CMD_SHOW_MY_OBJECTS),
        };
    }

    private function isMailActionAllowed(): bool
    {
        return $this->getContext() === self::CONTEXT_MAIL && $this->mailing_allowed;
    }

    private function isShareActionAllowed(): bool
    {
        return $this->getContext() === self::CONTEXT_WORKSPACE
            && $this->wsp_access_handler !== null
            && $this->wsp_node_id !== null;
    }

    /**
     * @return list<int>
     */
    private function retrieveObjectIdsFromQuery(): array
    {
        $obj_ids = $this->resolveObjectIds(
            $this->retrieveIdsFromQuery('contact_mailinglist_search_obj_ids')
        );
        if ($obj_ids !== []) {
            return $obj_ids;
        }

        return $this->resolveObjectIds(
            $this->retrieveIdsFromQuery('search_' . $this->getObjectType())
        );
    }

    /**
     * @param list<int>|string $obj_ids
     * @return list<int>
     */
    private function resolveObjectIds(array|string $obj_ids): array
    {
        $own_obj_ids = $this->getOwnObjectIds();
        if ($obj_ids === 'ALL_OBJECTS') {
            return $own_obj_ids;
        }

        return array_values(array_intersect($obj_ids, $own_obj_ids));
    }

    /**
     * @return list<int>
     */
    private function getOwnObjectIds(): array
    {
        return array_values(array_filter(
            ilParticipants::_getMembershipByType($this->user->getId(), [$this->getObjectType()]),
            ilObject::_hasUntrashedReference(...),
        ));
    }

    /**
     * @param list<int>|string $usr_ids
     * @return list<int>
     */
    private function resolveUserIds(array|string $usr_ids): array
    {
        $allowed_usr_ids = $this->collectMemberIds($this->retrieveObjectIdsFromQuery());
        if ($usr_ids === 'ALL_OBJECTS') {
            return $allowed_usr_ids;
        }

        return array_values(array_intersect($usr_ids, $allowed_usr_ids));
    }

    /**
     * @param list<int> $obj_ids
     * @return list<int>
     */
    private function collectMemberIds(array $obj_ids): array
    {
        $usr_ids = [];
        foreach ($obj_ids as $obj_id) {
            $ref_ids = new ObjectId($obj_id)->toReferenceIds();
            if ($ref_ids === []) {
                continue;
            }

            foreach (ilParticipants::getInstance($ref_ids[0]->toInt())->getParticipants() as $participant) {
                if (ilObjUser::_lookupActive($participant)) {
                    $usr_ids[] = $participant;
                }
            }
        }

        return array_values(array_unique($usr_ids));
    }

    /**
     * @return list<int>|string
     */
    private function retrieveIdsFromQuery(string $key): array|string
    {
        return $this->http->wrapper()->query()->retrieve(
            $key,
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()),
                $this->refinery->custom()->transformation(
                    static function (mixed $value): array|string {
                        if ($value === ['ALL_OBJECTS']) {
                            return 'ALL_OBJECTS';
                        }
                        if (!is_string($value) || $value === '') {
                            throw new Exception('invalid ids');
                        }

                        return array_map(intval(...), explode(',', $value));
                    }
                ),
                $this->refinery->always([])
            ])
        );
    }
}
