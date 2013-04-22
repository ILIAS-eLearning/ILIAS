<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/DataCollection/classes/class.ilDataCollectionRecord.php");
require_once("./Modules/DataCollection/classes/class.ilDataCollectionField.php");
require_once("./Modules/DataCollection/classes/class.ilDataCollectionTable.php");
require_once("./Modules/DataCollection/classes/class.ilDataCollectionDatatype.php");

/**
* Class ilDataCollectionRecordEditGUI
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
* @author Oskar Truffer <ot@studer-raimann.ch>
* @version $Id: 
*
*/
class ilDataCollectionRecordEditGUI
{

	private $record_id;
	private $table_id;
	private $table;
	private $parent_obj;

	/**
	 * Constructor
	 *
	 */
	public function __construct(ilObjDataCollectionGUI $parent_obj)
	{
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->parent_obj = $parent_obj;
		$this->record_id = $_REQUEST['record_id'];
		$this->table_id = $_GET['table_id'];
		
		include_once("class.ilDataCollectionDatatype.php");
		if($_REQUEST['table_id']) 
		{
			$this->table_id = $_REQUEST['table_id'];
		}

		$this->table = ilDataCollectionCache::getTableCache($this->table_id);
	}
	
	
	/**
	 * execute command
	 */
	public function executeCommand()
	{
		global $ilCtrl;

		$cmd = $ilCtrl->getCmd();
		switch($cmd)
		{
			default:
			$this->$cmd();
			break;
		}
		return true;
	}
	
	
	/**
	 * create Record
	 *
	 */
	public function create()
	{
		global $ilCtrl, $tpl;

		$this->initForm();

		$tpl->setContent($this->form->getHTML());
	}

