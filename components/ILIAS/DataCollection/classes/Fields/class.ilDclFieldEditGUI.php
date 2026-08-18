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

use ILIAS\HTTP\Services;
use ILIAS\Refinery\Factory;

class ilDclFieldEditGUI
{
    protected int $obj_id;
    protected int $table_id;

    protected ilDclTableListGUI $parent_obj;
    protected ilDclTable $table;
    protected ilPropertyFormGUI $form;
    protected ilDclBaseFieldModel $field_obj;
    private ilGlobalTemplateInterface $main_tpl;
    private ilLanguage $lng;
    protected ilHelpGUI $help;
    protected Services $http;
    protected Factory $refinery;
    protected int $field_id;
    private ilCtrlInterface $ctrl;

    public function __construct(ilDclTableListGUI $a_parent_obj)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $ilCtrl = $DIC->ctrl();

        $this->obj_id = $a_parent_obj->getObjId();
        $this->parent_obj = $a_parent_obj;
        $this->help = $DIC->help();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        $this->table_id = $this->http->wrapper()->query()->retrieve('table_id', $this->refinery->kindlyTo()->int());

        $hasFieldId = $this->http->wrapper()->query()->has('field_id');
        if ($hasFieldId) {
            $this->field_id = $this->http->wrapper()->query()->retrieve('field_id', $this->refinery->kindlyTo()->int());
        } else {
            $this->field_id = 0;
        }

        if ($this->field_id) {
            $this->field_obj = ilDclCache::getFieldCache($this->field_id);
        } else {
            $datatype = null;

            $has_datatype = $this->http->wrapper()->post()->has('datatype');

            if ($has_datatype) {
                $datatype_value = $this->http->wrapper()->post()->retrieve(
                    'datatype',
                    $this->refinery->kindlyTo()->int()
                );
                if (in_array(
                    $datatype_value,
                    array_keys(ilDclDatatype::getAllDatatype())
                )) {
                    $datatype = $datatype_value;
                }
            }
            $this->field_obj = ilDclFieldFactory::getFieldModelInstance($this->field_id, $datatype);
            if (!$this->table_id) {
                $ilCtrl->redirectByClass("ilDclTableListGUI", "listFields");
            }
            $this->field_obj->setTableId($this->table_id);
            $ilCtrl->saveParameter($this, "table_id");
        }

