<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Modules/DataCollection/classes/class.ilDataCollectionField.php");
require_once("./Modules/DataCollection/classes/class.ilDataCollectionDatatype.php");

/**
* Class ilDataCollectionFieldEditGUI
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
* @version $Id: 
*
*
*
* @ingroup ModulesDataCollection
*/
	
class ilDataCollectionFieldEditGUI
{
	
	/**
	 * Constructor
	 *
	*/
	public function  __construct()
	{
		include_once("class.ilDataCollectionDatatype.php");
		//TODO Prüfen, inwiefern sich die übergebenen GET-Parameter als Sicherheitslücke herausstellen
		$this->field_obj = new ilDataCollectionField($_GET[field_id]);
		$this->table_id = $_GET[table_id];

	}

	
	/**
	 * execute command
	 */
	function executeCommand()
	{
		global $tpl, $ilCtrl;
		
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
	 * create field add form
	*/
	public function create()
	{
		global $tpl;
		
		$this->initForm();
		$tpl->setContent($this->form->getHTML());
	}

	/**
	 * create field edit form
	*/
	public function edit()
	{
		global $tpl;
		
		$this->initForm("edit");
		$this->getValues();
		
		$tpl->setContent($this->form->getHTML());
	}	
	
	/**
	 * initEditCustomForm
	 *
	 * @param string $a_mode values: create | edit
	 */
	public function initForm($a_mode = "create")
	{
		global $ilCtrl, $lng;
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		
		$this->form->addCommandButton('save', 		$lng->txt('dcl_field_'.$a_mode));
		$this->form->addCommandButton('cancel', 	$lng->txt('cancel'));
		
		$this->form->setFormAction($ilCtrl->getFormAction($this, "save"));
	
		$this->form->setTitle($lng->txt('dcl_new_field'));
		
		$hidden_prop = new ilHiddenInputGUI("table_id");
		$hidden_prop ->setValue($this->table_id);
		$this->form->addItem($hidden_prop );
		
		$text_prop = new ilTextInputGUI($lng->txt("title"), "title");
		$this->form->addItem($text_prop);

		$edit_datatype = new ilRadioGroupInputGUI($lng->txt('dcl_datatype'),'datatype');
		
		foreach(ilDataCollectionDatatype::getAllDatatypes() as $datatype)
		{
			$opt = new ilRadioOption($lng->txt('dcl_'.$datatype['title']), $datatype['id']);
			
			foreach(ilDataCollectionDatatype::getProperties($datatype['id']) as $property)
			{
				if($property['datatype_id'] == $datatype['id'])
				{
						$subitem = new ilTextInputGUI($lng->txt('dcl_'.$property['title']), 'prop_'.$property['id']);
						$opt->addSubItem($subitem);
				}
			}
			$edit_datatype->addOption($opt);
		}
		$this->form->addItem($edit_datatype);
		
		// Description
		$text_prop = new ilTextAreaInputGUI($lng->txt("dcl_field_description"), "description");
		$this->form->addItem($text_prop);
		
		// Required
		$cb = new ilCheckboxInputGUI($lng->txt("dcl_field_required"), "required");
		$this->form->addItem($cb);
	}

	/**
	 * getFieldValues
	 */
	public function getValues()
	{
		$values =  array(
			'table_id'	=>	$this->field_obj->getTableId(),
			'title'			=>	$this->field_obj->getTitle(),
			'datatype'		=>	$this->field_obj->getDatatypeId(),
			'description'	=>	$this->field_obj->getDescription(),
			'required'		=>	$this->field_obj->getRequired(),
		);
		
		/*foreach(ilDataCollectionDatatype::getAllDatatypes() as $datatype)
		{
			$this->datatype_obj = new ilDataCollectionDatatype($datatype['id']);
			foreach($this->datatype_obj->getProperties() as $property)
			{
				$values[] = "lorem";
			}
		}*/
		
		//TODO
		//$props = ilDataCollectionField::getProperties(0);
		
		$this->form->setValuesByArray($values);
		
		return true;
	}
	
	
	/**
	 * save Field
	 *
	 * @param string $a_mode values: create | edit
	 */
	public function save($a_mode = "create")
	{
		global $ilCtrl, $lng;
		
		//TODO Berechtigungen prüfen
		//$this->dcl_object->checkPermission("write");
		//echo "<pre>".print_r($_POST,1)."</pre>";
		
		
		
		//echo "<pre>".print_r(get_class_methods($file_obj), 1)."</pre>";
		
		
		$this->initForm();
		if ($this->form->checkInput())
		{
			$field_obj = new ilDataCollectionField();
			$field_obj->setTitle($this->form->getInput("title"));
			$field_obj->setDescription($this->form->getInput("description"));
			$field_obj->setTableId($this->form->getInput("table_id"));
			$field_obj->setDatatypeId($this->form->getInput("datatype"));
			$field_obj->setRequired($this->form->getInput("required"));
			
			$field_obj->doCreate();
		
			// Get possible properties and save them
			include_once("./Modules/DataCollection/classes/class.ilDataCollectionFieldProp.php");
			foreach(ilDataCollectionDatatype::getProperties($field_obj->getDatatypeId()) as $property)
			{
				if($this->form->getInput("prop_".$property['id'])) 
				{
					$fieldprop_obj = new ilDataCollectionFieldProp();
					$fieldprop_obj->setDatatypePropertyId($property['id']);
					$fieldprop_obj->setFieldId($field_obj->getId());
					$fieldprop_obj->setValue($this->form->getInput("prop_".$property['id']));
					$fieldprop_obj->doCreate();
				}
			}

			//ilUtil::sendSuccess($lng->txt("msg_obj_modified"),true);
			
			//$ilCtrl->redirectByClass("ildatacollectionfieldlistgui", "listFields");
		}
		else
		{
			$this->form_gui->setValuesByPost();
			$this->tpl->setContent($this->form_gui->getHTML());
		}
	}
}

?>