	/**
	 * edit Record
	 */
	public function edit()
	{
		global $tpl, $ilCtrl;
		
		$this->initForm("edit");
		$this->getValues();
		
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * confirmDelete
	 */
	public function confirmDelete()
	{
		global $ilCtrl, $lng, $tpl;
		
		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$conf = new ilConfirmationGUI();
		$conf->setFormAction($ilCtrl->getFormAction($this));
		$conf->setHeaderText($lng->txt('dcl_confirm_delete_record'));

		$record = ilDataCollectionCache::getRecordCache($this->record_id);
		
		$conf->addItem('record_id', $record->getId(), implode(", ", $record->getRecordFieldValues()));
		$conf->addHiddenItem('table_id', $this->table_id);
		
		$conf->setConfirm($lng->txt('delete'), 'delete');
		$conf->setCancel($lng->txt('cancel'), 'cancelDelete');

		$tpl->setContent($conf->getHTML());
	}
	
	/**
	 * cancelDelete
	 */
	public function cancelDelete()
	{
		global $ilCtrl;
		
		$ilCtrl->redirectByClass("ildatacollectionfieldlistgui", "listFields");
	}
	
	/*
	 * delete
	 */
	public function delete()
	{
		global $ilCtrl, $lng;
		$record = ilDataCollectionCache::getRecordCache($this->record_id);
		
		if(!$this->table->hasPermissionToDeleteRecord($this->parent_obj->ref_id, $record))
		{
			$this->accessDenied();
			return;
		}
		
		$record->doDelete();
		ilUtil::sendSuccess($lng->txt("dcl_record_deleted"), true);
		$ilCtrl->redirectByClass("ildatacollectionrecordlistgui", "listRecords");
	}

	/**
	 * init Form
	 *
	 * @param string $a_mode values: create | edit
	 */
	public function initForm()
	{
		global $lng, $ilCtrl;

		//table_id
		$hidden_prop = new ilHiddenInputGUI("table_id");
		$hidden_prop ->setValue($this->table_id);
		$this->form->addItem($hidden_prop);

		$ilCtrl->setParameter($this, "record_id", $this->record_id);
		$this->form->setFormAction($ilCtrl->getFormAction($this));
		$allFields = $this->table->getRecordFields();

		foreach($allFields as $field)
		{
			$item = ilDataCollectionDatatype::getInputField($field);
			
			if($field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_REFERENCE)
			{
				$fieldref = $field->getFieldRef();
				$reffield = ilDataCollectionCache::getFieldCache($fieldref);
				$options = array();
				    $options[""] = '--';
				$reftable = ilDataCollectionCache::getTableCache($reffield->getTableId());
				foreach($reftable->getRecords() as $record)
				{
					$options[$record->getId()] = $record->getRecordFieldValue($fieldref);
				}
				$item->setOptions($options);
			}
			if($this->record_id)
			{
				$record = ilDataCollectionCache::getRecordCache($this->record_id);
			}
				

			$item->setRequired($field->getRequired());
			//WORKAROUND. If field is from type file: if it's required but already has a value it is no longer required as the old value is taken as default without the form knowing about it.
			if($field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_FILE ||  $field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_MOB
                && ($this->record_id
				&& $record->getId() != 0 
				&& ($record->getRecordFieldValue($field->getId()) != "-" || $record->getRecordFieldValue($field->getId()) != "")))
			{
				$item->setRequired(false);
			}
				
			if(!ilObjDataCollection::_hasWriteAccess($this->parent_obj->ref_id) && $field->getLocked())
			{
				$item->setDisabled(true);
			}
			$this->form->addItem($item);
		}

		// save and cancel commands
		if(isset($this->record_id))
		{
			$this->form->setTitle($lng->txt("dcl_update_record"));
			$this->form->addCommandButton("save", $lng->txt("dcl_update_record"));
			$this->form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
		}
		else
		{
			$this->form->setTitle($lng->txt("dcl_add_new_record"));
			$this->form->addCommandButton("save", $lng->txt("save"));
			$this->form->addCommandButton("cancelSave", $lng->txt("cancel"));
		}

		$ilCtrl->setParameter($this, "table_id", $this->table_id);
		$ilCtrl->setParameter($this, "record_id", $this->record_id);
		
	}

	/**
	 * get Values
	 * 
	 */	
	public function getValues()
	{

		//Get Record-Values
		$record_obj = ilDataCollectionCache::getRecordCache($this->record_id);

		//Get Table Field Definitions
		$allFields = $this->table->getFields();

		$values = array();
		foreach($allFields as $field)
		{
			$value = $record_obj->getRecordFieldFormInput($field->getId(), ilDataCollectionCache::getRecordFieldCache($record_obj, $field));
			$values['field_'.$field->getId()] = $value;
		}

		$this->form->setValuesByArray($values);
		return true;
	}
	
	/*
	 * cancelUpdate
	 */
	public function cancelUpdate()
	{
		global $ilCtrl;
		$ilCtrl->redirectByClass("ildatacollectionrecordlistgui", "listRecords");
	}
	
	/*
	 * cancelSave
	 */
	public function cancelSave()
	{
		$this->cancelUpdate();
	}

	/**
	 * save Record
	 *
	 * @param string $a_mode values: create | edit
	 */
	public function save()
	{	
		global $tpl, $ilUser, $lng, $ilCtrl;

		$this->initForm();
		if($this->form->checkInput())
		{
			$record_obj = ilDataCollectionCache::getRecordCache($this->record_id);
			$date_obj = new ilDateTime(time(), IL_CAL_UNIX);

			$record_obj->setTableId($this->table_id);
			$record_obj->setLastUpdate($date_obj->get(IL_CAL_DATETIME));
			$record_obj->setLastEditBy($ilUser->getId());

			if(ilObjDataCollection::_hasWriteAccess($this->parent_obj->ref_id))
			{
				$all_fields = $this->table->getRecordFields();
			}
			else
			{
				$all_fields = $this->table->getEditableFields();
			}
				
			$fail = "";
			//Check if we can create this record.
			foreach($all_fields as $field)
			{
				try
				{
				   $value = $this->form->getInput("field_".$field->getId());
					$field->checkValidity($value, $this->record_id);
				}catch(ilDataCollectionInputException $e){
				 $fail .= $field->getTitle().": ".$e."<br>";
				}
				
			}
			
			if($fail)
			{
				ilUtil::sendFailure($fail, true);
				$this->sendFailure();
				return;
			}

			if(!isset($this->record_id))
			{
				if(!($this->table->hasPermissionToAddRecord($this->parent_obj->ref_id)))
				{
					$this->accessDenied();
					return;
				}
				$record_obj->setOwner($ilUser->getId());
				$record_obj->setCreateDate($date_obj->get(IL_CAL_DATETIME));
				$record_obj->setTableId($this->table_id);
				$record_obj->doCreate();
				$this->record_id = $record_obj->getId();
			}
			else
			{
				if(!$record_obj->hasPermissionToEdit($this->parent_obj->ref_id))
				{
					$this->accessDenied();
					return;
				}
			}
			//edit values, they are valid we already checked them above
			foreach($all_fields as $field)
			{
				$value = $this->form->getInput("field_".$field->getId());
				$record_obj->setRecordFieldValue($field->getId(), $value);

			}

			$record_obj->doUpdate();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"),true);

			$ilCtrl->setParameter($this, "table_id", $this->table_id);
			$ilCtrl->setParameter($this, "record_id", $this->record_id);
			$ilCtrl->redirectByClass("ildatacollectionrecordlistgui", "listRecords");
		}
		else
		{
			global $tpl;
			$this->form->setValuesByPost();
			$tpl->setContent($this->form->getHTML());
		}

	}
	
	/*
	 * accessDenied
	 */
	private function accessDenied()
	{
		global $tpl;
		$tpl->setContent("Access denied");
	}
	
	/*
	 * sendFailure
	 */
	private function sendFailure()
	{
		global $tpl;
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHTML());
	}

