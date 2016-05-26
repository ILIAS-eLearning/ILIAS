<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/DataCollection/classes/Fields/Base/class.ilDclBaseRecordModel.php");
require_once("./Modules/DataCollection/classes/Fields/Base/class.ilDclBaseFieldModel.php");
require_once("./Modules/DataCollection/classes/class.ilDclTable.php");
require_once("./Modules/DataCollection/classes/Fields/Base/class.ilDclDatatype.php");
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
require_once('./Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php');
require_once('./Modules/DataCollection/classes/Helpers/class.ilDclPropertyFormGUI.php');

/**
 * Class ilDclRecordEditGUI
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 */
class ilDclRecordEditGUI {

	/**
	 * Possible redirects after saving/updating a record - use GET['redirect'] to set constants
	 *
	 */
	const REDIRECT_RECORD_LIST = 1;
	const REDIRECT_DETAIL = 2;
	/**
	 * @var int
	 */
	protected $record_id;
	/**
	 * @var int
	 */
	protected $table_id;
	/**
	 * @var ilDclTable
	 */
	protected $table;
	/**
	 * @var ilObjDataCollectionGUI
	 */
	protected $parent_obj;
	/**
	 * @var ilDclBaseRecordModel
	 */
	protected $record;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var ilLanguage
	 */
	protected $lng;
	/**
	 * @var ilUser
	 */
	protected $user;
	/**
	 * @var ilDclPropertyFormGUI
	 */
	protected $form;


	/**
	 * @param ilObjDataCollectionGUI $parent_obj
	 */
	public function __construct(ilObjDataCollectionGUI $parent_obj) {
		global $ilCtrl, $tpl, $lng, $ilUser;

		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->user = $ilUser;
		$this->parent_obj = $parent_obj;
		$this->record_id = $_REQUEST['record_id'];
		$this->table_id = $_REQUEST['table_id'];
	}


	/**
	 * @return bool
	 */
	public function executeCommand() {
		if ($_GET['mode']) {
			$this->ctrl->saveParameter($this, 'mode');
			$this->ctrl->setParameterByClass("ildclrecordlistgui", "mode", $_GET['mode']);
		}
		$this->ctrl->saveParameter($this, 'redirect');
		if ($this->record_id) {
			$this->record = ilDclCache::getRecordCache($this->record_id);
			if (!$this->record->hasPermissionToEdit($this->parent_obj->ref_id) OR !$this->record->hasPermissionToView($this->parent_obj->ref_id)) {
				$this->accessDenied();
			}
			$this->table = $this->record->getTable();
			$this->table_id = $this->table->getId();
		} else {
			$this->table = ilDclCache::getTableCache($this->table_id);
			if (!ilObjDataCollectionAccess::hasAddRecordAccess($_GET['ref_id'])) {
				$this->accessDenied();
			}
		}

		$cmd = $this->ctrl->getCmd();
		switch ($cmd) {
			default:
				$this->$cmd();
				break;
		}

		return true;
	}


	public function create() {
		$this->initForm();
		if ($this->ctrl->isAsynch()) {
			echo $this->form->getHTML();
			exit();
		} else {
			$this->tpl->setContent( $this->getLanguageJsKeys()
				. $this->form->getHTML());
		}
	}


	public function edit() {
		$this->initForm();

		$this->setFormValues();
		if ($this->ctrl->isAsynch()) {
			echo $this->form->getHTML();
			exit();
		} else {
			$this->tpl->setContent($this->getLanguageJsKeys()
				. $this->form->getHTML());
		}
	}


	public function confirmDelete() {
		$conf = new ilConfirmationGUI();
		$conf->setFormAction($this->ctrl->getFormAction($this));
		$conf->setHeaderText($this->lng->txt('dcl_confirm_delete_record'));
		$record = ilDclCache::getRecordCache($this->record_id);

		$all_fields = $this->table->getRecordFields();
		$record_data = "";
		foreach($all_fields as $key=>$field) {
			$field_record = ilDclCache::getRecordFieldCache($record, $field);

			$record_representation = ilDclCache::getRecordRepresentation($field_record);
			if($record_representation->getConfirmationHTML() !== false) {
				$record_data .= $field->getTitle().": ".$record_representation->getConfirmationHTML() ."<br />";
			}
		}
		$conf->addItem('record_id', $record->getId(), $record_data);
		$conf->addHiddenItem('table_id', $this->table_id);
		$conf->setConfirm($this->lng->txt('delete'), 'delete');
		$conf->setCancel($this->lng->txt('cancel'), 'cancelDelete');
		$this->tpl->setContent($conf->getHTML());
	}


