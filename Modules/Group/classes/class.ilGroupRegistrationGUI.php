<?php

declare(strict_types=1);
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

/**
* GUI class for group registrations
*
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesGroup
*/
class ilGroupRegistrationGUI extends ilRegistrationGUI
{
    public function __construct(ilObject $a_container)
    {
        parent::__construct($a_container);
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);

        if ($this->getWaitingList()->isOnList($this->user->getId())) {
            $this->tabs->activateTab('leave');
        }

        switch ($next_class) {
            default:
                $cmd = $this->ctrl->getCmd("show");
                $this->$cmd();
                break;
        }
    }


    protected function getFormTitle(): string
    {
        if ($this->getWaitingList()->isOnList($this->user->getId())) {
            return $this->lng->txt('member_status');
        }
        return $this->lng->txt('grp_registration');
    }

    protected function fillInformations(): void
    {
        if ($this->container->getInformation()) {
            $imp = new ilNonEditableValueGUI($this->lng->txt('crs_important_info'), '', true);
            $value = nl2br(ilUtil::makeClickable($this->container->getInformation(), true));
            $imp->setValue($value);
            $this->form->addItem($imp);
        }
    }

    /**
     * show information about the registration period
     */
    protected function fillRegistrationPeriod(): void
    {
        $now = new ilDateTime(time(), IL_CAL_UNIX, 'UTC');

        if ($this->container->isRegistrationUnlimited()) {
            $reg = new ilNonEditableValueGUI($this->lng->txt('mem_reg_period'));
            $reg->setValue($this->lng->txt('mem_unlimited'));
            $this->form->addItem($reg);
            return;
        }

        $start = $this->container->getRegistrationStart();
        $end = $this->container->getRegistrationEnd();

        $warning = '';
        if (ilDateTime::_before($now, $start)) {
            $tpl = new ilTemplate('tpl.registration_period_form.html', true, true, 'Services/Membership');
            $tpl->setVariable('TXT_FIRST', $this->lng->txt('mem_start'));
            $tpl->setVariable('FIRST', ilDatePresentation::formatDate($start));

            $tpl->setVariable('TXT_END', $this->lng->txt('mem_end'));
            $tpl->setVariable('END', ilDatePresentation::formatDate($end));

            $warning = $this->lng->txt('mem_reg_not_started');
        } elseif (ilDateTime::_after($now, $end)) {
            $tpl = new ilTemplate('tpl.registration_period_form.html', true, true, 'Services/Membership');
            $tpl->setVariable('TXT_FIRST', $this->lng->txt('mem_start'));
            $tpl->setVariable('FIRST', ilDatePresentation::formatDate($start));

            $tpl->setVariable('TXT_END', $this->lng->txt('mem_end'));
            $tpl->setVariable('END', ilDatePresentation::formatDate($end));

            $warning = $this->lng->txt('mem_reg_expired');
        } else {
            $tpl = new ilTemplate('tpl.registration_period_form.html', true, true, 'Services/Membership');
            $tpl->setVariable('TXT_FIRST', $this->lng->txt('mem_end'));
            $tpl->setVariable('FIRST', ilDatePresentation::formatDate($end));
        }

        $reg = new ilCustomInputGUI($this->lng->txt('mem_reg_period'));
        $reg->setHtml($tpl->get());
        if (strlen($warning)) {
            // Disable registration
            $this->enableRegistration(false);
            #$reg->setAlert($warning);
            $this->tpl->setOnScreenMessage('failure', $warning);
        }
        $this->form->addItem($reg);
    }

    /**
     * fill max member information
     * @access protected
     * @return void
     */
    protected function fillMaxMembers(): void
    {
        $alert = '';
        if (!$this->container->isMembershipLimited()) {
            return;
        }

        $tpl = new ilTemplate('tpl.max_members_form.html', true, true, 'Services/Membership');

        if ($this->container->getMinMembers()) {
            $tpl->setVariable('TXT_MIN', $this->lng->txt('mem_min_users'));
            $tpl->setVariable('NUM_MIN', $this->container->getMinMembers());
        }

        if ($this->container->getMaxMembers()) {
            $tpl->setVariable('TXT_MAX', $this->lng->txt('mem_max_users'));
            $tpl->setVariable('NUM_MAX', $this->container->getMaxMembers());
            $tpl->setVariable('TXT_FREE', $this->lng->txt('mem_free_places') . ":");

            $reg_info = ilObjGroupAccess::lookupRegistrationInfo($this->getContainer()->getId());
            $free = $reg_info['reg_info_free_places'];


            if ($free) {
                $tpl->setVariable('NUM_FREE', $free);
            } else {
                $tpl->setVariable('WARN_FREE', $free);
            }

            $waiting_list = new ilGroupWaitingList($this->container->getId());

            if (
                $this->container->isWaitingListEnabled() and
                $this->container->isMembershipLimited() and
                (!$free or $waiting_list->getCountUsers())) {
                if ($waiting_list->isOnList($this->user->getId())) {
                    $tpl->setVariable('TXT_WAIT', $this->lng->txt('mem_waiting_list_position'));
                    $tpl->setVariable('NUM_WAIT', $waiting_list->getPosition($this->user->getId()));
                } else {
                    $tpl->setVariable('TXT_WAIT', $this->lng->txt('mem_waiting_list'));
                    if ($free and $waiting_list->getCountUsers()) {
                        $tpl->setVariable('WARN_WAIT', $waiting_list->getCountUsers());
                    } else {
                        $tpl->setVariable('NUM_WAIT', $waiting_list->getCountUsers());
                    }
                }
            }

            if (
                !$free and
                !$this->container->isWaitingListEnabled()) {
                // Disable registration
                $this->enableRegistration(false);
                $alert = $this->lng->txt('mem_alert_no_places');
            } elseif (
                $this->container->isWaitingListEnabled() and
                $this->container->isMembershipLimited() and
                $waiting_list->isOnList($this->user->getId())) {
                // Disable registration
                $this->enableRegistration(false);
            } elseif (
                !$free and
                $this->container->isWaitingListEnabled() and
                $this->container->isMembershipLimited()) {
                $alert = $this->lng->txt('grp_warn_no_max_set_on_waiting_list');
            } elseif (
                $free and
                $this->container->isWaitingListEnabled() and
                $this->container->isMembershipLimited() and
                $this->getWaitingList()->getCountUsers()) {
                $alert = $this->lng->txt('grp_warn_wl_set_on_waiting_list');
            }
        }

        $max = new ilCustomInputGUI($this->lng->txt('mem_participants'));
        $max->setHtml($tpl->get());
        if (strlen($alert)) {
            #$max->setAlert($alert);
            $this->tpl->setOnScreenMessage('failure', $alert);
        }
        $this->form->addItem($max);
    }

    protected function fillRegistrationType(): void
    {
        if ($this->getWaitingList()->isOnList($this->user->getId())) {
            return;
        }

        switch ($this->container->getRegistrationType()) {
            case ilGroupConstants::GRP_REGISTRATION_DEACTIVATED:
                $reg = new ilNonEditableValueGUI($this->lng->txt('mem_reg_type'));
                $reg->setValue($this->lng->txt('grp_reg_disabled'));
                #$reg->setAlert($this->lng->txt('grp_reg_deactivated_alert'));
                $this->form->addItem($reg);

                // Disable registration
                $this->enableRegistration(false);

                break;

            case ilGroupConstants::GRP_REGISTRATION_PASSWORD:
                $txt = new ilNonEditableValueGUI($this->lng->txt('mem_reg_type'));
                $txt->setValue($this->lng->txt('grp_pass_request'));


                $pass = new ilTextInputGUI($this->lng->txt('passwd'), 'grp_passw');
                $pass->setInputType('password');
                $pass->setSize(12);
                $pass->setMaxLength(32);
                #$pass->setRequired(true);
                $pass->setInfo($this->lng->txt('group_password_registration_msg'));

                $txt->addSubItem($pass);
                $this->form->addItem($txt);
                break;

            case ilGroupConstants::GRP_REGISTRATION_REQUEST:

                // no "request" info if waiting list is active
                if ($this->isWaitingListActive()) {
                    return;
                }

                $txt = new ilNonEditableValueGUI($this->lng->txt('mem_reg_type'));
                $txt->setValue($this->lng->txt('grp_reg_request'));

                $sub = new ilTextAreaInputGUI($this->lng->txt('grp_reg_subject'), 'subject');
                $subject = '';
                if ($this->http->wrapper()->post()->has('subject')) {
                    $subject = $this->http->wrapper()->post()->retrieve(
                        'subject',
                        $this->refinery->kindlyTo()->string()
                    );
                }
                $sub->setValue($subject);
                $sub->setInfo($this->lng->txt('group_req_registration_msg'));
                $sub->setCols(40);
                $sub->setRows(5);
                if ($this->participants->isSubscriber($this->user->getId())) {
                    $sub_data = $this->participants->getSubscriberData($this->user->getId());
                    $sub->setValue($sub_data['subject']);
                    $sub->setInfo('');
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('grp_already_assigned'));
                    $this->enableRegistration(false);
                }
                $txt->addSubItem($sub);
                $this->form->addItem($txt);
                break;

            case ilGroupConstants::GRP_REGISTRATION_DIRECT:

                // no "direct registration" info if waiting list is active
                if ($this->isWaitingListActive()) {
                    return;
                }

                $txt = new ilNonEditableValueGUI($this->lng->txt('mem_reg_type'));
                $txt->setValue($this->lng->txt('group_req_direct'));

                $this->form->addItem($txt);
                break;
        }
    }

    /**
     * Add group specific command buttons
     * @return void
     */
    protected function addCommandButtons(): void
    {
        parent::addCommandButtons();
        switch ($this->container->getRegistrationType()) {
            case ilGroupConstants::GRP_REGISTRATION_REQUEST:
                if ($this->participants->isSubscriber($this->user->getId())) {
                    $this->form->clearCommandButtons();
                    $this->form->addCommandButton('updateSubscriptionRequest', $this->lng->txt('grp_update_subscr_request'));
                    $this->form->addCommandButton('cancelSubscriptionRequest', $this->lng->txt('grp_cancel_subscr_request'));
                } else {
                    if (!$this->isRegistrationPossible()) {
                        return;
                    }
                    $this->form->clearCommandButtons();
                    $this->form->addCommandButton('join', $this->lng->txt('grp_join_request'));
                    $this->form->addCommandButton('cancel', $this->lng->txt('cancel'));
                }
                break;
        }
    }


    /**
     * validate join request
     * @access protected
     * @return bool
     */
    protected function validate(): bool
    {
        if ($this->user->getId() == ANONYMOUS_USER_ID) {
            $this->join_error = $this->lng->txt('permission_denied');
            return false;
        }

        if (!$this->isRegistrationPossible()) {
            $this->join_error = $this->lng->txt('mem_error_preconditions');
            return false;
        }
        if ($this->container->getRegistrationType() == ilGroupConstants::GRP_REGISTRATION_PASSWORD) {
            $password = '';
            if ($this->http->wrapper()->post()->has('grp_passw')) {
                $password = $this->http->wrapper()->post()->retrieve(
                    'grp_passw',
                    $this->refinery->kindlyTo()->string()
                );
            }
            if (!strlen($password)) {
                $this->join_error = $this->lng->txt('err_wrong_password');
                return false;
            }
            if (strcmp($password, $this->container->getPassword()) !== 0) {
                $this->join_error = $this->lng->txt('err_wrong_password');
                return false;
            }
        }
        if (!$this->validateCustomFields()) {
            $this->join_error = $this->lng->txt('fill_out_all_required_fields');
            return false;
        }
        if (!$this->validateAgreement()) {
            $this->join_error = $this->lng->txt($this->type . '_agreement_required');
            return false;
        }

        return true;
    }

    /**
     * add user
     */
    protected function add(): void
    {
        // set agreement accepted
        $this->setAccepted(true);

        $free = max(0, $this->container->getMaxMembers() - $this->participants->getCountMembers());
        $waiting_list = new ilGroupWaitingList($this->container->getId());
        if (
            $this->container->isMembershipLimited() and
            $this->container->isWaitingListEnabled() and
            (!$free or $waiting_list->getCountUsers())) {
            $waiting_list->addToList($this->user->getId());
            $info = sprintf(
                $this->lng->txt('grp_added_to_list'),
                $this->container->getTitle(),
                $waiting_list->getPosition($this->user->getId())
            );

            $this->participants->sendNotification(
                ilGroupMembershipMailNotification::TYPE_WAITING_LIST_MEMBER,
                $this->user->getId()
            );
            $this->tpl->setOnScreenMessage('success', $info, true);
            $this->ctrl->setParameterByClass(
                "ilrepositorygui",
                "ref_id",
                $this->tree->getParentId($this->container->getRefId())
            );
            $this->ctrl->redirectByClass("ilrepositorygui", "");
        }

        switch ($this->container->getRegistrationType()) {
            case ilGroupConstants::GRP_REGISTRATION_REQUEST:

                $this->participants->addSubscriber($this->user->getId());
                $this->participants->updateSubscriptionTime($this->user->getId(), time());
                $subject = '';
                if ($this->http->wrapper()->post()->has('subject')) {
                    $subject = $this->http->wrapper()->post()->retrieve(
                        'subject',
                        $this->refinery->kindlyTo()->string()
                    );
                }
                $this->participants->updateSubject($this->user->getId(), $subject);
                $this->participants->sendNotification(
                    ilGroupMembershipMailNotification::TYPE_NOTIFICATION_REGISTRATION_REQUEST,
                    $this->user->getId()
                );

                $this->tpl->setOnScreenMessage('success', $this->lng->txt("application_completed"), true);
                $this->ctrl->setParameterByClass(
                    "ilrepositorygui",
                    "ref_id",
                    $this->tree->getParentId($this->container->getRefId())
                );
                $this->ctrl->redirectByClass("ilrepositorygui", "");
                break;

            default:

                $this->participants->add($this->user->getId(), ilParticipants::IL_GRP_MEMBER);
                $this->participants->sendNotification(
                    ilGroupMembershipMailNotification::TYPE_NOTIFICATION_REGISTRATION,
                    $this->user->getId()
                );
                $this->participants->sendNotification(
                    ilGroupMembershipMailNotification::TYPE_SUBSCRIBE_MEMBER,
                    $this->user->getId()
                );

                ilForumNotification::checkForumsExistsInsert($this->container->getRefId(), $this->user->getId());

                $pending_goto = ilSession::get('pending_goto');
                if (!$pending_goto) {
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt("grp_registration_completed"), true);
                    $this->ctrl->returnToParent($this);
                } else {
                    $tgt = $pending_goto;
                    ilSession::clear('pending_goto');
                    ilUtil::redirect($tgt);
                }
                break;
        }
    }


    /**
     * Init course participants
     *
     * @access protected
     */
    protected function initParticipants(): ilParticipants
    {
        $this->participants = ilGroupParticipants::_getInstanceByObjId($this->obj_id);
        return $this->participants;
    }

    /**
     * @see ilRegistrationGUI::initWaitingList()
     * @access protected
     */
    protected function initWaitingList(): ilWaitingList
    {
        $this->waiting_list = new ilGroupWaitingList($this->container->getId());
        return $this->waiting_list;
    }

    /**
     * @see ilRegistrationGUI::isWaitingListActive()
     */
    protected function isWaitingListActive(): bool
    {
        static $active = null;

        if ($active !== null) {
            return $active;
        }
        if (!$this->container->getMaxMembers()) {
            return $active = false;
        }
        if (
            !$this->container->isWaitingListEnabled() or
            !$this->container->isMembershipLimited()) {
            return $active = false;
        }

        $free = max(0, $this->container->getMaxMembers() - $this->participants->getCountMembers());
        return $active = (!$free or $this->getWaitingList()->getCountUsers());
    }
}