        $this->table = ilDclCache::getTableCache($this->table_id);
    }

    public function executeCommand(): void
    {
        $this->ctrl->saveParameter($this, 'field_id');
        $cmd = $this->ctrl->getCmd();

        if (!$this->checkAccess()) {
            $this->permissionDenied();
            return;
        }

        switch ($cmd) {
            case "update":
                $this->save("update");
                break;
            default:
                $this->$cmd();
                break;
        }
    }

    public function create(): void
    {
        $this->help->setSubScreenId('create');

        $this->initForm();
        $this->main_tpl->setContent($this->form->getHTML());
    }

    public function edit(): void
    {
        $this->help->setSubScreenId('edit');

        $this->initForm("edit");
        $this->field_obj->fillPropertiesForm($this->form);
        $this->main_tpl->setContent($this->form->getHTML());
    }

    public function permissionDenied(): void
    {
        $this->main_tpl->setContent("Permission denied");
    }

    public function confirmDelete(): void
    {
        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setHeaderText($this->lng->txt('dcl_confirm_delete_field'));

        $conf->addItem('field_id', $this->field_obj->getId(), $this->field_obj->getTitle());

        $conf->setConfirm($this->lng->txt('delete'), 'delete');
        $conf->setCancel($this->lng->txt('cancel'), 'cancelDelete');

        $this->main_tpl->setContent($conf->getHTML());
    }

    public function cancelDelete(): void
    {
        $this->ctrl->redirectByClass("ildclfieldlistgui", "listFields");
    }

    public function delete(): void
    {
        $this->table->deleteField((int) $this->field_obj->getId());
        $this->ctrl->redirectByClass("ildclfieldlistgui", "listFields");
    }

    public function cancel(): void
    {
        $this->ctrl->redirectByClass("ildclfieldlistgui", "listFields");
    }

    public function initForm(string $a_mode = "create"): void
    {
        $this->form = new ilPropertyFormGUI();

        if ($a_mode == "edit") {
            $this->form->setTitle($this->lng->txt('dcl_edit_field'));
            $hidden_prop = new ilHiddenInputGUI("field_id");
            $this->form->addItem($hidden_prop);

            $this->form->setFormAction($this->ctrl->getFormAction($this));

            $this->form->addCommandButton('update', $this->lng->txt('dcl_update_field'));
        } else {
            $this->form->setTitle($this->lng->txt('dcl_new_field'));
            $hidden_prop = new ilHiddenInputGUI("table_id");
            $hidden_prop->setValue((string) $this->field_obj->getTableId());
            $this->form->addItem($hidden_prop);

            $this->form->setFormAction($this->ctrl->getFormAction($this));

            $this->form->addCommandButton('save', $this->lng->txt('dcl_create_field'));
        }
        $this->form->addCommandButton('cancel', $this->lng->txt('cancel'));

        $text_prop = new ilTextInputGUI($this->lng->txt("title"), "title");
        $text_prop->setRequired(true);
        $text_prop->setInfo(sprintf(
            $this->lng->txt('fieldtitle_allow_chars'),
            ilDclBaseFieldModel::_getTitleInvalidChars(false)
        ));
        $text_prop->setValidationRegexp(ilDclBaseFieldModel::_getTitleInvalidChars());
        $this->form->addItem($text_prop);

        $text_prop = new ilTextAreaInputGUI($this->lng->txt("dcl_field_description"), "description");
        $text_prop->setInfo($this->lng->txt('dcl_field_description_desc'));
        $this->form->addItem($text_prop);

        $edit_datatype = new ilRadioGroupInputGUI($this->lng->txt('dcl_datatype'), 'datatype');

        if ($a_mode === 'edit') {
            $field_representation = ilDclFieldFactory::getFieldRepresentationInstance($this->field_obj);
            $field_representation->addFieldCreationForm($edit_datatype, $this->getDataCollectionObject(), $a_mode);
            $edit_datatype->setDisabled(true);
        } else {
            foreach (ilDclDatatype::getAllDatatype() as $datatype) {
                $model = new ilDclBaseFieldModel();
                $model->setDatatypeId($datatype->getId());
                $model = ilDclFieldFactory::getFieldModelInstanceByClass($model);
                $field_representation = ilDclFieldFactory::getFieldRepresentationInstance($model);
                $field_representation->addFieldCreationForm($edit_datatype, $this->getDataCollectionObject());
            }
        }
        $edit_datatype->setRequired(true);
        $this->form->addItem($edit_datatype);

    }

    public function save(string $a_mode = "create"): void
    {
        $this->initForm($a_mode == "update" ? "edit" : "create");

        if ($this->checkInput($a_mode)) {

            // check if confirmation is needed and if so, fetch and render confirmationGUI
            if (($a_mode == "update") && !($this->form->getInput('confirmed')) && $this->field_obj->isConfirmationRequired($this->form)) {
                $ilConfirmationGUI = $this->field_obj->getConfirmationGUI($this->form);
                $this->main_tpl->setContent($ilConfirmationGUI->getHTML());

                return;
            }

            $title = $this->form->getInput("title");
            if ($a_mode != "create" && $title != $this->field_obj->getTitle()) {
                $this->main_tpl->setOnScreenMessage('info', $this->lng->txt("dcl_field_title_change_warning"), true);
            }

            $this->field_obj->setTitle($title);
            $this->field_obj->setDescription($this->form->getInput("description"));
            $this->field_obj->setDatatypeId((int) $this->form->getInput("datatype"));

            if ($a_mode == "update") {
                $this->field_obj->doUpdate();
            } else {
                $this->field_obj->setOrder($this->table->getNewFieldOrder());
                $this->field_obj->doCreate();
            }

            $this->field_obj->storePropertiesFromForm($this->form);

            $this->ctrl->setParameter($this, "field_id", $this->field_obj->getId());

            if ($a_mode == "update") {
                $this->main_tpl->setOnScreenMessage('success', $this->lng->txt("dcl_msg_field_modified"), true);
            } else {
                $this->table->addField($this->field_obj);
                $this->table->buildOrderFields();
                $this->main_tpl->setOnScreenMessage('success', $this->lng->txt("msg_field_created"));
            }
            $this->ctrl->redirectByClass(strtolower("ilDclFieldListGUI"), "listFields");
        } else {
            $this->form->setValuesByPost();
            $this->main_tpl->setContent($this->form->getHTML());
        }
    }

    protected function checkInput(string $a_mode): bool
    {
        $return = $this->form->checkInput();

        if (!$this->field_obj->checkFieldCreationInput($this->form)) {
            $return = false;
        }

        // Don't allow multiple fields with the same title in this table
        if ($a_mode == 'create') {
            if ($title = $this->form->getInput('title')) {
                if (ilDclTable::_hasFieldByTitle($title, $this->table_id)) {
                    $inputObj = $this->form->getItemByPostVar('title');
                    $inputObj->setAlert($this->lng->txt("dcl_field_title_unique"));
                    $return = false;
                }
            }
        }

        if (!$return) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("form_input_not_valid"));
        }

        return $return;
    }

    protected function checkAccess(): bool
    {
        if ($field_id = $this->field_obj->getId()) {
            return ilObjDataCollectionAccess::hasAccessToField(
                $this->getDataCollectionObject()->getRefId(),
                $this->table_id,
                (int) $field_id
            );
        } else {
            return ilObjDataCollectionAccess::hasAccessToFields(
                $this->getDataCollectionObject()->getRefId(),
                $this->table_id
            );
        }
    }

    public function getDataCollectionObject(): ilObjDataCollection
    {
        return $this->parent_obj->getDataCollectionObject();
    }
}
