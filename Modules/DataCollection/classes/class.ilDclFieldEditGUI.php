<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Modules/DataCollection/classes/Fields/Base/class.ilDclBaseFieldModel.php");
require_once("./Modules/DataCollection/classes/Fields/Base/class.ilDclDatatype.php");
require_once("./Modules/DataCollection/classes/class.ilDclTable.php");
require_once("class.ilDclCache.php");
require_once('./Services/Form/classes/class.ilNonEditableValueGUI.php');
require_once("./Modules/DataCollection/classes/Fields/Base/class.ilDclFieldProperty.php");
require_once('./Modules/DataCollection/classes/Fields/Plugin/class.ilDclFieldTypePlugin.php');

/**
 * Class ilDclFieldEditGUI
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 *
 *
 * @ingroup ModulesDataCollection
 */
class ilDclFieldEditGUI {
    /**
     * @var int
     */
	protected $obj_id;

    /**
     * @var int
     */
    protected $table_id;

    /**
     * @var ilObjDataCollectionGUI|object
     */
    protected $parent_obj;

    /**
     * @var ilDclTable
     */
    protected $table;

    /**
     * @var ilPropertyFormGUI
     */
    protected $form;

	/**
	 * Constructor
	 *
	 * @param    object $a_parent_obj
	 * @param    int    $table_id We need a table_id if no field_id is set (creation mode). We ignore the table_id by edit mode
	 * @param    int    $field_id The field_id of a existing fiel (edit mode)
	 */
	public function __construct(ilObjDataCollectionGUI $a_parent_obj, $table_id, $field_id) {
		global $ilCtrl;

		$this->obj_id = $a_parent_obj->obj_id;
		$this->parent_obj = $a_parent_obj;
		$this->table_id = $table_id;
		if (!$table_id) {
			$table_id = $_GET["table_id"];
		}

		if (!isset($field_id)) {
			$this->field_id = $_GET['field_id'];
		}

		if (isset($field_id)) {
			$this->field_obj = ilDclCache::getFieldCache($field_id);
		} else {
			$datatype = null;
			if(isset($_POST['datatype']) && in_array($_POST['datatype'], array_keys(ilDclDatatype::getAllDatatype()))) {
				$datatype = $_POST['datatype'];
			}
			$this->field_obj = ilDclFieldFactory::getFieldModelInstance($field_id, $datatype);
			if (!$table_id) {
				$ilCtrl->redirectByClass("ilDataCollectionGUI", "listFields");
			}
			$this->field_obj->setTableId($table_id);
			$ilCtrl->saveParameter($this, "table_id");
		}

		$this->table = ilDclCache::getTableCache($table_id);
	}


