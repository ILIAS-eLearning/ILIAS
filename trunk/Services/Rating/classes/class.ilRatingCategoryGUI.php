<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Rating/classes/class.ilRatingCategory.php");

/**
 * Class ilRatingCategoryGUI. User interface class for rating categories.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * 
 * @ingroup ServicesRating
 */
class ilRatingCategoryGUI
{	
	protected $parent_id; // [int]
	protected $export_callback; // [string|array]
	protected $export_subobj_title; // [string]
	
	function __construct($a_parent_id, $a_export_callback = null, $a_export_subobj_title = null)
	{
		global $lng;
		
		$this->parent_id = (int)$a_parent_id;
		$this->export_callback = $a_export_callback;
		$this->export_subobj_title = $a_export_subobj_title;
		
		$lng->loadLanguageModule("rating");
		
		if($_REQUEST["cat_id"])
		{
			$cat = new ilRatingCategory($_REQUEST["cat_id"]);
			if($cat->getParentId() == $this->parent_id)
			{
				$this->cat_id = $cat->getId();								
			}						
		}
	}
	
	/**
	 * execute command
	 */
	function &executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("listCategories");
		
		switch($next_class)
		{			
			default:
				return $this->$cmd();
				break;
		}
	}
	
	protected function listCategories()
	{
		global $tpl, $ilToolbar, $lng, $ilCtrl;
	
		$ilToolbar->addButton($lng->txt("rating_add_category"), 
			$ilCtrl->getLinkTarget($this, "add"));
		
		$ilToolbar->addSeparator();
		
		$ilToolbar->addButton($lng->txt("export"), 
			$ilCtrl->getLinkTarget($this, "export"));
		
		include_once "Services/Rating/classes/class.ilRatingCategoryTableGUI.php";
		$table = new ilRatingCategoryTableGUI($this, "listCategories", $this->parent_id);		
		$tpl->setContent($table->getHTML());		
	}
	
	
	protected function initCategoryForm($a_id = null)
	{
		global $lng, $ilCtrl;
				
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setTarget("_top");
		$form->setFormAction($ilCtrl->getFormAction($this, "save"));
		$form->setTitle($lng->txt("rating_category_".($a_id ? "edit" : "create")));

		// title
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(128);
		$ti->setSize(40);
		$ti->setRequired(true);
		$form->addItem($ti);

		// description
		$ta = new ilTextAreaInputGUI($lng->txt("description"), "desc");
		$ta->setCols(40);
		$ta->setRows(2);
		$form->addItem($ta);

		if(!$a_id)
		{
			$form->addCommandButton("save", $lng->txt("rating_category_add"));
		}
		else
		{			
			$cat = new ilRatingCategory($a_id);				
			$ti->setValue($cat->getTitle());
			$ta->setValue($cat->getDescription());		
			
			$form->addCommandButton("update", $lng->txt("rating_category_update"));
		}
		$form->addCommandButton("listCategories", $lng->txt("cancel"));

		return $form;		
	}
	
	protected function add($a_form = null)
	{
		global $tpl;
		
		if(!$a_form)
		{
			$a_form = $this->initCategoryForm();
		}
		
		$tpl->setContent($a_form->getHTML());				
	}
	
	protected function save()
	{
		global $ilCtrl, $lng;
		
		$form = $this->initCategoryForm("create");
		if($form->checkInput())
		{
			include_once "Services/Rating/classes/class.ilRatingCategory.php";
			$cat = new ilRatingCategory();
			$cat->setParentId($this->parent_id);
			$cat->setTitle($form->getInput("title"));
			$cat->setDescription($form->getInput("desc"));
			$cat->save();
			
			ilUtil::sendSuccess($lng->txt("rating_category_created"));
			$ilCtrl->redirect($this, "listCategories");
		}
		
		$form->setValuesByPost();
		$this->add($form);		
	}
	
	protected function edit($a_form = null)
	{
		global $tpl, $ilCtrl;
				
		$ilCtrl->setParameter($this, "cat_id", $this->cat_id);
		
		if(!$a_form)
		{			
			$a_form = $this->initCategoryForm($this->cat_id);
		}
		
		$tpl->setContent($a_form->getHTML());				
	}
	
	protected function update()
	{
		global $ilCtrl, $lng;
		
		$form = $this->initCategoryForm($this->cat_id);
		if($form->checkInput())
		{
			include_once "Services/Rating/classes/class.ilRatingCategory.php";
			$cat = new ilRatingCategory($this->cat_id);
			$cat->setTitle($form->getInput("title"));
			$cat->setDescription($form->getInput("desc"));
			$cat->update();
			
			ilUtil::sendSuccess($lng->txt("rating_category_updated"));
			$ilCtrl->redirect($this, "listCategories");
		}
		
		$form->setValuesByPost();
		$this->add($form);		
	}
	
	protected function updateOrder()
	{
		global $ilCtrl, $lng;
		
		$order = $_POST["pos"];
		asort($order);
		
		$cnt = 0;
		foreach($order as $id => $pos)
		{
			$cat = new ilRatingCategory($id);
			if($cat->getParentId() == $this->parent_id)
			{
				$cnt += 10;
				$cat->setPosition($cnt);
				$cat->update();				
			}			
		}
		
		ilUtil::sendSuccess($lng->txt("settings_saved"), true);
		$ilCtrl->redirect($this, "listCategories");
	}
	
	protected function confirmDelete()
	{
		global $tpl, $ilCtrl, $lng;
		
		if(!$this->cat_id)
		{
			return $this->listCategories();
		}
		
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setHeaderText($lng->txt("rating_category_delete_sure")."<br/>".
			$lng->txt("info_delete_warning_no_trash"));

		$cgui->setFormAction($ilCtrl->getFormAction($this));
		$cgui->setCancel($lng->txt("cancel"), "listCategories");
		$cgui->setConfirm($lng->txt("confirm"), "delete");

		$cat = new ilRatingCategory($this->cat_id);		
		$cgui->addItem("cat_id", $this->cat_id, $cat->getTitle());
		
		$tpl->setContent($cgui->getHTML());
	}
	
	protected function delete()
	{
		global $ilCtrl, $lng;
		
		if($this->cat_id)
		{
			ilRatingCategory::delete($this->cat_id);			
			ilUtil::sendSuccess($lng->txt("rating_category_deleted"), true);			
		}
		
		// fix order
		$cnt = 0;
		foreach(ilRatingCategory::getAllForObject($this->parent_id) as $item)
		{
			$cnt += 10;
			
			$cat = new ilRatingCategory($item["id"]);
			$cat->setPosition($cnt);
			$cat->update();			
		}
		
		$ilCtrl->redirect($this, "listCategories");
	}
	
	protected function export()
	{		
		global $lng;
		
		$title = ilObject::_lookupTitle($this->parent_id);
		include_once "./Services/Excel/classes/class.ilExcelUtils.php";
		include_once "./Services/Excel/classes/class.ilExcelWriterAdapter.php";
		$adapter = new ilExcelWriterAdapter($title.".xls", true);
		
		// restrict to currently active (probably not needed - see delete())
		$active = array();
		foreach(ilRatingCategory::getAllForObject($this->parent_id) as $item)
		{
			$active[$item["id"]] = $item["title"];
		}
		
		ob_start();
		$workbook = $adapter->getWorkbook();
		$worksheet = $workbook->addWorksheet();
		
		var_dump();
		
		// title row
		$row = 0;		
		$worksheet->write($row, 0, $this->export_subobj_title." (".$lng->txt("id").")");
		$worksheet->write($row, 1, $this->export_subobj_title);
		$worksheet->write($row, 2, $lng->txt("rating_export_category")." (".$lng->txt("id").")");
		$worksheet->write($row, 3, $lng->txt("rating_export_category"));
		$worksheet->write($row, 4, $lng->txt("rating_export_date"));
		$worksheet->write($row, 5, $lng->txt("rating_export_rating"));			
		
		// content rows
		foreach(ilRating::getExportData($this->parent_id, ilObject::_lookupType($this->parent_id), array_keys($active)) as $item)
		{
			$row++;		
			
			$sub_obj_title = $item["sub_obj_type"];
			if($this->export_callback)
			{
				$sub_obj_title = call_user_func($this->export_callback, $item["sub_obj_id"], $item["sub_obj_type"]);
			}
			
			$worksheet->write($row, 0, $item["sub_obj_id"]);
			$worksheet->write($row, 1, $sub_obj_title);
			$worksheet->write($row, 2, $item["category_id"]);
			$worksheet->write($row, 3, $active[$item["category_id"]]);
			$worksheet->write($row, 4, date("Y-m-d H:i:s", $item["tstamp"]));
			$worksheet->write($row, 5, $item["rating"]);							
		}	
		
		ob_end_clean();

		$workbook->close();													
	}
}

?>
