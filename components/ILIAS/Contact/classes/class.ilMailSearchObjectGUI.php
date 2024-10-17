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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

abstract class ilMailSearchObjectGUI
{
    private ilTabsGUI $tabs;
    protected GlobalHttpState $http;
    protected Refinery $refinery;
    protected ?string $view = null;
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilObjUser $user;
    protected ilErrorHandling $error;
    protected ilRbacSystem $rbacsystem;
    protected ilRbacReview $rbacreview;
    protected ilTree $tree;
    protected ilObjectDataCache $cache;
    protected ilFormatMail $umail;
    protected bool $mailing_allowed;
    protected \ILIAS\UI\Factory $ui_factory;
    protected \ILIAS\UI\Renderer $ui_renderer;

    /**
     * @param ilWorkspaceAccessHandler|ilPortfolioAccessHandler|null $wsp_access_handler
     * @throws ilCtrlException
     */
    public function __construct(protected $wsp_access_handler = null, protected ?int $wsp_node_id = null)
    {
        global $DIC;

        $this->tpl = $DIC['tpl'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->lng = $DIC['lng'];
        $this->user = $DIC['ilUser'];
        $this->error = $DIC['ilErr'];
        $this->rbacsystem = $DIC['rbacsystem'];
        $this->rbacreview = $DIC['rbacreview'];
        $this->tree = $DIC['tree'];
        $this->cache = $DIC['ilObjDataCache'];
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->tabs = $DIC->tabs();

        $this->ctrl->saveParameter($this, 'mobj_id');
        $this->ctrl->saveParameter($this, 'ref');

        $mail = new ilMail($this->user->getId());
        $this->mailing_allowed = $this->rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId());

        $this->umail = new ilFormatMail($this->user->getId());

        $this->lng->loadLanguageModule('mail');
    }

    private function isDefaultRequestContext(): bool
    {
        return (
            !$this->http->wrapper()->query()->has('ref') ||
            $this->http->wrapper()->query()->retrieve('ref', $this->refinery->kindlyTo()->string()) !== 'wsp'
        );
    }

    private function getContext(): string
    {
        $context = 'mail';
        if ($this->http->wrapper()->query()->has('ref')) {
            $context = $this->http->wrapper()->query()->retrieve('ref', $this->refinery->kindlyTo()->string());
        }

        return $context;
    }

    private function isLocalRoleTitle(string $title): bool
    {
        foreach ($this->getLocalDefaultRolePrefixes() as $local_role_prefix) {
            if (str_starts_with($title, $local_role_prefix)) {
                return true;
            }
        }

        return false;
    }

    abstract protected function getObjectType(): string;

    /**
     * @return string[] Returns an array like ['il_crs_member_', 'il_crs_tutor', ...]
     */
    abstract protected function getLocalDefaultRolePrefixes(): array;

    protected function getRequestValue(string $key, \ILIAS\Refinery\Transformation $trafo, $default = null)
    {
        $value = $default;
        if ($this->http->wrapper()->query()->has($key)) {
            $value = $this->http->wrapper()->query()->retrieve($key, $trafo);
        }

        if ($this->http->wrapper()->post()->has($key)) {
            $value = $this->http->wrapper()->post()->retrieve($key, $trafo);
        }

        return $value;
    }