	public function cancelDelete() {
		$this->ctrl->redirectByClass("ildclrecordlistgui", "listRecords");
	}


	public function delete() {
		$record = ilDclCache::getRecordCache($this->record_id);

		if (!$this->table->hasPermissionToDeleteRecord($this->parent_obj->ref_id, $record)) {
			$this->accessDenied();

			return;
		}

		$record->doDelete();
		ilUtil::sendSuccess($this->lng->txt("dcl_record_deleted"), true);
		$this->ctrl->redirectByClass("ildclrecordlistgui", "listRecords");
	}


	/**
	 * Return All fields and values from a record ID. If this method is requested over AJAX,
	 * data is returned in JSON format
	 *
	 * @param int $record_id
	 *
	 * @return array
	 */
	public function getRecordData($record_id = 0) {
		$record_id = ($record_id) ? $record_id : $_GET['record_id'];
		$return = array();
		if ($record_id) {
			$record = ilDclCache::getRecordCache((int)$record_id);
			if (is_object($record)) {
				$return = $record->getRecordFieldValues();
			}
		}
		if ($this->ctrl->isAsynch()) {
			echo json_encode($return);
			exit();
		}

		return $return;
	}


	/**
	 * init Form
	 *
	 * @move move parts to RecordRepresentationGUI
	 */
	public function initForm() {
		$this->form = new ilDclPropertyFormGUI();
		$prefix = ($this->ctrl->isAsynch()) ? 'dclajax' : 'dcl'; // Used by datacolleciton.js to select input elements
		$this->form->setId($prefix . $this->table_id . $this->record_id);

		$hidden_prop = new ilHiddenInputGUI("table_id");
		$hidden_prop->setValue($this->table_id);
		$this->form->addItem($hidden_prop);
		if ($this->record_id) {
			$hidden_prop = new ilHiddenInputGUI("record_id");
			$hidden_prop->setValue($this->record_id);
			$this->form->addItem($hidden_prop);
		}

		$this->ctrl->setParameter($this, "record_id", $this->record_id);
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		$allFields = $this->table->getRecordFields();
		$inline_css = '';
		foreach ($allFields as $field) {
			$item = ilDclCache::getFieldRepresentation($field)->getInputField($this->form, $this->record_id);
			if ($item === NULL) {
				continue; // Fields calculating values at runtime, e.g. ilDclFormulaFieldModel do not have input
			}

			if (!ilObjDataCollectionAccess::hasWriteAccess($this->parent_obj->ref_id) && $field->getLocked()) {
				$item->setDisabled(true);
			}
			$this->form->addItem($item);
		}

		$this->tpl->addInlineCss($inline_css);

		// Add possibility to change the owner in edit mode
		if ($this->record_id) {
			$ownerField = $this->table->getField('owner');
			$inputfield = ilDclCache::getFieldRepresentation($ownerField)->getInputField($this->form);
			$this->form->addItem($inputfield);
		}

		// save and cancel commands
		if ($this->record_id) {
			$this->form->setTitle($this->lng->txt("dcl_update_record"));
			$this->form->addCommandButton("save", $this->lng->txt("dcl_update_record"));
			if (!$this->ctrl->isAsynch()) {
				$this->form->addCommandButton("cancelUpdate", $this->lng->txt("cancel"));
			}
		} else {
			$this->form->setTitle($this->lng->txt("dcl_add_new_record"));
			$this->form->addCommandButton("save", $this->lng->txt("save"));
			if (!$this->ctrl->isAsynch()) {
				$this->form->addCommandButton("cancelSave", $this->lng->txt("cancel"));
			}
		}
		$this->ctrl->setParameter($this, "table_id", $this->table_id);
		$this->ctrl->setParameter($this, "record_id", $this->record_id);
	}


	/**
	 * Set values from object to form
	 *
	 * @return bool
	 */
	public function setFormValues() {
		//Get Record-Values
		$record_obj = ilDclCache::getRecordCache($this->record_id);
		if ($record_obj->getId()) {
			//Get Table Field Definitions
			$allFields = $this->table->getFields();
			foreach ($allFields as $field) {
				$record_obj->fillRecordFieldFormInput($field->getId(), $this->form);
			}
		} else {
			$this->form->setValuesByPost();
		}

		return true;
	}


