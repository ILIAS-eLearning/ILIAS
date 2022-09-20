<?php

declare(strict_types=0);
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

/**
 * GUI class for course registrations
 * @author       Stefan Meyer <smeyer.ilias@gmx.de>
 * @version      $Id$
 * @ingroup      ModulesCourse
 * @ilCtrl_Calls ilCourseRegistrationGUI:
 */
class ilCourseRegistrationGUI extends ilRegistrationGUI
{
    private object $parent_gui;

    public function __construct(ilObject $a_container, object $a_parent_gui)
    {
        parent::__construct($a_container);
        $this->parent_gui = $a_parent_gui;
    }

    public function executeCommand()
    {
        if ($this->getWaitingList()->isOnList($this->user->getId())) {
            $this->tabs->activateTab('leave');
        }

        if (!$this->access->checkAccess('join', '', $this->getRefId())) {
            $this->ctrl->setReturn($this->parent_gui, 'infoScreen');
            $this->ctrl->returnToParent($this);
            return;
        }

        $next_class = $this->ctrl->getNextClass($this);
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
        return $this->lng->txt('crs_registration');
    }

    protected function fillInformations(): void
    {
        if ($this->container->getImportantInformation()) {
            $imp = new ilNonEditableValueGUI($this->lng->txt('crs_important_info'), "", true);
            $value = nl2br(ilUtil::makeClickable($this->container->getImportantInformation(), true));
            $imp->setValue($value);
            $this->form->addItem($imp);
        }

        if ($this->container->getSyllabus()) {
            $syl = new ilNonEditableValueGUI($this->lng->txt('crs_syllabus'), "", true);
            $value = nl2br(ilUtil::makeClickable($this->container->getSyllabus(), true));
            $syl->setValue($value);
            $this->form->addItem($syl);
        }
    }

