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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

abstract class ilMailSearchObjectGUI
{
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

        $this->ctrl->saveParameter($this, 'mobj_id');
        $this->ctrl->saveParameter($this, 'ref');

        $mail = new ilMail($this->user->getId());
        $this->mailing_allowed = $this->rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId());

        $this->umail = new ilFormatMail($this->user->getId());
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
        $existing = $this->wsp_access_handler->getPermissions($this->wsp_node_id);
        $added = false;
        foreach ($a_obj_ids as $object_id) {
            if (!in_array($object_id, $existing, true)) {
                $added = $this->wsp_access_handler->addPermission($this->wsp_node_id, $object_id);
            }
        }

        if ($added) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('wsp_share_success'), true);
        }
        $this->ctrl->redirectByClass(ilWorkspaceAccessGUI::class, 'share');
    }

    protected function share(): void
    {
        $view = '';
        if ($this->http->wrapper()->query()->has('view')) {
            $view = $this->http->wrapper()->query()->retrieve('view', $this->refinery->kindlyTo()->string());
        }

        if ($view === 'myobjects') {
            $obj_ids = [];
            if ($this->http->wrapper()->query()->has('search_' . $this->getObjectType())) {
                $obj_ids = [
                    $this->http->wrapper()->query()->retrieve(
                        'search_' . $this->getObjectType(),
                        $this->refinery->kindlyTo()->int()
                    )
                ];
            } elseif ($this->http->wrapper()->post()->has('search_' . $this->getObjectType())) {
                $obj_ids = $this->http->wrapper()->post()->retrieve(
                    'search_' . $this->getObjectType(),
                    $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
                );
            }

            if ($obj_ids !== []) {
                $this->addPermission($obj_ids);
            } else {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_course'));
                $this->showMyObjects();
            }
        } elseif ($view === $this->getObjectType() . '_members') {
            $usr_ids = [];
            if ($this->http->wrapper()->query()->has('search_members')) {
                $usr_ids = [
                    $this->http->wrapper()->query()->retrieve(
                        'search_members',
                        $this->refinery->kindlyTo()->int()
                    )
                ];
            } elseif ($this->http->wrapper()->post()->has('search_members')) {
                $usr_ids = $this->http->wrapper()->post()->retrieve(
                    'search_members',
                    $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
                );
            }

            if ($usr_ids !== []) {
                $this->addPermission($usr_ids);
            } else {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_one_entry'));
                $this->showMembers();
            }
        } else {
            $this->showMyObjects();
        }
    }

    protected function mail(): void
    {
        $view = '';
        if ($this->http->wrapper()->query()->has('view')) {
            $view = $this->http->wrapper()->query()->retrieve('view', $this->refinery->kindlyTo()->string());
        }

        if ($view === 'myobjects') {
            $obj_ids = [];
            if ($this->http->wrapper()->query()->has('search_' . $this->getObjectType())) {
                $obj_ids = [
                    $this->http->wrapper()->query()->retrieve(
                        'search_' . $this->getObjectType(),
                        $this->refinery->kindlyTo()->int()
                    )
                ];
            } elseif ($this->http->wrapper()->post()->has('search_' . $this->getObjectType())) {
                $obj_ids = $this->http->wrapper()->post()->retrieve(
                    'search_' . $this->getObjectType(),
                    $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
                );
            }

            if ($obj_ids !== []) {
                $this->mailObjects();
            } else {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_course'));
                $this->showMyObjects();
            }
        } elseif ($view === $this->getObjectType() . '_members') {
            $usr_ids = [];
            if ($this->http->wrapper()->query()->has('search_members')) {
                $usr_ids = [
                    $this->http->wrapper()->query()->retrieve(
                        'search_members',
                        $this->refinery->kindlyTo()->int()
                    )
                ];
            } elseif ($this->http->wrapper()->post()->has('search_members')) {
                $usr_ids = $this->http->wrapper()->post()->retrieve(
                    'search_members',
                    $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
                );
            }

            if ($usr_ids !== []) {
                $this->mailMembers();
            } else {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_one_entry'));
                $this->showMembers();
            }
        } else {
            $this->showMyObjects();
        }
    }

    protected function mailObjects(): void
    {
        $members = [];
        $mail_data = $this->umail->getSavedData();

        $obj_ids = [];
        if ($this->http->wrapper()->query()->has('search_' . $this->getObjectType())) {
            $obj_ids = [
                $this->http->wrapper()->query()->retrieve(
                    'search_' . $this->getObjectType(),
                    $this->refinery->kindlyTo()->int()
                )
            ];
        } elseif ($this->http->wrapper()->post()->has('search_' . $this->getObjectType())) {
            $obj_ids = $this->http->wrapper()->post()->retrieve(
                'search_' . $this->getObjectType(),
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }

        foreach ($obj_ids as $obj_id) {
            $ref_ids = ilObject::_getAllReferences($obj_id);
            foreach ($ref_ids as $ref_id) {
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

        $mail_data = $members !== [] ? $this->umail->appendSearchResult(array_unique($members), 'to') : $this->umail->getSavedData();

        $this->umail->savePostData(
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
        $usr_ids = [];
        if ($this->http->wrapper()->query()->has('search_members')) {
            $usr_ids = [
                $this->http->wrapper()->query()->retrieve(
                    'search_members',
                    $this->refinery->kindlyTo()->int()
                )
            ];
        } elseif ($this->http->wrapper()->post()->has('search_members')) {
            $usr_ids = $this->http->wrapper()->post()->retrieve(
                'search_members',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }

        $mail_data = $this->umail->getSavedData();
        foreach ($usr_ids as $usr_id) {
            $login = ilObjUser::_lookupLogin($usr_id);
            if (!$this->umail->existsRecipient($login, (string) $mail_data['rcp_to'])) {
                $members[] = $login;
            }
        }
        $mail_data = $this->umail->appendSearchResult(array_unique($members), 'to');

        $this->umail->savePostData(
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
        $obj_ids = [];
        if ($this->http->wrapper()->query()->has('search_' . $this->getObjectType())) {
            $obj_ids = $this->refinery->kindlyTo()->listOf(
                $this->refinery->kindlyTo()->int()
            )->transform(explode(',', $this->http->wrapper()->query()->retrieve(
                'search_' . $this->getObjectType(),
                $this->refinery->kindlyTo()->string()
            )));
        } elseif ($this->http->wrapper()->post()->has('search_' . $this->getObjectType())) {
            $obj_ids = $this->http->wrapper()->post()->retrieve(
                'search_' . $this->getObjectType(),
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        } elseif (ilSession::get('search_' . $this->getObjectType())) {
            $obj_ids = $this->refinery->kindlyTo()->listOf(
                $this->refinery->kindlyTo()->int()
            )->transform(explode(',', ilSession::get('search_' . $this->getObjectType())));
            ilSession::set('search_' . $this->getObjectType(), '');
        }

        if ($obj_ids === []) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_course'));
            $this->showMyObjects();
            return;
        }

        foreach ($obj_ids as $obj_id) {
            /** @var ilObjGroup|ilObjCourse $object */
            $object = ilObjectFactory::getInstanceByObjId($obj_id);
            if (!$object->getShowMembers()) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_crs_list_members_not_available_for_at_least_one_crs'));
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

        $table = new ilMailSearchObjectMembershipsTableGUI(
            $this,
            $this->getObjectType(),
            $context,
            $obj_ids
        );
        $tableData = [];

        $searchTpl = new ilTemplate('tpl.mail_search_template.html', true, true, 'Services/Contact');
        foreach ($obj_ids as $obj_id) {
            $members_obj = ilParticipants::getInstanceByObjId($obj_id);
            $usr_ids = array_map('intval', ilUtil::_sortIds($members_obj->getParticipants(), 'usr_data', 'lastname', 'usr_id'));
            foreach ($usr_ids as $usr_id) {
                $user = new ilObjUser($usr_id);
                if (!$user->getActive()) {
                    continue;
                }

                $fullname = '';
                if (in_array(ilObjUser::_lookupPref($user->getId(), 'public_profile'), ['g', 'y'])) {
                    $fullname = $user->getLastname() . ', ' . $user->getFirstname();
                }

                $rowData = [
                    'members_id' => $user->getId(),
                    'members_login' => $user->getLogin(),
                    'members_name' => $fullname,
                    'members_crs_grp' => $this->cache->lookupTitle((int) $obj_id),
                    'search_' . $this->getObjectType() => $obj_id
                ];

                if ('mail' === $context && ilBuddySystem::getInstance()->isEnabled()) {
                    $relation = ilBuddyList::getInstanceByGlobalUser()->getRelationByUserId($user->getId());
                    $state_name = ilStr::convertUpperCamelCaseToUnderscoreCase($relation->getState()->getName());
                    $rowData['status'] = '';
                    if ($user->getId() !== $this->user->getId()) {
                        if ($relation->isOwnedByActor()) {
                            $rowData['status'] = $this->lng->txt('buddy_bs_state_' . $state_name . '_a');
                        } else {
                            $rowData['status'] = $this->lng->txt('buddy_bs_state_' . $state_name . '_p');
                        }
                    }
                }

                $tableData[] = $rowData;
            }
        }
        $table->setData($tableData);

        if ($tableData !== []) {
            $searchTpl->setVariable('TXT_MARKED_ENTRIES', $this->lng->txt('marked_entries'));
        }

        $searchTpl->setVariable('TABLE', $table->getHTML());
        $this->tpl->setContent($searchTpl->get());

        if ($this->isDefaultRequestContext()) {
            $this->tpl->printToStdout();
        }
    }

    abstract protected function doesExposeMembers(ilObject $object): bool;

    public function showMyObjects(): void
    {
        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));

        $searchTpl = new ilTemplate('tpl.mail_search_template.html', true, true, 'Services/Contact');

        $this->lng->loadLanguageModule('crs');

        $table = new ilMailSearchObjectsTableGUI(
            $this,
            $this->getObjectType(),
            $this->getContext()
        );
        $table->setId('search_' . $this->getObjectType() . '_tbl');

        $objs_ids = ilParticipants::_getMembershipByType($this->user->getId(), [$this->getObjectType()]);
        $counter = 0;
        $tableData = [];
        if ($objs_ids !== []) {
            $num_courses_hidden_members = 0;
            foreach ($objs_ids as $obj_id) {
                /** @var ilObjCourse|ilObjGroup $object */
                $object = ilObjectFactory::getInstanceByObjId($obj_id);

                $ref_ids = array_keys(ilObject::_getAllReferences($object->getId()));
                $ref_id = $ref_ids[0];
                $object->setRefId($ref_id);
                $showMemberListEnabled = $object->getShowMembers();

                if ($this->doesExposeMembers($object)) {
                    $participants = ilParticipants::getInstanceByObjId($object->getId());
                    $usr_ids = $participants->getParticipants();

                    foreach ($usr_ids as $key => $usr_id) {
                        $is_active = ilObjUser::_lookupActive($usr_id);
                        if (!$is_active) {
                            unset($usr_ids[$key]);
                        }
                    }
                    $usr_ids = array_values($usr_ids);

                    $hiddenMembers = false;
                    if (!$showMemberListEnabled) {
                        ++$num_courses_hidden_members;
                        $hiddenMembers = true;
                    }

                    $path_arr = $this->tree->getPathFull($object->getRefId(), $this->tree->getRootId());
                    $path = '';
                    foreach ($path_arr as $data) {
                        if ($path !== '') {
                            $path .= ' -> ';
                        }
                        $path .= $data['title'];
                    }
                    $path = $this->lng->txt('path') . ': ' . $path;

                    $current_selection_list = new ilAdvancedSelectionListGUI();
                    $current_selection_list->setListTitle($this->lng->txt('actions'));
                    $current_selection_list->setId('act_' . $counter);

                    $this->ctrl->setParameter($this, 'search_' . $this->getObjectType(), $object->getId());
                    $this->ctrl->setParameter($this, 'view', 'myobjects');

                    if ($this->isDefaultRequestContext()) {
                        if ($this->mailing_allowed) {
                            $current_selection_list->addItem(
                                $this->lng->txt('mail_members'),
                                '',
                                $this->ctrl->getLinkTarget($this, 'mail')
                            );
                        }
                    } else {
                        $current_selection_list->addItem(
                            $this->lng->txt('wsp_share_with_members'),
                            '',
                            $this->ctrl->getLinkTarget($this, 'share')
                        );
                    }
                    $current_selection_list->addItem(
                        $this->lng->txt('mail_list_members'),
                        '',
                        $this->ctrl->getLinkTarget($this, 'showMembers')
                    );

                    $this->ctrl->clearParameters($this);

                    $rowData = [
                        'OBJECT_ID' => $object->getId(),
                        'OBJECT_NAME' => $object->getTitle(),
                        'OBJECT_NO_MEMBERS' => count($usr_ids),
                        'OBJECT_PATH' => $path,
                        'COMMAND_SELECTION_LIST' => $current_selection_list->getHTML(),
                        'hidden_members' => $hiddenMembers,
                    ];
                    $counter++;
                    $tableData[] = $rowData;
                }
            }

            if ($num_courses_hidden_members > 0) {
                $searchTpl->setCurrentBlock('caption_block');
                $searchTpl->setVariable('TXT_LIST_MEMBERS_NOT_AVAILABLE', $this->lng->txt('mail_crs_list_members_not_available'));
                $searchTpl->parseCurrentBlock();
            }
        }

        $searchTpl->setVariable('TXT_MARKED_ENTRIES', $this->lng->txt('marked_entries'));

        $table->setData($tableData);
        $searchTpl->setVariable('TABLE', $table->getHTML());
        $this->tpl->setContent($searchTpl->get());

        if ($this->isDefaultRequestContext()) {
            $this->tpl->printToStdout();
        }
    }

    public function executeCommand(): bool
    {
        $forward_class = $this->ctrl->getNextClass($this);
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
}