	/**
	 * Cancel Update
	 */
	public function cancelUpdate() {
		$this->checkAndPerformRedirect(true);
	}


	/**
	 * Cancel Save
	 */
	public function cancelSave() {
		$this->cancelUpdate();
	}

	public function saveConfirmation() {

		if(!isset($_SESSION['record_form_values'])) {
			$this->ctrl->redirect($this, "edit");
		}

		$permission = ilObjDataCollectionAccess::hasWriteAccess($this->parent_obj->ref_id);
		if ($permission) {
			$all_fields = $this->table->getRecordFields();
		} else {
			$all_fields = $this->table->getEditableFields();
		}

		$record_obj = ilDclCache::getRecordCache($this->record_id);
		$date_obj = new ilDateTime(time(), IL_CAL_UNIX);
		$record_obj->setTableId($this->table_id);
		$record_obj->setLastUpdate($date_obj->get(IL_CAL_DATETIME));
		$record_obj->setLastEditBy($this->user->getId());

		$confirmation = new ilConfirmationGUI();
		$confirmation->setFormAction($this->ctrl->getFormAction($this));
		$confirmation->setHeaderText($this->lng->txt('dcl_confirm_storing_records'));

		$confirmation->setCancel($this->lng->txt('dcl_edit_record'), 'edit');
		$confirmation->setConfirm($this->lng->txt('dcl_save_record'), 'save');
		
		$all_values = $_SESSION['record_form_values'];
		$record_data = "";

		foreach($all_fields as $key=>$field) {
			$field_record = ilDclCache::getRecordFieldCache($record_obj, $field);
			$field_record->setValue($all_values[$field->getId()]);

			$record_representation = ilDclCache::getRecordRepresentation($field_record);
			if(is_array($all_values[$field->getId()])) {
				foreach($all_values[$field->getId()] as $key=>$value) {
					$confirmation->addHiddenItem('field_'.$field->getId().'['.$key.']', $value);
				}
			} else {
				$confirmation->addHiddenItem('field_'.$field->getId(), $all_values[$field->getId()]);
			}

			if($record_representation->getConfirmationHTML() !== false) {
				$record_data .= $field->getTitle().": ".$record_representation->getConfirmationHTML() ."<br />";
			}
		}

		$hash = $all_values["ilfilehash"];
		$confirmation->addHiddenItem('ilfilehash', $hash);
		$confirmation->addHiddenItem('table_id', $this->table_id);
		$confirmation->addItem('save_confirmed', 1, $record_data);

		if ($this->ctrl->isAsynch()) {
			echo $confirmation->getHTML();
			exit();
		} else {
			$this->tpl->setContent($confirmation->getHTML());
		}
	}