	/**
	 * execute command
	 */
	public function executeCommand() {
		global $tpl, $ilCtrl, $ilUser;

		$cmd = $ilCtrl->getCmd();

		if (!$this->table->hasPermissionToFields($this->parent_obj->ref_id)) {
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

		return true;
	}


	/**
	 * create field add form
	 */
	public function create() {
		global $tpl;

		$this->initForm();
		$tpl->setContent($this->form->getHTML());
	}


	/**
	 * create field edit form
	 */
	public function edit() {
		global $tpl;

		$this->initForm("edit");
		$this->getValues();

		$tpl->setContent($this->form->getHTML());
	}


	/*
	 * permissionDenied
	 */
	public function permissionDenied() {
		global $tpl;
		$tpl->setContent("Permission denied");
	}


	/**
	 * confirmDelete
	 */
	public function confirmDelete() {
		global $ilCtrl, $lng, $tpl;

		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$conf = new ilConfirmationGUI();
		$conf->setFormAction($ilCtrl->getFormAction($this));
		$conf->setHeaderText($lng->txt('dcl_confirm_delete_field'));

		$conf->addItem('field_id', (int)$this->field_obj->getId(), $this->field_obj->getTitle());

		$conf->setConfirm($lng->txt('delete'), 'delete');
		$conf->setCancel($lng->txt('cancel'), 'cancelDelete');

		$tpl->setContent($conf->getHTML());
	}


	/**
	 * cancelDelete
	 */
	public function cancelDelete() {
		global $ilCtrl;

		$ilCtrl->redirectByClass("ildclfieldlistgui", "listFields");
	}


	/*
	 * delete
	 */
	public function delete() {
		global $ilCtrl;

		$this->table->deleteField($this->field_obj->getId());
		$ilCtrl->redirectByClass("ildclfieldlistgui", "listFields");
	}


	/*
	 * cancel
	 */
	public function cancel() {
		global $ilCtrl;
		$ilCtrl->redirectByClass("ildclfieldlistgui", "listFields");
	}


	/**
	 * initEditCustomForm
	 *
	 * @param string $a_mode values: create | edit
	 */
	public function initForm($a_mode = "create") {
		global $ilCtrl, $lng;

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		if ($a_mode == "edit") {
			$this->form->setTitle($lng->txt('dcl_edit_field'));
			$hidden_prop = new ilHiddenInputGUI("field_id");
			$this->form->addItem($hidden_prop);

			$this->form->setFormAction($ilCtrl->getFormAction($this), "update");

			$this->form->addCommandButton('update', $lng->txt('dcl_update_field'));
		} else {
			$this->form->setTitle($lng->txt('dcl_new_field'));
			$hidden_prop = new ilHiddenInputGUI("table_id");
			$hidden_prop->setValue($this->field_obj->getTableId());
			$this->form->addItem($hidden_prop);

			$this->form->setFormAction($ilCtrl->getFormAction($this), "save");

			$this->form->addCommandButton('save', $lng->txt('dcl_create_field'));
		}
		$this->form->addCommandButton('cancel', $lng->txt('cancel'));

		$text_prop = new ilTextInputGUI($lng->txt("title"), "title");
		$text_prop->setRequired(true);
		$text_prop->setInfo(sprintf($lng->txt('fieldtitle_allow_chars'), ilDclBaseFieldModel::_getTitleValidChars(false)));
		$text_prop->setValidationRegexp(ilDclBaseFieldModel::_getTitleValidChars(true));
		$this->form->addItem($text_prop);

        // Description
        $text_prop = new ilTextAreaInputGUI($lng->txt("dcl_field_description"), "description");
        $this->form->addItem($text_prop);

        $edit_datatype = new ilRadioGroupInputGUI($lng->txt('dcl_datatype'), 'datatype');

		foreach (ilDclDatatype::getAllDatatype() as $datatype) {
			$model = new ilDclBaseFieldModel();
			$model->setDatatypeId($datatype->getId());

			if($a_mode == 'edit' && $datatype->getId() == $this->field_obj->getDatatypeId()) {
				$model = $this->field_obj;
			}

			$field_representation = ilDclFieldFactory::getFieldRepresentationInstance($model);
			$field_representation->addFieldCreationForm($edit_datatype, $this->parent_obj->getDataCollectionObject(), $a_mode);
		}
		$edit_datatype->setRequired(true);

		//you can't change type but we still need it in POST
		if ($a_mode == "edit") {
			$edit_datatype->setDisabled(true);
		}
		$this->form->addItem($edit_datatype);

		// Required
		$cb = new ilCheckboxInputGUI($lng->txt("dcl_field_required"), "required");
		$this->form->addItem($cb);

		//Unique
		$cb = new ilCheckboxInputGUI($lng->txt("dcl_unique"), "unique");
        $cb->setInfo($lng->txt('dcl_unique_desc'));
		$this->form->addItem($cb);
	}


	/**
	 * getFieldValues
	 */
	public function getValues() {
		//Std-Values
		$values = array(
			'table_id' => $this->field_obj->getTableId(),
			'field_id' => $this->field_obj->getId(),
			'title' => $this->field_obj->getTitle(),
			'datatype' => $this->field_obj->getDatatypeId(),
			'description' => $this->field_obj->getDescription(),
			'required' => $this->field_obj->getRequired(),
			'unique' => $this->field_obj->isUnique(),
		);

		$properties = $this->field_obj->getValidFieldProperties();
		foreach ($properties as $prop) {
			$values['prop_' . $prop] = $this->field_obj->getProperty($prop);
		}

		$this->form->setValuesByArray($values);

		return true;
	}


	/**
	 * save Field
	 *
	 * @param string $a_mode values: create | update
	 */
	public function save($a_mode = "create") {
		global $ilCtrl, $lng, $tpl;

		//check access
		if (!$this->table->hasPermissionToFields($this->parent_obj->ref_id)) {
			$this->accessDenied();

			return;
		}

		$this->initForm($a_mode == "update" ? "edit" : "create");

		if ($this->checkInput($a_mode)) {
			$title = $this->form->getInput("title");
			if ($a_mode != "create" && $title != $this->field_obj->getTitle()) {
				ilUtil::sendInfo($lng->txt("dcl_field_title_change_warning"), true);
			}

			$this->field_obj->setTitle($title);
			$this->field_obj->setDescription($this->form->getInput("description"));
			$this->field_obj->setDatatypeId($this->form->getInput("datatype"));
			$this->field_obj->setRequired($this->form->getInput("required"));
			$this->field_obj->setUnique($this->form->getInput("unique"));

			if ($a_mode == "update") {
				$this->field_obj->doUpdate();
			} else {
				$this->field_obj->setVisible(true);
				$this->field_obj->setOrder($this->table->getNewOrder());
				$this->field_obj->doCreate();
			}

			// Get possible properties and save them
			$field_props = $this->field_obj->getValidFieldProperties();
			foreach ($field_props as $property) {
				$representation = ilDclFieldFactory::getFieldRepresentationInstance($this->field_obj);
				$value = $this->form->getInput($representation->getPropertyInputFieldId($property));

				// save non empty values and set them to null, when they already exist. Do not override plugin-hook when already set.
				if(!empty($value) || ($this->field_obj->getPropertyInstance($property) != NULL && $property != ilDclBaseFieldModel::PROP_PLUGIN_HOOK_NAME)) {
					$this->field_obj->setProperty($property, $value)->store();
				}
			}

			$ilCtrl->setParameter($this, "field_id", $this->field_obj->getId());

			if ($a_mode == "update") {
				ilUtil::sendSuccess($lng->txt("dcl_msg_field_modified"), true);
			} else {
				$this->table->addField($this->field_obj);
				$this->table->buildOrderFields();
				ilUtil::sendSuccess($lng->txt("msg_field_created"), false);
			}
			$ilCtrl->redirectByClass(strtolower("ilDclFieldListGUI"), "listFields");
		} else {
			$this->form->setValuesByPost();
			$tpl->setContent($this->form->getHTML());
		}
	}


	/**
	 * Check input of form
	 *
	 * @param $a_mode 'create' | 'update'
	 *
	 * @return bool
	 */
	protected function checkInput($a_mode) {
		global $lng;
		$return = $this->form->checkInput();

		// load specific model for input checking
		$datatype_id = $this->form->getInput('datatype');
		if($datatype_id != null && is_numeric($datatype_id)) {
			$base_model = new ilDclBaseFieldModel();
			$base_model->setDatatypeId($datatype_id);
			$field_validation_class = ilDclFieldFactory::getFieldModelInstanceByClass($base_model);

			if(!$field_validation_class->checkFieldCreationInput($this->form)) {
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
			ilUtil::sendFailure($lng->txt("form_input_not_valid"));
		}

		return $return;
	}


	/*
	 * accessDenied
	 */
	private function accessDenied() {
		global $tpl;
		$tpl->setContent("Access Denied");
	}
}

?>