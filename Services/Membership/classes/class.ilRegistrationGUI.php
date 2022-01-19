<?php declare(strict_types=1);/*
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
 * Base class for Course and Group registration
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesRegistration
 */
abstract class ilRegistrationGUI
{
    protected ilPrivacySettings $privacy;

    protected ilObject $container;
    protected int $ref_id;
    protected int $obj_id;
    protected string $type;

    protected ilParticipants $participants;
    protected ilWaitingList $waiting_list;
    protected ?ilPropertyFormGUI $form = null;

    protected bool $registration_possible = true;
    protected string $join_error = '';

    protected ilObjUser $user;
    protected ilTabsGUI $tabs;
    protected ilTree $tree;
    protected ilRbacReview $rbacreview;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilAccessHandler $access;

    public function __construct(ilObject $a_container)
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->tabs = $DIC->tabs();
        $this->tree = $DIC->repositoryTree();
        $this->rbacreview = $DIC->rbac()->review();

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('grp');
        $this->lng->loadLanguageModule('ps');
        $this->lng->loadLanguageModule('membership');

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->access = $DIC->access();

        $this->container = $a_container;
        $this->ref_id = $this->container->getRefId();
        $this->obj_id = ilObject::_lookupObjId($this->ref_id);
        $this->type = ilObject::_lookupType($this->obj_id);

        $this->initParticipants();
        $this->initWaitingList();