	/**
	 * Save record
	 */
	public function save() {
		global $ilAppEventHandler, $ilUser;

		$this->initForm();

		// if save confirmation is enabled: Temporary file-uploads need to be handled
		if($this->table->getSaveConfirmation() && isset($_POST['save_confirmed']) && isset($_POST['ilfilehash']) && !isset($this->record_id) && !$this->ctrl->isAsynch()) {
			$restore_files = ilDclPropertyFormGUI::getTempFileByHash($_POST['ilfilehash'], $ilUser->getId());
			$_FILES = $restore_files;
			unset($_SESSION['record_form_values']);
		}

		$valid = $this->form->checkInput();

		$record_obj = ilDclCache::getRecordCache($this->record_id);
		$date_obj = new ilDateTime(time(), IL_CAL_UNIX);
		$record_obj->setTableId($this->table_id);
		$record_obj->setLastUpdate($date_obj->get(IL_CAL_DATETIME));
		$record_obj->setLastEditBy($this->user->getId());

		$create_mode = false;

		if (ilObjDataCollectionAccess::hasWriteAccess($this->parent_obj->ref_id)) {
			$all_fields = $this->table->getRecordFields();
		} else {
			$all_fields = $this->table->getEditableFields();
		}

		//Check if we can create this record.
		foreach ($all_fields as $field) {
			try {
				$value = $this->form->getInput("field_" . $field->getId());
				$field->checkValidity($value, $this->record_id);
			} catch (ilDclInputException $e) {
				$valid = false;
				$item = $this->form->getItemByPostVar('field_'.$field->getId());
				$item->setAlert($e);
			}
		}

		if (!$valid) {
			$this->sendFailure($this->lng->txt('form_input_not_valid'));
			return;
		}

		if($valid) {
			if (!isset($this->record_id)) {
				if (!($this->table->hasPermissionToAddRecord($this->parent_obj->ref_id))) {
					$this->accessDenied();

					return;
				}

				// when save_confirmation is enabled, not yet confirmed and we have not an async-request => prepare for displaying confirmation
				if ($this->table->getSaveConfirmation() && $this->form->getInput('save_confirmed') == null && !$this->ctrl->isAsynch()) {
					// temporary store fileuploads (reuse code from ilPropertyFormGUI)
					$hash = $_POST["ilfilehash"];
					foreach($_FILES as $field => $data)
					{
						if(is_array($data["tmp_name"]))
						{
							foreach($data["tmp_name"] as $idx => $upload)
							{
								if(is_array($upload))
								{
									foreach($upload as $idx2 => $file)
									{
										if($file && is_uploaded_file($file))
										{
											$file_name = $data["name"][$idx][$idx2];
											$file_type = $data["type"][$idx][$idx2];
											$this->form->keepTempFileUpload($hash, $field, $file, $file_name, $file_type, $idx, $idx2);
										}
									}
								}
								else if($upload && is_uploaded_file($upload))
								{
									$file_name = $data["name"][$idx];
									$file_type = $data["type"][$idx];
									$this->form->keepTempFileUpload($hash, $field, $upload, $file_name, $file_type, $idx);
								}
							}
						}
						else
						{
							$this->form->keepTempFileUpload($hash, $field, $data["tmp_name"], $data["name"], $data["type"]);
						}
					}

					//edit values, they are valid we already checked them above
					foreach ($all_fields as $field) {
						$record_obj->setRecordFieldValueFromForm($field->getId(), $this->form);
					}

					$_SESSION['record_form_values'] = $record_obj->getRecordFieldValues();
					$_SESSION['record_form_values']['ilfilehash'] = $hash;

					$this->ctrl->redirect($this, 'saveConfirmation');
					return;
				}

				$record_obj->setOwner($this->user->getId());
				$record_obj->setCreateDate($date_obj->get(IL_CAL_DATETIME));
				$record_obj->setTableId($this->table_id);
				$record_obj->doCreate();

				$this->record_id = $record_obj->getId();
				$create_mode = true;
			} else {
				if (!$record_obj->hasPermissionToEdit($this->parent_obj->ref_id)) {
					$this->accessDenied();

					return;
				}
			}

			//edit values, they are valid we already checked them above
			foreach ($all_fields as $field) {
				$record_obj->setRecordFieldValueFromForm($field->getId(), $this->form);
			}

			// Do we need to set a new owner for this record?
			if (!$create_mode) {
				$owner_id = ilObjUser::_lookupId($_POST['field_owner']);
				if (!$owner_id) {
					$this->sendFailure($this->lng->txt('user_not_known'));

					return;
				}
				$record_obj->setOwner($owner_id);
			}

			$dispatchEvent = "update";
			if ($create_mode) {
				$dispatchEvent = "create";
				ilObjDataCollection::sendNotification("new_record", $this->table_id, $record_obj->getId());
			}

			$record_obj->doUpdate();

			$ilAppEventHandler->raise('Modules/DataCollection',
				$dispatchEvent.'Record',
				array(
					'dcl' => $this->parent_obj->getDataCollectionObject(),
					'table_id' => $this->table_id,
					'obj_id' => $record_obj->getId(),
					'object' => $record_obj,
					));

			$this->ctrl->setParameter($this, "table_id", $this->table_id);
			$this->ctrl->setParameter($this, "record_id", $this->record_id);

			if (!$this->ctrl->isAsynch()) {
				ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
			}

			$this->checkAndPerformRedirect();
			if ($this->ctrl->isAsynch()) {
				// If ajax request, return the form in edit mode again
				$this->record_id = $record_obj->getId();
				$this->initForm();
				$this->setFormValues();
				echo $this->tpl->getMessageHTML($this->lng->txt('msg_obj_modified'), 'success') . $this->form->getHTML();
				exit();
			} else {
				$this->ctrl->redirectByClass("ildclrecordlistgui", "listRecords");
			}
		} else {
			// Form not valid...
			//TODO: URL title flushes on invalid form
			$this->form->setValuesByPost();
			if ($this->ctrl->isAsynch()) {
				echo $this->form->getHTML();
				exit();
			} else {
				$this->tpl->setContent($this->getLanguageJsKeys() . $this->form->getHTML());
			}
		}
	}


