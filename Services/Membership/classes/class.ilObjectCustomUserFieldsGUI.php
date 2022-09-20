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
use ILIAS\Refinery\Factory;

/**
 * @author       Stefan Meyer <meyer@leifos.com>
 * @version      $Id$
 * @ilCtrl_Calls ilObjectCustomUserFieldsGUI
 * @ingroup      ServicesMembership
 */
class ilObjectCustomUserFieldsGUI
{
    protected const MODE_CREATE = 1;
    protected const MODE_UPDATE = 2;

    private ?ilPropertyFormGUI $form = null;
    private ilLanguage $lng;
    private ilGlobalTemplateInterface $tpl;
    private ilCtrlInterface $ctrl;
    private ilTabsGUI $tabs_gui;
    protected ilErrorHandling $errorHandling;
    protected ilAccessHandler $accessHandler;
    protected ilToolbarGUI $toolbarGUI;
    protected ilObjUser $user;
    protected GlobalHttpState $http;
    protected Factory $refinery;
    private int $obj_id;
    private int $ref_id;

    public function __construct(int $a_obj_id)
    {
        global $DIC;

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('ps');
        $this->lng->loadLanguageModule(ilObject::_lookupType($a_obj_id));

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->tabs_gui = $DIC->tabs();
        $this->errorHandling = $DIC['ilErr'];
        $this->accessHandler = $DIC->access();
        $this->toolbarGUI = $DIC->toolbar();
        $this->user = $DIC->user();
        $this->obj_id = $a_obj_id;

        // Currently only supported for container objects
        $refs = ilObject::_getAllReferences($this->obj_id);
        $this->ref_id = end($refs);
    }