    /**
     * @param int[] $a_obj_ids
     */
    protected function addPermission(array $a_obj_ids): void
    {
        $added = $this->wsp_access_handler->addMissingPermissionForObjects($this->wsp_node_id, $a_obj_ids);

        if ($added) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('wsp_share_success'), true);
        }
        $this->ctrl->redirectByClass(ilWorkspaceAccessGUI::class, 'share');
    }

    private function shareObjects(): void
    {
        $obj_ids = $this->http->wrapper()->query()->retrieve(
            'contact_mailinglist_search_obj_ids',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->int()),
                $this->refinery->always([])
            ])
        );

        if ($obj_ids !== []) {
            $this->addPermission($obj_ids);
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_course'));
            $this->showMyObjects();
        }
    }

    private function shareMembers(): void
    {
        $usr_ids = $this->http->wrapper()->query()->retrieve(
            'contact_mailinglist_search_members_ids',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()),
                $this->refinery->always([])
            ])
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

        $obj_ids = $this->http->wrapper()->query()->retrieve(
            'contact_mailinglist_search_obj_ids',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()),
                $this->refinery->always([])
            ])
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
                        $recipient = (new ilRoleMailboxAddress($role['obj_id']))->value();
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
            $mail_data['attachments'],
            $mail_data['rcp_to'],
            $mail_data['rcp_cc'],
            $mail_data['rcp_bcc'],
            $mail_data['m_subject'],
            $mail_data['m_message'],
            $mail_data['use_placeholders'],
            $mail_data['tpl_ctx_id'],
            $mail_data['tpl_ctx_params']
        );

        $this->ctrl->redirectToURL('ilias.php?baseClass=ilMailGUI&type=search_res');
    }

    public function mailMembers(): void
    {
        $members = [];

        $usr_ids = $this->http->wrapper()->query()->retrieve(
            'contact_mailinglist_search_members_ids',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()),
                $this->refinery->always([])
            ])
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
            $mail_data['attachments'],
            $mail_data['rcp_to'],
            $mail_data['rcp_cc'],
            $mail_data['rcp_bcc'],
            $mail_data['m_subject'],
            $mail_data['m_message'],
            $mail_data['use_placeholders'],
            $mail_data['tpl_ctx_id'],
            $mail_data['tpl_ctx_params']
        );

        $this->ctrl->redirectToURL('ilias.php?baseClass=ilMailGUI&type=search_res');
    }

    public function cancel(): void
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

    public function showMembers(): void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, 'showMyObjects')
        );

        $obj_ids = $this->http->wrapper()->query()->retrieve(
            'contact_mailinglist_search_obj_ids',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()),
                $this->refinery->always([])
            ])
        );

        if ($obj_ids === []) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_course'));
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

        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));

        $this->ctrl->setParameter($this, 'view', $this->getObjectType() . '_members');
        if ($obj_ids !== []) {
            $this->ctrl->setParameter($this, 'search_' . $this->getObjectType(), implode(',', $obj_ids));
        }
        $this->tpl->setVariable('ACTION', $this->ctrl->getFormAction($this));
        $this->ctrl->clearParameters($this);
        $this->lng->loadLanguageModule($this->getObjectType());

        $context = $this->getContext();
        $searchTpl = new ilTemplate(
            'tpl.mail_search_template.html',
            true,
            true,
            'components/ILIAS/Contact'
        );

        $table = new MailSearchObjectMembershipsTable(
            $obj_ids,
            $this->getObjectType(),
            $context,
            $this->user->getId(),
            $this->ctrl,
            $this->lng,
            $this->ui_factory,
            $this->http,
            $this->cache
        );

        if ($context === 'mail') {
            $mail = new ilMail($this->user->getId());
            $table->setMailingAllowed(
                $this->rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId())
            );
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

    abstract protected function doesExposeMembers(ilObject $object): bool;

    public function showMyObjects(): void
    {
        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));

        $searchTpl = new ilTemplate(
            'tpl.mail_search_template.html',
            true,
            true,
            'components/ILIAS/Contact'
        );

        $this->lng->loadLanguageModule('crs');

        $context = $this->getContext();

        $table = new MailSearchObjectsTable(
            $this->user,
            $this->getObjectType(),
            $context,
            $this->ctrl,
            $this->lng,
            $this->ui_factory,
            $this->http,
            $this->tree,
            $this->rbacsystem
        );

        if ($context === 'mail') {
            $mail = new ilMail($this->user->getId());
            $table->setMailingAllowed(
                $this->rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId())
            );
        }

        if ($table->getNumHiddenMembers() > 0) {
            $searchTpl->setCurrentBlock('caption_block');
            $searchTpl->setVariable(
                'TXT_LIST_MEMBERS_NOT_AVAILABLE',
                $this->lng->txt('mail_crs_list_members_not_available')
            );
            $searchTpl->parseCurrentBlock();
        }

        $searchTpl->setVariable('TXT_MARKED_ENTRIES', $this->lng->txt('marked_entries'));
        $searchTpl->setVariable('TABLE', $this->ui_renderer->render($table->getComponent()));
        $this->tpl->setContent($searchTpl->get());

        if ($this->isDefaultRequestContext()) {
            $this->tpl->printToStdout();
        }
    }

    public function executeCommand(): bool
    {
        $forward_class = $this->ctrl->getNextClass($this) ?? '';
        switch (strtolower($forward_class)) {
            case strtolower(ilBuddySystemGUI::class):
                if (!ilBuddySystem::getInstance()->isEnabled()) {
                    $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
                }

                $this->ctrl->saveParameter($this, 'search_' . $this->getObjectType());

                $this->ctrl->setReturn($this, 'showMembers');
                $this->ctrl->forwardCommand(new ilBuddySystemGUI());
                break;

            default:
                if (!($cmd = $this->ctrl->getCmd())) {
                    $cmd = 'showMyObjects';
                }

                $this->$cmd();
                break;
        }

        return true;
    }

    private function handleMailSearchObjectActions(): void
    {
        $query = $this->http->wrapper()->query();

        if (!$query->has('contact_mailinglist_search_action')) {
            return;
        }

        $action = $query->retrieve('contact_mailinglist_search_action', $this->refinery->to()->string());

        switch ($action) {
            case 'mailObjects':
                $this->mailObjects();
                break;

            case 'mailMembers':
                $this->mailMembers();
                break;

            case 'shareObjects':
                $this->shareObjects();
                break;

            case 'shareMembers':
                $this->shareMembers();
                break;

            case 'showMembers':
                $this->showMembers();
                break;

            default:
                $this->ctrl->redirect($this, 'showMyObjects');
                break;
        }
    }
}