	/**
	 * This function is only used by the ajax request if searching for ILIAS references. It builds the html for the search results.
	 */
	public function searchObjects(){
		global $lng;

		$search = $_POST['search_for'];
		$dest = $_POST['dest'];
		$html = "";
		include_once './Services/Search/classes/class.ilQueryParser.php';
		$query_parser = new ilQueryParser($search);
		$query_parser->setMinWordLength(1,true);
		$query_parser->setCombination(QP_COMBINATION_AND);
		$query_parser->parse();
		if(!$query_parser->validate())
		{
			$html .= $query_parser->getMessage()."<br />";
		}

		// only like search since fulltext does not support search with less than 3 characters
		include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
		$object_search = new ilLikeObjectSearch($query_parser);
		$res = $object_search->performSearch();
		$res->setRequiredPermission('copy');
		$res->filter(ROOT_FOLDER_ID,true);

		if(!count($results = $res->getResultsByObjId()))
		{
			$html .= $lng->txt('dcl_no_search_results_found_for').	$search."<br />";
		}
		$results = $this->parseSearchResults($results);

		foreach($results as $entry){
			$tpl = new ilTemplate("tpl.dcl_tree.html",true, true, "Modules/DataCollection");
			foreach((array) $entry['refs'] as $reference)
			{
				include_once './Services/Tree/classes/class.ilPathGUI.php';
				$path = new ilPathGUI();

				$tpl->setCurrentBlock('result');
				$tpl->setVariable('RESULT_PATH',$path->getPath(ROOT_FOLDER_ID, $reference)." > ".$entry['title']);
				$tpl->setVariable('RESULT_REF',$reference);
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
	 * @param ilObject[] $a_res
	 * @return array
	 */
	private function parseSearchResults($a_res)
	{
		foreach($a_res as $obj_id => $references)
		{
			$r['title'] 	= ilObject::_lookupTitle($obj_id);
			$r['desc']		= ilObject::_lookupDescription($obj_id);
			$r['obj_id']	= $obj_id;
			$r['refs']		= $references;

			$rows[] = $r;
		}

		return $rows ? $rows : array();
	}
}

?>