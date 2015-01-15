<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/DataCollection/classes/class.ilDataCollectionRecord.php");
require_once("./Modules/DataCollection/classes/class.ilDataCollectionField.php");
require_once("./Modules/DataCollection/classes/class.ilDataCollectionTable.php");
require_once("./Modules/DataCollection/classes/class.ilDataCollectionDatatype.php");
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
require_once('./Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php');

/**
 * Class ilDataCollectionRecordEditGUI
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 */
class ilDataCollectionRecordEditGUI {

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
	 * @var ilDataCollectionTable
	 */
	protected $table;
	/**
	 * @var ilObjDataCollectionGUI
	 */
	protected $parent_obj;
	/**
	 * @var ilDataCollectionRecord
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
	 * @var ilPropertyFormGUI
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
		$this->ctrl->saveParameter($this, 'redirect');
		if ($this->record_id) {
			$this->record = ilDataCollectionCache::getRecordCache($this->record_id);
			if (!$this->record->hasPermissionToEdit($this->parent_obj->ref_id) OR !$this->record->hasPermissionToView($this->parent_obj->ref_id)) {
				$this->accessDenied();
			}
			$this->table = $this->record->getTable();
			$this->table_id = $this->table->getId();
		} else {
			$this->table = ilDataCollectionCache::getTableCache($this->table_id);
			if (!ilObjDataCollectionAccess::_hasAddRecordAccess($_GET['ref_id'])) {
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
			$this->tpl->setContent("<script>ilDataCollection.strings.add_value='" . $this->lng->txt('add_value') . "';</script>"
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
			$this->tpl->setContent("<script>ilDataCollection.strings.add_value='" . $this->lng->txt('add_value') . "';</script>"
				. $this->form->getHTML());
		}
	}


	public function confirmDelete() {
		$conf = new ilConfirmationGUI();
		$conf->setFormAction($this->ctrl->getFormAction($this));
		$conf->setHeaderText($this->lng->txt('dcl_confirm_delete_record'));
		$record = ilDataCollectionCache::getRecordCache($this->record_id);
		$conf->addItem('record_id', $record->getId(), implode(", ", $record->getRecordFieldValues()));
		$conf->addHiddenItem('table_id', $this->table_id);
		$conf->setConfirm($this->lng->txt('delete'), 'delete');
		$conf->setCancel($this->lng->txt('cancel'), 'cancelDelete');
		$this->tpl->setContent($conf->getHTML());
	}


	public function cancelDelete() {
		$this->ctrl->redirectByClass("ildatacollectionrecordlistgui", "listRecords");
	}


	public function delete() {
		$record = ilDataCollectionCache::getRecordCache($this->record_id);

		if (!$this->table->hasPermissionToDeleteRecord($this->parent_obj->ref_id, $record)) {
			$this->accessDenied();

			return;
		}

		$record->doDelete();
		ilUtil::sendSuccess($this->lng->txt("dcl_record_deleted"), true);
		$this->ctrl->redirectByClass("ildatacollectionrecordlistgui", "listRecords");
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
			$record = ilDataCollectionCache::getRecordCache((int)$record_id);
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
	 */
	public function initForm() {
		$this->form = new ilPropertyFormGUI();
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

		foreach ($allFields as $field) {
			$item = ilDataCollectionDatatype::getInputField($field);
			if ($item === NULL) {
				continue; // Fields calculating values at runtime, e.g. ilDataCollectionFormulaField do not have input
			}
			if ($field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_REFERENCE) {
				$fieldref = $field->getFieldRef();
				$reffield = ilDataCollectionCache::getFieldCache($fieldref);
				$options = array();
				if (!$field->isNRef()) {
					$options[""] = $this->lng->txt('dcl_please_select');
				}
				$reftable = ilDataCollectionCache::getTableCache($reffield->getTableId());
				foreach ($reftable->getRecords() as $record) {
					// If the referenced field is MOB or FILE, we display the filename in the dropdown
					if ($reffield->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_FILE) {
						$file_obj = new ilObjFile($record->getRecordFieldValue($fieldref), false);
						$options[$record->getId()] = $file_obj->getFileName();
					} else {
						if ($reffield->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_MOB) {
							$media_obj = new ilObjMediaObject($record->getRecordFieldValue($fieldref), false);
							$options[$record->getId()] = $media_obj->getTitle();
						} else {
							$options[$record->getId()] = $record->getRecordFieldValue($fieldref);
						}
					}
				}
				asort($options);
				$item->setOptions($options);
				if ($field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_REFERENCE) { // FSX use this to apply to MultiSelectInputGUI
					//					if (!$field->isNRef()) { // addCustomAttribute only defined for single selects
					$item->addCustomAttribute('data-ref="1"');
					$item->addCustomAttribute('data-ref-table-id="' . $reftable->getId() . '"');
					$item->addCustomAttribute('data-ref-field-id="' . $reffield->getId() . '"');
					//					}
				}
			}

			if ($this->record_id) {
				$record = ilDataCollectionCache::getRecordCache($this->record_id);
			}

			$item->setRequired($field->getRequired());
			//WORKAROUND. If field is from type file: if it's required but already has a value it is no longer required as the old value is taken as default without the form knowing about it.
			if ($field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_FILE
				|| $field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_MOB
			) {
				if ($this->record_id AND $record->getId()) {
					$field_value = $record->getRecordFieldValue($field->getId());
					if ($field_value) {
						$item->setRequired(false);
					}
				}
				// If this is an ajax request to return the form, input files are currently not supported
				if ($this->ctrl->isAsynch()) {
					$item->setDisabled(true);
				}
			}

			if (!ilObjDataCollection::_hasWriteAccess($this->parent_obj->ref_id) && $field->getLocked()) {
				$item->setDisabled(true);
			}
			$this->form->addItem($item);
		}

		// Add possibility to change the owner in edit mode
		if ($this->record_id) {
			$ownerField = $this->table->getField('owner');
			$inputfield = ilDataCollectionDatatype::getInputField($ownerField);
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
		$record_obj = ilDataCollectionCache::getRecordCache($this->record_id);
		//Get Table Field Definitions
		$allFields = $this->table->getFields();
		$values = array();
		foreach ($allFields as $field) {
			$value = $record_obj->getRecordFieldFormInput($field->getId());
			$values['field_' . $field->getId()] = $value;
		}
		$values['record_id'] = $record_obj->getId();
		$this->form->setValuesByArray($values);

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


	/**
	 * Save record
	 */
	public function save() {
		$this->initForm();
		if ($this->form->checkInput()) {
			$record_obj = ilDataCollectionCache::getRecordCache($this->record_id);
			$date_obj = new ilDateTime(time(), IL_CAL_UNIX);
			$record_obj->setTableId($this->table_id);
			$record_obj->setLastUpdate($date_obj->get(IL_CAL_DATETIME));
			$record_obj->setLastEditBy($this->user->getId());

			$create_mode = false;

			if (ilObjDataCollection::_hasWriteAccess($this->parent_obj->ref_id)) {
				$all_fields = $this->table->getRecordFields();
			} else {
				$all_fields = $this->table->getEditableFields();
			}

			$fail = "";
			//Check if we can create this record.
			foreach ($all_fields as $field) {
				try {
					$value = $this->form->getInput("field_" . $field->getId());
					$field->checkValidity($value, $this->record_id);
				} catch (ilDataCollectionInputException $e) {
					$fail .= $field->getTitle() . ": " . $e . "<br>";
				}
			}

			if ($fail) {
				$this->sendFailure($fail);

				return;
			}

			if (!isset($this->record_id)) {
				if (!($this->table->hasPermissionToAddRecord($this->parent_obj->ref_id))) {
					$this->accessDenied();

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
				$value = $this->form->getInput("field_" . $field->getId());
				//deletion flag on MOB inputs.
				if ($field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_MOB
					&& $this->form->getItemByPostVar("field_" . $field->getId())->getDeletionFlag()
				) {
					$value = - 1;
				}
				$record_obj->setRecordFieldValue($field->getId(), $value);
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

			if ($create_mode) {
				ilObjDataCollection::sendNotification("new_record", $this->table_id, $record_obj->getId());
			}
			$record_obj->doUpdate();

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
				$this->ctrl->redirectByClass("ildatacollectionrecordlistgui", "listRecords");
			}
		} else {
			// Form not valid...
			$this->form->setValuesByPost();
			if ($this->ctrl->isAsynch()) {
				echo $this->form->getHTML();
				exit();
			} else {
				$this->tpl->setContent($this->form->getHTML());
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
					$this->ctrl->setParameterByClass('ildatacollectionrecordviewgui', 'record_id', $this->record_id);
					$this->ctrl->setParameterByClass('ildatacollectionrecordviewgui', 'table_id', $this->table_id);
					$this->ctrl->redirectByClass("ildatacollectionrecordviewgui", "renderRecord");
					break;
				case self::REDIRECT_RECORD_LIST:
					$this->ctrl->redirectByClass("ildatacollectionrecordlistgui", "listRecords");
					break;
				default:
					$this->ctrl->redirectByClass("ildatacollectionrecordlistgui", "listRecords");
			}
		}
	}


	protected function accessDenied() {
		if (!$this->ctrl->isAsynch()) {
			ilUtil::sendFailure($this->lng->txt('dcl_msg_no_perm_edit'), true);
			$this->ctrl->redirectByClass('ildatacollectionrecordlistgui', 'listRecords');
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
			$this->tpl->setContent($this->form->getHTML());
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
				$tpl->setVariable('RESULT_PATH', $path->getPath(ROOT_FOLDER_ID, $reference) . " > " . $entry['title']);
				$tpl->setVariable('RESULT_REF', $reference);
				$tpl->setVariable('FIELD_ID', $dest);
				$tpl->parseCurrentBlock();
			}
			$html .= $tpl->get();
		}

		echo $html;
		exit;
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