    protected function fillRegistrationPeriod(): void
    {
        $now = new ilDateTime(time(), IL_CAL_UNIX, 'UTC');

        if ($this->container->getSubscriptionUnlimitedStatus()) {
            $reg = new ilNonEditableValueGUI($this->lng->txt('mem_reg_period'));
            $reg->setValue($this->lng->txt('mem_unlimited'));
            $this->form->addItem($reg);
            return;
        } elseif ($this->container->getSubscriptionLimitationType() == ilCourseConstants::IL_CRS_SUBSCRIPTION_DEACTIVATED) {
            return;
        }

        $start = new ilDateTime($this->container->getSubscriptionStart(), IL_CAL_UNIX, 'UTC');
        $end = new ilDateTime($this->container->getSubscriptionEnd(), IL_CAL_UNIX, 'UTC');

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
            $this->tpl->setOnScreenMessage('failure', $warning);
            #$reg->setAlert($warning);
        }
        $this->form->addItem($reg);
    }

    protected function fillMaxMembers(): void
    {
        if (!$this->container->isSubscriptionMembershipLimited()) {
            return;
        }
        $tpl = new ilTemplate('tpl.max_members_form.html', true, true, 'Services/Membership');

        $alert = '';
        if ($this->container->getSubscriptionMinMembers()) {
            $tpl->setVariable('TXT_MIN', $this->lng->txt('mem_min_users') . ':');
            $tpl->setVariable('NUM_MIN', $this->container->getSubscriptionMinMembers());
        }

        if ($this->container->getSubscriptionMaxMembers()) {
            $tpl->setVariable('TXT_MAX', $this->lng->txt('mem_max_users'));
            $tpl->setVariable('NUM_MAX', $this->container->getSubscriptionMaxMembers());

            $tpl->setVariable('TXT_FREE', $this->lng->txt('mem_free_places') . ":");
            $reg_info = ilObjCourseAccess::lookupRegistrationInfo($this->getContainer()->getId());
            $free = $reg_info['reg_info_free_places'];

            if ($free) {
                $tpl->setVariable('NUM_FREE', $free);
            } else {
                $tpl->setVariable('WARN_FREE', $free);
            }

            $waiting_list = new ilCourseWaitingList($this->container->getId());
            if (
                $this->container->isSubscriptionMembershipLimited() && $this->container->enabledWaitingList() && (!$free || $waiting_list->getCountUsers())) {
                if ($waiting_list->isOnList($this->user->getId())) {
                    $tpl->setVariable('TXT_WAIT', $this->lng->txt('mem_waiting_list_position'));
                    $tpl->setVariable('NUM_WAIT', $waiting_list->getPosition($this->user->getId()));
                } else {
                    $tpl->setVariable('TXT_WAIT', $this->lng->txt('mem_waiting_list'));
                    if ($free && $waiting_list->getCountUsers()) {
                        $tpl->setVariable('WARN_WAIT', $waiting_list->getCountUsers());
                    } else {
                        $tpl->setVariable('NUM_WAIT', $waiting_list->getCountUsers());
                    }
                }
            }
            if (
                !$free && !$this->container->enabledWaitingList()) {
                // Disable registration
                $this->enableRegistration(false);
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mem_alert_no_places'));
            #$alert = $this->lng->txt('mem_alert_no_places');
            } elseif (
                $this->container->enabledWaitingList() && $this->container->isSubscriptionMembershipLimited() && $waiting_list->isOnList($this->user->getId())
            ) {
                // Disable registration
                $this->enableRegistration(false);
            } elseif (
                !$free && $this->container->enabledWaitingList() && $this->container->isSubscriptionMembershipLimited()) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_warn_no_max_set_on_waiting_list'));
            #$alert = $this->lng->txt('crs_warn_no_max_set_on_waiting_list');
            } elseif (
                $free && $this->container->enabledWaitingList() && $this->container->isSubscriptionMembershipLimited() && $this->getWaitingList()->getCountUsers()) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_warn_wl_set_on_waiting_list'));
                #$alert = $this->lng->txt('crs_warn_wl_set_on_waiting_list');
            }
        }

        $max = new ilCustomInputGUI($this->lng->txt('mem_participants'));
        $max->setHtml($tpl->get());
        if (strlen($alert)) {
            $max->setAlert($alert);
        }
        $this->form->addItem($max);
    }

    protected function fillRegistrationType(): void
    {
        if ($this->container->getSubscriptionLimitationType() == ilCourseConstants::IL_CRS_SUBSCRIPTION_DEACTIVATED) {
            $reg = new ilCustomInputGUI($this->lng->txt('mem_reg_type'));
            #$reg->setHtml($this->lng->txt('crs_info_reg_deactivated'));
            $reg->setAlert($this->lng->txt('crs_info_reg_deactivated'));
            #ilUtil::sendFailure($this->lng->txt('crs_info_reg_deactivated'));
            #$reg = new ilNonEditableValueGUI($this->lng->txt('mem_reg_type'));
            #$reg->setValue($this->lng->txt('crs_info_reg_deactivated'));
            #$reg->setAlert($this->lng->txt('grp_reg_deactivated_alert'));
            $this->form->addItem($reg);

            // Disable registration
            $this->enableRegistration(false);
            return;
        }

        switch ($this->container->getSubscriptionType()) {
            case ilCourseConstants::IL_CRS_SUBSCRIPTION_DIRECT:

                // no "request" info if waiting list is active
                if ($this->isWaitingListActive()) {
                    return;
                }

                $txt = new ilNonEditableValueGUI($this->lng->txt('mem_reg_type'));
                $txt->setValue($this->lng->txt('crs_info_reg_direct'));

                $this->form->addItem($txt);
                break;

            case ilCourseConstants::IL_CRS_SUBSCRIPTION_PASSWORD:
                $txt = new ilNonEditableValueGUI($this->lng->txt('mem_reg_type'));
                $txt->setValue($this->lng->txt('crs_subscription_options_password'));

                $pass = new ilTextInputGUI($this->lng->txt('passwd'), 'grp_passw');
                $pass->setInputType('password');
                $pass->setSize(12);
                $pass->setMaxLength(32);
                #$pass->setRequired(true);
                $pass->setInfo($this->lng->txt('crs_info_reg_password'));

                $txt->addSubItem($pass);
                $this->form->addItem($txt);
                break;

            case ilCourseConstants::IL_CRS_SUBSCRIPTION_CONFIRMATION:

                // no "request" info if waiting list is active
                if ($this->isWaitingListActive()) {
                    return;
                }

                $txt = new ilNonEditableValueGUI($this->lng->txt('mem_reg_type'));
                $txt->setValue($this->lng->txt('crs_subscription_options_confirmation'));

                $sub = new ilTextAreaInputGUI($this->lng->txt('crs_reg_subject'), 'subject');
                $sub->setInfo($this->lng->txt('crs_info_reg_confirmation'));
                $sub->setCols(40);
                $sub->setRows(5);
                if ($this->participants->isSubscriber($this->user->getId())) {
                    $sub_data = $this->participants->getSubscriberData($this->user->getId());
                    $sub->setValue($sub_data['subject']);
                    $sub->setInfo('');
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_reg_user_already_subscribed'));
                    $this->enableRegistration(false);
                }
                $txt->addSubItem($sub);
                $this->form->addItem($txt);
                break;

            default:
        }
    }

    protected function addCommandButtons(): void
    {
        parent::addCommandButtons();
        switch ($this->container->getSubscriptionType()) {
            case ilCourseConstants::IL_CRS_SUBSCRIPTION_CONFIRMATION:
                if ($this->participants->isSubscriber($this->user->getId())) {
                    $this->form->clearCommandButtons();
                    $this->form->addCommandButton(
                        'updateSubscriptionRequest',
                        $this->lng->txt('crs_update_subscr_request')
                    );
                    $this->form->addCommandButton(
                        'cancelSubscriptionRequest',
                        $this->lng->txt('crs_cancel_subscr_request')
                    );
                } elseif ($this->isRegistrationPossible()) {
                    $this->form->clearCommandButtons();
                    $this->form->addCommandButton('join', $this->lng->txt('crs_join_request'));
                    $this->form->addCommandButton('cancel', $this->lng->txt('cancel'));
                }
                break;
        }
        if (!$this->isRegistrationPossible()) {
        }
    }

    protected function validate(): bool
    {
        if ($this->user->getId() == ANONYMOUS_USER_ID) {
            $this->join_error = $this->lng->txt('permission_denied');
            return false;
        }

        // Set aggrement to not accepted
        $this->setAccepted(false);

        if (!$this->isRegistrationPossible()) {
            $this->join_error = $this->lng->txt('mem_error_preconditions');
            return false;
        }
        if ($this->container->getSubscriptionType() == ilCourseConstants::IL_CRS_SUBSCRIPTION_PASSWORD) {
            $pass = $this->http->wrapper()->post()->retrieve(
                'grp_passw',
                $this->refinery->kindlyTo()->string()
            );
            if ((string) $pass === '') {
                $this->join_error = $this->lng->txt('crs_password_required');
                return false;
            }
            if (strcmp($pass, $this->container->getSubscriptionPassword()) !== 0) {
                $this->join_error = $this->lng->txt('crs_password_not_valid');
                return false;
            }
        }
        if (!$this->validateCustomFields()) {
            $this->join_error = $this->lng->txt('fill_out_all_required_fields');
            return false;
        }
        if (!$this->validateAgreement()) {
            $this->join_error = $this->lng->txt('crs_agreement_required');
            return false;
        }

        return true;
    }

    protected function add()
    {
        // set aggreement accepted
        $this->setAccepted(true);

        $free = max(0, $this->container->getSubscriptionMaxMembers() - $this->participants->getCountMembers());
        $waiting_list = new ilCourseWaitingList($this->container->getId());
        if ($this->container->isSubscriptionMembershipLimited() && $this->container->enabledWaitingList() && (!$free || $waiting_list->getCountUsers())) {
            $waiting_list->addToList($this->user->getId());
            $info = sprintf(
                $this->lng->txt('crs_added_to_list'),
                $waiting_list->getPosition($this->user->getId())
            );
            $this->tpl->setOnScreenMessage('success', $info, true);

            $this->participants->sendNotification(
                ilCourseMembershipMailNotification::TYPE_NOTIFICATION_ADMINS_REGISTRATION_REQUEST,
                $this->user->getId()
            );
            $this->participants->sendNotification(
                ilCourseMembershipMailNotification::TYPE_WAITING_LIST_MEMBER,
                $this->user->getId()
            );
            $this->ctrl->setParameterByClass(
                "ilrepositorygui",
                "ref_id",
                $this->tree->getParentId($this->container->getRefId())
            );
            $this->ctrl->redirectByClass("ilrepositorygui", "");
        }

        switch ($this->container->getSubscriptionType()) {
            case ilCourseConstants::IL_CRS_SUBSCRIPTION_CONFIRMATION:
                $this->participants->addSubscriber($this->user->getId());
                $this->participants->updateSubscriptionTime($this->user->getId(), time());

                $subject = $this->http->wrapper()->post()->retrieve(
                    'subject',
                    $this->refinery->kindlyTo()->string()
                );
                $this->participants->updateSubject($this->user->getId(), $subject);
                $this->participants->sendNotification(
                    ilCourseMembershipMailNotification::TYPE_NOTIFICATION_ADMINS_REGISTRATION_REQUEST,
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

                if ($this->container->isSubscriptionMembershipLimited() && $this->container->getSubscriptionMaxMembers()) {
                    $success = $GLOBALS['DIC']['rbacadmin']->assignUserLimited(
                        ilParticipants::getDefaultMemberRole($this->container->getRefId()),
                        $this->user->getId(),
                        $this->container->getSubscriptionMaxMembers(),
                        array(ilParticipants::getDefaultMemberRole($this->container->getRefId()))
                    );
                    if (!$success) {
                        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_subscription_failed_limit'));
                        $this->show();
                        return;
                    }
                }

                $this->participants->add($this->user->getId(), ilParticipants::IL_CRS_MEMBER);
                $this->participants->sendNotification(ilCourseMembershipMailNotification::TYPE_NOTIFICATION_ADMINS, $this->user->getId());
                $this->participants->sendNotification(ilCourseMembershipMailNotification::TYPE_SUBSCRIBE_MEMBER, $this->user->getId());

                ilForumNotification::checkForumsExistsInsert($this->container->getRefId(), $this->user->getId());

                if ($this->container->getType() == "crs") {
                    $this->container->checkLPStatusSync($this->user->getId());
                }
                $pending_goto = ilSession::get('pending_goto');
                if (!$pending_goto) {
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt("crs_subscription_successful"), true);
                    $this->ctrl->returnToParent($this);
                } else {
                    $tgt = $pending_goto;
                    ilSession::clear('pending_goto');
                    ilUtil::redirect($tgt);
                }
                break;
        }
    }

    protected function initParticipants(): ilParticipants
    {
        $this->participants = ilCourseParticipants::_getInstanceByObjId($this->obj_id);
        return $this->participants;
    }

    protected function initWaitingList(): ilWaitingList
    {
        $this->waiting_list = new ilCourseWaitingList($this->container->getId());
        return $this->waiting_list;
    }

    protected function isWaitingListActive(): bool
    {
        static $active = null;

        if ($active !== null) {
            return $active;
        }
        if (!$this->container->enabledWaitingList() || !$this->container->isSubscriptionMembershipLimited()) {
            return $active = false;
        }
        if (!$this->container->getSubscriptionMaxMembers()) {
            return $active = false;
        }

        $free = max(0, $this->container->getSubscriptionMaxMembers() - $this->participants->getCountMembers());
        return $active = (!$free || $this->getWaitingList()->getCountUsers());
    }
}
