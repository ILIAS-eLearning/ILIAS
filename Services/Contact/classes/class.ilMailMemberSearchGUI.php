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

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilMailMemberSearchGUI
 * @author Nadia Matuschek <nmatuschek@databay.de>
 *
**/
class ilMailMemberSearchGUI
{
    private ServerRequestInterface $httpRequest;
    /** @var array{role_id: int, mailbox: string, form_option_title: string, default_checked: bool}[] */
    private array $mail_roles;
    /** @var ilObjGroupGUI|ilObjCourseGUI|ilMembershipGUI */
    private $gui;
    private ilAbstractMailMemberRoles $objMailMemberRoles;
    private ?ilParticipants $objParticipants;
    private ilCtrlInterface $ctrl;
    private ilGlobalTemplateInterface $tpl;
    private ilLanguage $lng;
    private ilAccessHandler $access;
    public int $ref_id;

    /**
     * ilMailMemberSearchGUI constructor.
     * @param ilObjGroupGUI|ilObjCourseGUI|ilMembershipGUI $gui
     * @param int $ref_id
     * @param ilAbstractMailMemberRoles $objMailMemberRoles
     */
    public function __construct(object $gui, int $ref_id, ilAbstractMailMemberRoles $objMailMemberRoles)
    {
        global $DIC;

        $this->ctrl = $DIC['ilCtrl'];
        $this->tpl = $DIC['tpl'];
        $this->lng = $DIC['lng'];
        $this->access = $DIC['ilAccess'];
        $this->httpRequest = $DIC->http()->request();

        $this->lng->loadLanguageModule('mail');
        $this->lng->loadLanguageModule('search');

        $this->gui = $gui;
        $this->ref_id = $ref_id;

        $this->objMailMemberRoles = $objMailMemberRoles;
        $this->mail_roles = $objMailMemberRoles->getMailRoles($ref_id);
    }

    
    public function executeCommand() : bool
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->ctrl->setReturn($this, '');

        switch ($cmd) {
            case 'sendMailToSelectedUsers':
                $this->sendMailToSelectedUsers();
                break;

            case 'showSelectableUsers':
                $this->showSelectableUsers();
                break;

            case 'nextMailForm':
                $this->nextMailForm();
                break;

            case 'cancel':
                $this->redirectToParentReferer();
                break;

            default:
                if (isset($this->httpRequest->getQueryParams()['returned_from_mail']) && $this->httpRequest->getQueryParams()['returned_from_mail'] === '1') {
                    $this->redirectToParentReferer();
                }
                $this->showSearchForm();
                break;
        }


