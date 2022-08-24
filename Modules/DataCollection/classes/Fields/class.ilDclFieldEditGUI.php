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
 ********************************************************************
 */

/**
 * Class ilDclFieldEditGUI
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 * @ingroup ModulesDataCollection
 */
class ilDclFieldEditGUI
{
    protected int $obj_id;
    protected int $table_id;

    protected ilDclTableListGUI $parent_obj;
    protected ilDclTable $table;
    protected ilPropertyFormGUI $form;
    protected ilDclBaseFieldModel $field_obj;
    private ilGlobalTemplateInterface $main_tpl;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;
    protected int $field_id;

    /**
     * Constructor
     */
    public function __construct(ilDclTableListGUI $a_parent_obj)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $ilCtrl = $DIC['ilCtrl'];

        $this->obj_id = $a_parent_obj->getObjId();
        $this->parent_obj = $a_parent_obj;
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

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
                    $this->refinery->kindlyTo()->string()
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

    /**
     * execute command
     */
    public function executeCommand(): void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilCtrl->saveParameter($this, 'field_id');

        $cmd = $ilCtrl->getCmd();

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

    /**
     * create field add form
     */
    public function create(): void
    {
        global $DIC;
        $tpl = $DIC['tpl'];

        $this->initForm();
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * create field edit form
     */
    public function edit(): void
    {
        global $DIC;
        $tpl = $DIC['tpl'];

        $this->initForm("edit");

        $this->field_obj->fillPropertiesForm($this->form);

        $tpl->setContent($this->form->getHTML());
    }

    /*
     * permissionDenied
     */
    public function permissionDenied(): void
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $tpl->setContent("Permission denied");
    }