    protected function initMemberIdFromQuery(): int
    {
        if ($this->http->wrapper()->query()->has('member_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'member_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    protected function initFielIdFromQuery(): int
    {
        if ($this->http->wrapper()->query()->has('field_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'field_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    protected function initRequiredStatusFromPost(): array
    {
        if ($this->http->wrapper()->post()->has('required')) {
            return $this->http->wrapper()->post()->retrieve(
                'required',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->bool()
                )
            );
        }
        return [];
    }

    public function executeCommand(): void
    {
        if (!$this->accessHandler->checkAccess('write', '', $this->ref_id)) {
            $this->errorHandling->raiseError($this->lng->txt('permission_denied'), $this->errorHandling->WARNING);
        }

        $cmd = $this->ctrl->getCmd();
        switch ($next_class = $this->ctrl->getNextClass($this)) {
            default:
                if (!$cmd) {
                    $cmd = 'show';
                }
                $this->$cmd();
                break;
        }
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    protected function show(): void
    {
        if (ilMemberAgreement::_hasAgreementsByObjId($this->getObjId())) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('ps_cdf_warning_modify'));
        }
        $this->listFields();
    }

    protected function listFields(): void
    {
        $this->toolbarGUI->addButton(
            $this->lng->txt('ps_cdf_add_field'),
            $this->ctrl->getLinkTarget($this, 'addField')
        );
        $table = new ilObjectCustomUserFieldsTableGUI($this, 'listFields');
        $table->parse(ilCourseDefinedFieldDefinition::_getFields($this->getObjId()));
        $this->tpl->setContent($table->getHTML());
    }

    protected function saveFields(): void
    {
        $fields = ilCourseDefinedFieldDefinition::_getFields($this->getObjId());
        foreach ($fields as $field_obj) {
            $field_obj->enableRequired($this->initRequiredStatusFromPost()[$field_obj->getId()] ?? false);
            $field_obj->update();
        }

        ilMemberAgreement::_deleteByObjId($this->getObjId());
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
        $this->listFields();
    }

    protected function confirmDeleteFields(): void
    {
        $field_ids = [];
        if ($this->http->wrapper()->post()->has('field_ids')) {
            $field_ids = $this->http->wrapper()->post()->retrieve(
                'field_ids',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }

        if (!count($field_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('ps_cdf_select_one'));
            $this->listFields();
            return;
        }
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setHeaderText($this->lng->txt('ps_cdf_delete_sure'));

        foreach ($field_ids as $field_id) {
            $tmp_field = new ilCourseDefinedFieldDefinition($this->getObjId(), $field_id);

            $confirm->addItem('field_ids[]', (string) $field_id, $tmp_field->getName());
        }

        $confirm->setConfirm($this->lng->txt('delete'), 'deleteFields');
        $confirm->setCancel($this->lng->txt('cancel'), 'listFields');
        $this->tpl->setContent($confirm->getHTML());
    }

    protected function deleteFields(): void
    {
        $field_ids = [];
        if ($this->http->wrapper()->post()->has('field_ids')) {
            $field_ids = $this->http->wrapper()->post()->retrieve(
                'field_ids',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }

        foreach ($field_ids as $field_id) {
            $tmp_field = new ilCourseDefinedFieldDefinition($this->obj_id, $field_id);
            $tmp_field->delete();
        }

        ilMemberAgreement::_deleteByObjId($this->obj_id);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('ps_cdf_deleted'));
        $this->listFields();
    }

    protected function addField(): void
    {
        $this->initFieldForm(self::MODE_CREATE);
        $this->form->getItemByPostVar('va')->setValues(array(''));
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function saveField(): void
    {
        $this->initFieldForm(self::MODE_CREATE);
        if ($this->form->checkInput()) {
            $udf = new ilCourseDefinedFieldDefinition($this->getObjId());
            $udf->setName((string) $this->form->getInput('na'));
            $udf->setType((int) $this->form->getInput('ty'));
            $udf->setValues($udf->prepareValues($this->form->getInput('va')));
            $udf->setValueOptions($this->form->getItemByPostVar('va')->getOpenAnswerIndexes()); // #14720
            $udf->enableRequired((bool) $this->form->getInput('re'));
            $udf->save();

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('ps_cdf_added_field'));
            // reset agreements
            ilMemberAgreement::_deleteByObjId($this->getObjId());
            $this->listFields();
            return;
        }
        // not valid
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
        $this->form->setValuesByPost();
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function editField(): void
    {
        if (!$this->initFielIdFromQuery()) {
            $this->listFields();
            return;
        }

        $this->initFieldForm(self::MODE_UPDATE);
        $udf = new ilCourseDefinedFieldDefinition($this->getObjId(), $this->initFielIdFromQuery());
        $this->form->getItemByPostVar('na')->setValue($udf->getName());
        $this->form->getItemByPostVar('ty')->setValue((string) $udf->getType());
        $this->form->getItemByPostVar('re')->setChecked($udf->isRequired());
        $this->form->getItemByPostVar('va')->setValues($udf->getValues());
        $this->form->getItemByPostVar('va')->setOpenAnswerIndexes($udf->getValueOptions());
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function updateField(): void
    {
        $this->initFieldForm(self::MODE_UPDATE);
        if ($this->form->checkInput()) {
            $udf = new ilCourseDefinedFieldDefinition($this->getObjId(), $this->initFielIdFromQuery());
            $udf->setName($this->form->getInput('na'));
            $udf->setType((int) $this->form->getInput('ty'));
            $prepared = $udf->prepareValues($this->form->getInput('va'));
            $udf->setValues($prepared);
            $udf->setValueOptions($this->form->getItemByPostVar('va')->getOpenAnswerIndexes());
            $udf->enableRequired((bool) $this->form->getInput('re'));
            $udf->update();

            // Finally reset member agreements
            ilMemberAgreement::_deleteByObjId($this->getObjId());
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
            $this->listFields();
            return;
        }

        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
        $this->form->setValuesByPost();
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function initFieldForm(int $a_mode): ilPropertyFormGUI
    {
        if ($this->form instanceof ilPropertyFormGUI) {
            return $this->form;
        }
        $this->form = new ilPropertyFormGUI();

        switch ($a_mode) {
            case self::MODE_CREATE:
                $this->form->setFormAction($this->ctrl->getFormAction($this));
                $this->form->setTitle($this->lng->txt('ps_cdf_add_field'));
                $this->form->addCommandButton('saveField', $this->lng->txt('save'));
                $this->form->addCommandButton('listFields', $this->lng->txt('cancel'));
                break;

            case self::MODE_UPDATE:
                $this->ctrl->setParameter($this, 'field_id', $this->initFielIdFromQuery());
                $this->form->setFormAction($this->ctrl->getFormAction($this));
                $this->form->setTitle($this->lng->txt('ps_cdf_edit_field'));
                $this->form->addCommandButton('updateField', $this->lng->txt('save'));
                $this->form->addCommandButton('listFields', $this->lng->txt('cancel'));
                break;
        }

        // Name
        $na = new ilTextInputGUI($this->lng->txt('ps_cdf_name'), 'na');
        $na->setSize(32);
        $na->setMaxLength(255);
        $na->setRequired(true);
        $this->form->addItem($na);

        // Type
        $ty = new ilRadioGroupInputGUI($this->lng->txt('ps_field_type'), 'ty');
        $ty->setRequired(true);
        $this->form->addItem($ty);

        if ($a_mode === self::MODE_UPDATE) {
            $ty->setDisabled(true); // #14888
        }

        //		Text type
        $ty_te = new ilRadioOption($this->lng->txt('ps_type_txt_long'), (string) ilCourseDefinedFieldDefinition::IL_CDF_TYPE_TEXT);
        $ty->addOption($ty_te);

        //		Select Type
        $ty_se = new ilRadioOption($this->lng->txt('ps_type_select_long'), (string) ilCourseDefinedFieldDefinition::IL_CDF_TYPE_SELECT);
        $ty->addOption($ty_se);

        // Select Type Values
        $ty_se_mu = new ilSelectBuilderInputGUI($this->lng->txt('ps_cdf_value'), 'va');
        $ty_se_mu->setAllowMove(true);
        $ty_se_mu->setRequired(true);
        $ty_se_mu->setSize(32);
        $ty_se_mu->setMaxLength(128);
        $ty_se->addSubItem($ty_se_mu);

        // Required
        $re = new ilCheckboxInputGUI($this->lng->txt('ps_cdf_required'), 're');
        $re->setValue("1");
        $this->form->addItem($re);
        return $this->form;
    }

    protected function editMember(?ilPropertyFormGUI $form = null): void
    {
        $member_id = $this->initMemberIdFromQuery();
        $this->ctrl->saveParameter($this, 'member_id');

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->clearSubTabs();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, 'cancelEditMember')
        );
        if ($form instanceof ilPropertyFormGUI) {
            $this->tpl->setContent($form->getHTML());
        } else {
            $form = $this->initMemberForm();
            ilMemberAgreementGUI::setCourseDefinedFieldValues(
                $form,
                $this->getObjId(),
                $member_id
            );
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function cancelEditMember(): void
    {
        $this->ctrl->returnToParent($this);
    }

    protected function initMemberForm(): ilPropertyFormGUI
    {
        $member_id = $this->initMemberIdFromQuery();
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $title = $this->lng->txt(ilObject::_lookupType($this->getObjId()) . '_cdf_edit_member');
        $name = ilObjUser::_lookupName($member_id);
        $title .= (': ' . $name['lastname'] . ', ' . $name['firstname']);
        $form->setTitle($title);

        ilMemberAgreementGUI::addCustomFields(
            $form,
            $this->getObjId(),
            ilObject::_lookupType($this->getObjId()),
            'edit'
        );
        $form->addCommandButton('saveMember', $this->lng->txt('save'));
        $form->addCommandButton('cancelEditMember', $this->lng->txt('cancel'));
        return $form;
    }

    protected function saveMember(): void
    {
        $member_id = $this->initMemberIdFromQuery();
        $this->ctrl->saveParameter($this, 'member_id');

        $form = $this->initMemberForm();
        if ($form->checkInput()) {
            // save history
            $history = new ilObjectCustomUserFieldHistory($this->getObjId(), $member_id);
            $history->setEditingTime(new ilDateTime(time(), IL_CAL_UNIX));
            $history->setUpdateUser($this->user->getId());
            $history->save();

            ilMemberAgreementGUI::saveCourseDefinedFields($form, $this->getObjId(), $member_id);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->returnToParent($this);
            return;
        }

        $form->setValuesByPost();
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
        $this->editMember($form);
    }
}