        return true;
    }

    private function redirectToParentReferer() : void
    {
        $url = $this->getStoredReferer();
        $this->unsetStoredReferer();
        $this->ctrl->redirectToURL($url);
    }

    public function storeReferer() : void
    {
        $back_link = $this->ctrl->getParentReturn($this);

        if (isset($this->httpRequest->getServerParams()['HTTP_REFERER'])) {
            $referer = $this->httpRequest->getServerParams()['HTTP_REFERER'];
            $urlParts = parse_url($referer);

            if (isset($urlParts['path'])) {
                $url = ltrim(basename($urlParts['path']), '/');
                if (isset($urlParts['query'])) {
                    $url .= '?' . $urlParts['query'];
                }
                if ($url !== '') {
                    $back_link = $url;
                }
            }
        }

        ilSession::set('ilMailMemberSearchGUIReferer', $back_link);
    }

    private function getStoredReferer() : string
    {
        return (string) ilSession::get('ilMailMemberSearchGUIReferer');
    }
    
    private function unsetStoredReferer() : void
    {
        ilSession::set('ilMailMemberSearchGUIReferer', '');
    }

    protected function nextMailForm() : void
    {
        $form = $this->initMailToMembersForm();
        if ($form->checkInput()) {
            if ($form->getInput('mail_member_type') === 'mail_member_roles') {
                if (is_array($form->getInput('roles')) && count($form->getInput('roles')) > 0) {
                    $role_mail_boxes = [];
                    $roles = $form->getInput('roles');
                    foreach ($roles as $role_id) {
                        $mailbox = $this->objMailMemberRoles->getMailboxRoleAddress((int) $role_id);
                        $role_mail_boxes[] = $mailbox;
                    }

                    ilSession::set('mail_roles', $role_mail_boxes);

                    $this->ctrl->redirectToURL(ilMailFormCall::getRedirectTarget(
                        $this,
                        'showSearchForm',
                        ['type' => ilMailFormGUI::MAIL_FORM_TYPE_ROLE],
                        [
                            'type' => ilMailFormGUI::MAIL_FORM_TYPE_ROLE,
                            'rcp_to' => implode(',', $role_mail_boxes),
                            'sig' => $this->gui->createMailSignature()
                        ],
                        $this->generateContextArray()
                    ));
                } else {
                    $form->setValuesByPost();
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_checkbox'));
                    $this->showSearchForm();
                    return;
                }
            } else {
                $this->showSelectableUsers();
                return;
            }
        }

        $form->setValuesByPost();
        $this->showSearchForm();
    }

    protected function generateContextArray() : array
    {
        $contextParameters = [];

        $type = ilObject::_lookupType($this->ref_id, true);
        switch ($type) {
            case 'grp':
            case 'crs':
                if ($this->access->checkAccess('write', '', $this->ref_id)) {
                    $contextParameters = [
                        'ref_id' => $this->ref_id,
                        'ts' => time(),
                        ilMail::PROP_CONTEXT_SUBJECT_PREFIX => ilContainer::_lookupContainerSetting(
                            ilObject::_lookupObjId($this->ref_id),
                            ilObjectServiceSettingsGUI::EXTERNAL_MAIL_PREFIX,
                            ''
                        )
                    ];

                    if ('crs' === $type) {
                        $contextParameters[ilMailFormCall::CONTEXT_KEY] = ilCourseMailTemplateTutorContext::ID;
                    }
                }
                break;

            case 'sess':
                if ($this->access->checkAccess('write', '', $this->ref_id)) {
                    $contextParameters = [
                        ilMailFormCall::CONTEXT_KEY => ilSessionMailTemplateParticipantContext::ID,
                        'ref_id' => $this->ref_id,
                        'ts' => time()
                    ];
                }
                break;
        }

        return $contextParameters;
    }
    
    protected function showSelectableUsers() : void
    {
        $this->tpl->loadStandardTemplate();
        $tbl = new ilMailMemberSearchTableGUI($this, 'showSelectableUsers');
        $provider = new ilMailMemberSearchDataProvider($this->getObjParticipants(), $this->ref_id);
        $tbl->setData($provider->getData());

        $this->tpl->setContent($tbl->getHTML());
    }

    
    protected function sendMailToSelectedUsers() : void
    {
        if (!isset($this->httpRequest->getParsedBody()['user_ids']) || !is_array($this->httpRequest->getParsedBody()['user_ids']) || 0 === count($this->httpRequest->getParsedBody()['user_ids'])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"));
            $this->showSelectableUsers();
            return;
        }

        $rcps = [];
        foreach ($this->httpRequest->getParsedBody()['user_ids'] as $usr_id) {
            $rcps[] = ilObjUser::_lookupLogin($usr_id);
        }

        if (!count(array_filter($rcps))) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"));
            $this->showSelectableUsers();
            return;
        }

        ilMailFormCall::setRecipients($rcps);

        $this->ctrl->redirectToURL(ilMailFormCall::getRedirectTarget(
            $this,
            'members',
            [],
            [
                'type' => ilMailFormGUI::MAIL_FORM_TYPE_NEW,
                'sig' => $this->gui->createMailSignature(),
            ],
            $this->generateContextArray()
        ));
    }

    protected function showSearchForm() : void
    {
        $this->storeReferer();

        $form = $this->initMailToMembersForm();
        $this->tpl->setContent($form->getHTML());
    }

    
    protected function getObjParticipants() : ?ilParticipants
    {
        return $this->objParticipants;
    }

    /**
     * @param ilParticipants $objParticipants
     */
    public function setObjParticipants(ilParticipants $objParticipants) : void
    {
        $this->objParticipants = $objParticipants;
    }
    
    
    protected function initMailToMembersForm() : ilPropertyFormGUI
    {
        $this->lng->loadLanguageModule('mail');

        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('mail_members'));

        $form->setFormAction($this->ctrl->getFormAction($this, 'nextMailForm'));

        $radio_grp = $this->getMailRadioGroup();

        $form->addItem($radio_grp);
        $form->addCommandButton('nextMailForm', $this->lng->txt('mail_members_search_continue'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        return $form;
    }

    /**
     * @return array{role_id: int, mailbox: string, form_option_title: string, default_checked?: bool}[]
     */
    private function getMailRoles() : array
    {
        return $this->mail_roles;
    }
    
    
    protected function getMailRadioGroup() : ilRadioGroupInputGUI
    {
        $mail_roles = $this->getMailRoles();

        $radio_grp = new ilRadioGroupInputGUI($this->lng->txt('mail_sel_label'), 'mail_member_type');

        $radio_sel_users = new ilRadioOption($this->lng->txt('mail_sel_users'), 'mail_sel_users');

        $radio_roles = new ilRadioOption($this->objMailMemberRoles->getRadioOptionTitle(), 'mail_member_roles');
        foreach ($mail_roles as $role) {
            $chk_role = new ilCheckboxInputGUI($role['form_option_title'], 'roles[]');

            if (isset($role['default_checked']) && $role['default_checked'] === true) {
                $chk_role->setChecked(true);
            }
            $chk_role->setValue((string) $role['role_id']);
            $chk_role->setInfo($role['mailbox']);
            $radio_roles->addSubItem($chk_role);
        }

        $radio_grp->setValue('mail_member_roles');

        $radio_grp->addOption($radio_sel_users);
        $radio_grp->addOption($radio_roles);

        return $radio_grp;
    }
}