    /**
     * confirmDelete
     */
    public function confirmDelete(): void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];

        $conf = new ilConfirmationGUI();
        $conf->setFormAction($ilCtrl->getFormAction($this));
        $conf->setHeaderText($lng->txt('dcl_confirm_delete_field'));

        $conf->addItem('field_id', (int) $this->field_obj->getId(), $this->field_obj->getTitle());

        $conf->setConfirm($lng->txt('delete'), 'delete');
        $conf->setCancel($lng->txt('cancel'), 'cancelDelete');

        $tpl->setContent($conf->getHTML());
    }

    /**
     * cancelDelete
     */
    public function cancelDelete(): void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $ilCtrl->redirectByClass("ildclfieldlistgui", "listFields");
    }

    /*
     * delete
     */
    public function delete(): void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $this->table->deleteField($this->field_obj->getId());
        $ilCtrl->redirectByClass("ildclfieldlistgui", "listFields");
    }

    /*
     * cancel
     */
    public function cancel(): void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilCtrl->redirectByClass("ildclfieldlistgui", "listFields");
    }

    /**
     * initEditCustomForm
     * @param string $a_mode values: create | edit
     */
    public function initForm(string $a_mode = "create"): void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $this->form = new ilPropertyFormGUI();

        if ($a_mode == "edit") {
            $this->form->setTitle($lng->txt('dcl_edit_field'));
            $hidden_prop = new ilHiddenInputGUI("field_id");
            $this->form->addItem($hidden_prop);

            $this->form->setFormAction($ilCtrl->getFormAction($this));

            $this->form->addCommandButton('update', $lng->txt('dcl_update_field'));
        } else {
            $this->form->setTitle($lng->txt('dcl_new_field'));
            $hidden_prop = new ilHiddenInputGUI("table_id");
            $hidden_prop->setValue($this->field_obj->getTableId());
            $this->form->addItem($hidden_prop);

            $this->form->setFormAction($ilCtrl->getFormAction($this));

            $this->form->addCommandButton('save', $lng->txt('dcl_create_field'));
        }
        $this->form->addCommandButton('cancel', $lng->txt('cancel'));

        $text_prop = new ilTextInputGUI($lng->txt("title"), "title");
        $text_prop->setRequired(true);
        $text_prop->setInfo(sprintf(
            $lng->txt('fieldtitle_allow_chars'),
            ilDclBaseFieldModel::_getTitleInvalidChars(false)
        ));
        $text_prop->setValidationRegexp(ilDclBaseFieldModel::_getTitleInvalidChars(true));
        $this->form->addItem($text_prop);

        // Description
        $text_prop = new ilTextAreaInputGUI($lng->txt("dcl_field_description"), "description");
        $this->form->addItem($text_prop);

        $edit_datatype = new ilRadioGroupInputGUI($lng->txt('dcl_datatype'), 'datatype');

        foreach (ilDclDatatype::getAllDatatype() as $datatype) {
            $model = new ilDclBaseFieldModel();
            $model->setDatatypeId($datatype->getId());

            if ($a_mode == 'edit' && $datatype->getId() == $this->field_obj->getDatatypeId()) {
                $model = $this->field_obj;
            }

            $field_representation = ilDclFieldFactory::getFieldRepresentationInstance($model);
            $field_representation->addFieldCreationForm($edit_datatype, $this->getDataCollectionObject(), $a_mode);
        }
        $edit_datatype->setRequired(true);

        //you can't change type but we still need it in POST
        if ($a_mode == "edit") {
            $edit_datatype->setDisabled(true);
        }
        $this->form->addItem($edit_datatype);

        //Unique
        $cb = new ilCheckboxInputGUI($lng->txt("dcl_unique"), "unique");
        $cb->setInfo($lng->txt('dcl_unique_desc'));
        $this->form->addItem($cb);
    }

    /**
     * save Field
     * @param string $a_mode values: create | update
     */
    public function save(string $a_mode = "create"): void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];

        $this->initForm($a_mode == "update" ? "edit" : "create");

        if ($this->checkInput($a_mode)) {
            // check if confirmation is needed and if so, fetch and render confirmationGUI
            if (($a_mode == "update") && !($this->form->getInput('confirmed')) && $this->field_obj->isConfirmationRequired($this->form)) {
                $ilConfirmationGUI = $this->field_obj->getConfirmationGUI($this->form);
                $tpl->setContent($ilConfirmationGUI->getHTML());

                return;
            }

            $title = $this->form->getInput("title");
            if ($a_mode != "create" && $title != $this->field_obj->getTitle()) {
                $this->main_tpl->setOnScreenMessage('info', $lng->txt("dcl_field_title_change_warning"), true);
            }

            $this->field_obj->setTitle($title);
            $this->field_obj->setDescription($this->form->getInput("description"));
            $this->field_obj->setDatatypeId($this->form->getInput("datatype"));
            $this->field_obj->setUnique($this->form->getInput("unique"));

            if ($a_mode == "update") {
                $this->field_obj->doUpdate();
            } else {
                $this->field_obj->setOrder($this->table->getNewFieldOrder());
                $this->field_obj->doCreate();
            }

            // Get possible properties and save them
            $this->field_obj->storePropertiesFromForm($this->form);

            $ilCtrl->setParameter($this, "field_id", $this->field_obj->getId());

            if ($a_mode == "update") {
                $this->main_tpl->setOnScreenMessage('success', $lng->txt("dcl_msg_field_modified"), true);
            } else {
                $this->table->addField($this->field_obj);
                $this->table->buildOrderFields();
                $this->main_tpl->setOnScreenMessage('success', $lng->txt("msg_field_created"), false);
            }
            $ilCtrl->redirectByClass(strtolower("ilDclFieldListGUI"), "listFields");
        } else {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
        }
    }

    /**
     * Check input of form
     * @param $a_mode 'create' | 'update'
     * @return bool
     */
    protected function checkInput(string $a_mode): bool
    {
        global $DIC;
        $lng = $DIC['lng'];
        $return = $this->form->checkInput();

        // load specific model for input checking
        $datatype_id = $this->form->getInput('datatype');
        if ($datatype_id != null && is_numeric($datatype_id)) {
            $base_model = new ilDclBaseFieldModel();
            $base_model->setDatatypeId($datatype_id);
            $field_validation_class = ilDclFieldFactory::getFieldModelInstanceByClass($base_model);

            if (!$field_validation_class->checkFieldCreationInput($this->form)) {
                $return = false;
            }
        }

        // Don't allow multiple fields with the same title in this table
        if ($a_mode == 'create') {
            if ($title = $this->form->getInput('title')) {
                if (ilDclTable::_hasFieldByTitle($title, $this->table_id)) {
                    $inputObj = $this->form->getItemByPostVar('title');
                    $inputObj->setAlert($lng->txt("dcl_field_title_unique"));
                    $return = false;
                }
            }
        }

        if (!$return) {
            $this->main_tpl->setOnScreenMessage('failure', $lng->txt("form_input_not_valid"));
        }

        return $return;
    }

    /**
     * @return bool
     */
    protected function checkAccess(): bool
    {
        if ($field_id = $this->field_obj->getId()) {
            return ilObjDataCollectionAccess::hasAccessToField(
                $this->getDataCollectionObject()->getRefId(),
                $this->table_id,
                $field_id
            );
        } else {
            return ilObjDataCollectionAccess::hasAccessToFields(
                $this->getDataCollectionObject()->getRefId(),
                $this->table_id
            );
        }
    }

    /**
     * @return ilObjDataCollection
     */
    public function getDataCollectionObject(): ilObjDataCollection
    {
        return $this->parent_obj->getDataCollectionObject();
    }
}