        $this->privacy = ilPrivacySettings::getInstance();
    }

    public function getContainer() : ilObject
    {
        return $this->container;
    }

    public function getRefId() : int
    {
        return $this->ref_id;
    }

    protected function isRegistrationPossible() : bool
    {
        return $this->registration_possible;
    }

    protected function enableRegistration(bool $a_status) : void
    {
        $this->registration_possible = $a_status;
    }

    /**
     * Init participants object (course or group participants)
     */
    abstract protected function initParticipants() : ilParticipants;

    /**
     * Init waiting list (course or group waiting list)
     */
    abstract protected function initWaitingList() : ilWaitingList;

    /**
     * Check if the waiting list is active
     * Maximum of members exceeded or
     * any user on the waiting list
     */
    abstract protected function isWaitingListActive() : bool;

    /**
     * Get waiting list object
     */
    protected function getWaitingList() : ilWaitingList
    {
        return $this->waiting_list;
    }

    protected function leaveWaitingList() : void
    {
        $this->getWaitingList()->removeFromList($this->user->getId());
        $parent = $this->tree->getParentId($this->container->getRefId());

        $message = sprintf(
            $this->lng->txt($this->container->getType() . '_removed_from_waiting_list'),
            $this->container->getTitle()
        );
        ilUtil::sendSuccess($message, true);
        $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $parent);
        $this->ctrl->redirectByClass("ilrepositorygui", "");
    }

    /**
     * Get title for property form
     */
    abstract protected function getFormTitle() : string;

    /**
     * fill informations
     */
    abstract protected function fillInformations() : void;

    /**
     * show informations about the registration period
     */
    abstract protected function fillRegistrationPeriod() : void;

    /**
     * show informations about the maximum number of user.
     */
    abstract protected function fillMaxMembers() : void;

    /**
     * show informations about registration procedure
     */
    abstract protected function fillRegistrationType() : void;

    /**
     * Show membership limitations
     */
    protected function fillMembershipLimitation() : void
    {
        if (!$items = ilObjCourseGrouping::_getGroupingItems($this->container)) {
            return;
        }
        $mem = new ilCustomInputGUI($this->lng->txt('groupings'));
        $tpl = new ilTemplate('tpl.membership_limitation_form.html', true, true, 'Services/Membership');
        $tpl->setVariable('LIMIT_INTRO', $this->lng->txt($this->type . '_grp_info_reg'));
        foreach ($items as $ref_id) {
            $obj_id = ilObject::_lookupObjId($ref_id);
            $type = ilObject::_lookupType($obj_id);
            $title = ilObject::_lookupTitle($obj_id);

            if ($this->access->checkAccess('visible', '', $ref_id, $type)) {
                $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $ref_id);
                $tpl->setVariable(
                    'LINK_ITEM',
                    $this->ctrl->getLinkTargetByClass("ilrepositorygui", "")
                );
                $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
                $tpl->setVariable('ITEM_LINKED_TITLE', $title);
            } else {
                $tpl->setVariable('ITEM_TITLE');
            }
            $tpl->setCurrentBlock('items');
            $tpl->setVariable('TYPE_ICON', ilObject::_getIcon($obj_id, 'tiny', $type));
            $tpl->setVariable('ALT_ICON', $this->lng->txt('obj_' . $type));
            $tpl->parseCurrentBlock();
        }
        $mem->setHtml($tpl->get());
        if (!ilObjCourseGrouping::_checkGroupingDependencies($this->container)) {
            $mem->setAlert($this->container->getMessage());
            $this->enableRegistration(false);
        }
        $this->form->addItem($mem);
    }

    protected function fillAgreement() : void
    {
        if (!$this->isRegistrationPossible()) {
            return;
        }

        if (!$this->privacy->confirmationRequired($this->type) and !ilCourseDefinedFieldDefinition::_hasFields($this->container->getId())) {
            return;
        }

        $this->lng->loadLanguageModule('ps');

        $fields_info = ilExportFieldsInfo::_getInstanceByType(ilObject::_lookupType($this->container->getId()));

        if (!count($fields_info->getExportableFields())) {
            return;
        }
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt($this->type . '_usr_agreement'));
        $this->form->addItem($section);

        ilMemberAgreementGUI::addExportFieldInfo($this->form, $this->obj_id, $this->type);

        ilMemberAgreementGUI::addCustomFields($this->form, $this->obj_id, $this->type);

        // Checkbox agreement
        if ($this->privacy->confirmationRequired($this->type)) {
            ilMemberAgreementGUI::addAgreement($this->form, $this->obj_id, $this->type);
        }
    }

    protected function showCustomFields() : void
    {
        if (!count($cdf_fields = ilCourseDefinedFieldDefinition::_getFields($this->container->getId()))) {
            return;
        }
        $cdf = new ilNonEditableValueGUI($this->lng->txt('ps_crs_user_fields'));
        $cdf->setValue($this->lng->txt($this->type . '_ps_cdf_info'));
        $cdf->setRequired(true);
        foreach ($cdf_fields as $field_obj) {
            $course_user_data = new ilCourseUserData($this->user->getId(), $field_obj->getId());

            switch ($field_obj->getType()) {
                case IL_CDF_TYPE_SELECT:
                    $select = new ilSelectInputGUI($field_obj->getName(), 'cdf[' . $field_obj->getId() . ']');
                    $select->setValue(ilUtil::stripSlashes($_POST['cdf'][$field_obj->getId()]));
                    $select->setOptions($field_obj->prepareSelectBox());
                    if ($field_obj->isRequired()) {
                        $select->setRequired(true);
                    }
                    $cdf->addSubItem($select);
                    break;

                case IL_CDF_TYPE_TEXT:
                    $text = new ilTextInputGUI($field_obj->getName(), 'cdf[' . $field_obj->getId() . ']');
                    $text->setValue(ilUtil::stripSlashes($_POST['cdf'][$field_obj->getId()]));
                    $text->setSize(32);
                    $text->setMaxLength(255);
                    if ($field_obj->isRequired()) {
                        $text->setRequired(true);
                    }
                    $cdf->addSubItem($text);
                    break;
            }
        }
        $this->form->addItem($cdf);
    }

    protected function validateAgreement() : bool
    {
        if ($_POST['agreement']) {
            return true;
        }
        if (!$this->privacy->confirmationRequired($this->type)) {
            return true;
        }
        return false;
    }

    protected function validateCustomFields() : bool
    {
        $required_fullfilled = true;
        $value = '';
        foreach (ilCourseDefinedFieldDefinition::_getFields($this->container->getId()) as $field_obj) {
            switch ($field_obj->getType()) {
                case IL_CDF_TYPE_SELECT:

                    // Split value id from post
                    list($field_id, $option_id) = explode('_', $_POST['cdf_' . $field_obj->getId()]);

                    $open_answer_indexes = $field_obj->getValueOptions();
                    if (in_array($option_id, $open_answer_indexes)) {
                        $value = $_POST['cdf_oa_' . $field_obj->getId() . '_' . $option_id];
                    } else {
                        $value = $field_obj->getValueById($option_id);
                    }
                    break;

                case IL_CDF_TYPE_TEXT:
                    $value = $_POST['cdf_' . $field_obj->getId()];
                    break;
            }

            $course_user_data = new ilCourseUserData($this->user->getId(), $field_obj->getId());
            $course_user_data->setValue($value);
            $course_user_data->update();

            // #14220
            if ($field_obj->isRequired() and $value == "") {
                $required_fullfilled = false;
            }
        }
        return $required_fullfilled;
    }

    protected function setAccepted(bool $a_status) : void
    {
        if (!$this->privacy->confirmationRequired($this->type) and !ilCourseDefinedFieldDefinition::_hasFields($this->container->getId())) {
            return;
        }

        $agreement = new ilMemberAgreement($this->user->getId(), $this->container->getId());
        $agreement->setAccepted($a_status);
        $agreement->setAcceptanceTime(time());
        $agreement->save();
    }

    /**
     * cancel subscription
     */
    public function cancel() : void
    {
        $this->ctrl->setParameterByClass(
            "ilrepositorygui",
            "ref_id",
            $this->tree->getParentId($this->container->getRefId())
        );
        $this->ctrl->redirectByClass("ilrepositorygui", "");
    }

    public function show(?ilPropertyFormGUI $form = null) : void
    {
        if (!$form instanceof ilPropertyFormGUI) {
            $this->initForm();
        }
        if ($_SESSION["pending_goto"]) {
            ilUtil::sendInfo($this->lng->txt("reg_goto_parent_membership_info"));
        }
        $this->tpl->setContent($this->form->getHTML());
    }

    public function join() : void
    {
        $form = $this->initForm();
        if (!$form->checkInput() || !$this->validate()) {
            $form->setValuesByPost();
            if ($this->join_error) {
                ilUtil::sendFailure($this->join_error);
            } else {
                ilUtil::sendFailure($this->lng->txt('err_check_input'));
            }
            $this->show($form);
            return;
        }
        $this->add();
    }

    protected function validate() : bool
    {
        return true;
    }

    /**
     * @todo get rid $this->form
     */
    protected function initForm() : ilPropertyFormGUI
    {
        if (is_object($this->form)) {
            return $this->form;
        }

        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this, 'join'));
        $this->form->setTitle($this->getFormTitle());

        $this->fillInformations();
        $this->fillMembershipLimitation();
        if ($this->isRegistrationPossible()) {
            $this->fillRegistrationPeriod();
        }
        if ($this->isRegistrationPossible() || $this->participants->isSubscriber($this->user->getId())) {
            $this->fillRegistrationType();
        }
        if ($this->isRegistrationPossible()) {
            $this->fillMaxMembers();
        }
        if ($this->isRegistrationPossible()) {
            $this->fillAgreement();
        }
        $this->addCommandButtons();
        return $this->form;
    }

    /**
     * @todo get rid of $this->form
     */
    protected function addCommandButtons() : void
    {
        if (
            $this->isRegistrationPossible() &&
            $this->isWaitingListActive() &&
            !$this->getWaitingList()->isOnList($this->user->getId())
        ) {
            $this->form->addCommandButton('join', $this->lng->txt('mem_add_to_wl'));
            $this->form->addCommandButton('cancel', $this->lng->txt('cancel'));
        } elseif ($this->isRegistrationPossible() && !$this->getWaitingList()->isOnList($this->user->getId())) {
            $this->form->addCommandButton('join', $this->lng->txt('join'));
            $this->form->addCommandButton('cancel', $this->lng->txt('cancel'));
        }
        if ($this->getWaitingList()->isOnList($this->user->getId())) {
            ilUtil::sendQuestion(
                sprintf(
                    $this->lng->txt($this->container->getType() . '_cancel_waiting_list'),
                    $this->container->getTitle()
                )
            );
            $this->form->addCommandButton('leaveWaitingList', $this->lng->txt('leave_waiting_list'));
            $this->form->addCommandButton('cancel', $this->lng->txt('cancel'));
        }
    }

    protected function updateSubscriptionRequest() : void
    {
        $this->participants->updateSubject($this->user->getId(), ilUtil::stripSlashes($_POST['subject']));
        ilUtil::sendSuccess($this->lng->txt('sub_request_saved'), true);
        $this->ctrl->setParameterByClass(
            "ilrepositorygui",
            "ref_id",
            $this->tree->getParentId($this->container->getRefId())
        );
        $this->ctrl->redirectByClass("ilrepositorygui", "");
    }

    protected function cancelSubscriptionRequest() : void
    {
        $this->participants->deleteSubscriber($this->user->getId());
        ilUtil::sendSuccess($this->lng->txt('sub_request_deleted'), true);

        $this->ctrl->setParameterByClass(
            "ilrepositorygui",
            "ref_id",
            $this->tree->getParentId($this->container->getRefId())
        );
        $this->ctrl->redirectByClass("ilrepositorygui", "");
    }
}