	/**
	 * Checkes to what view (table or detail) should be redirected and performs redirect
	 *
	 */
	protected function checkAndPerformRedirect($force_redirect = false) {
		if ($force_redirect || (isset($_GET['redirect']) && !$this->ctrl->isAsynch())) {
			switch ((int)$_GET['redirect']) {
				case self::REDIRECT_DETAIL:
					$this->ctrl->setParameterByClass('ildclrecordviewgui', 'record_id', $this->record_id);
					$this->ctrl->setParameterByClass('ildclrecordviewgui', 'table_id', $this->table_id);
					$this->ctrl->redirectByClass("ildclrecordviewgui", "renderRecord");
					break;
				case self::REDIRECT_RECORD_LIST:
					$this->ctrl->redirectByClass("ildclrecordlistgui", "listRecords");
					break;
				default:
					$this->ctrl->redirectByClass("ildclrecordlistgui", "listRecords");
			}
		}
	}


	protected function accessDenied() {
		if (!$this->ctrl->isAsynch()) {
			ilUtil::sendFailure($this->lng->txt('dcl_msg_no_perm_edit'), true);
			$this->ctrl->redirectByClass('ildclrecordlistgui', 'listRecords');
		} else {
			echo $this->lng->txt('dcl_msg_no_perm_edit');
			exit();
		}
	}


	/**
	 * @param $message
	 */
	protected function sendFailure($message) {
		$keep = ($this->ctrl->isAsynch()) ? false : true;
		$this->form->setValuesByPost();
		if ($this->ctrl->isAsynch()) {
			echo $this->tpl->getMessageHTML($message, 'failure') . $this->form->getHTML();
			exit();
		} else {
			ilUtil::sendFailure($message, $keep);
			$this->tpl->setContent($this->getLanguageJsKeys().$this->form->getHTML());
		}
	}

	/**
	 * This function is only used by the ajax request if searching for ILIAS references. It builds the html for the search results.
	 */
	public function searchObjects() {
		$search = $_POST['search_for'];
		$dest = $_POST['dest'];
		$html = "";
		include_once './Services/Search/classes/class.ilQueryParser.php';
		$query_parser = new ilQueryParser($search);
		$query_parser->setMinWordLength(1, true);
		$query_parser->setCombination(QP_COMBINATION_AND);
		$query_parser->parse();
		if (!$query_parser->validate()) {
			$html .= $query_parser->getMessage() . "<br />";
		}

		// only like search since fulltext does not support search with less than 3 characters
		include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
		$object_search = new ilLikeObjectSearch($query_parser);
		$res = $object_search->performSearch();
		//$res->setRequiredPermission('copy');
		$res->filter(ROOT_FOLDER_ID, true);

		if (!count($results = $res->getResultsByObjId())) {
			$html .= $this->lng->txt('dcl_no_search_results_found_for') . ' ' . $search . "<br />";
		}
		$results = $this->parseSearchResults($results);

		foreach ($results as $entry) {
			$tpl = new ilTemplate("tpl.dcl_tree.html", true, true, "Modules/DataCollection");
			foreach ((array)$entry['refs'] as $reference) {
				include_once './Services/Tree/classes/class.ilPathGUI.php';
				$path = new ilPathGUI();
				$tpl->setCurrentBlock('result');
				$tpl->setVariable('RESULT_PATH', $path->getPath(ROOT_FOLDER_ID, $reference) . " » " . $entry['title']);
				$tpl->setVariable('RESULT_REF', $reference);
				$tpl->setVariable('FIELD_ID', $dest);
				$tpl->parseCurrentBlock();
			}
			$html .= $tpl->get();
		}

		echo $html;
		exit;
	}

	protected function getLanguageJsKeys() {
		return "<script>ilDataCollection.strings.add_value='" . $this->lng->txt('add_value') . "';</script>";
	}


	/**
	 * Parse search results
	 *
	 * @param ilObject[] $a_res
	 *
	 * @return array
	 */
	protected function parseSearchResults($a_res) {
		$rows = array();
		foreach ($a_res as $obj_id => $references) {
			$r = array();
			$r['title'] = ilObject::_lookupTitle($obj_id);
			$r['desc'] = ilObject::_lookupDescription($obj_id);
			$r['obj_id'] = $obj_id;
			$r['refs'] = $references;
			$rows[] = $r;
		}

		return $rows;
	}
}

?>