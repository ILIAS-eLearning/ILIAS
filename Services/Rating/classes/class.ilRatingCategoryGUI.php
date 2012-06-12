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
	
	function __construct($a_parent_id)
	{
		global $lng;
		
		$this->parent_id = (int)$a_parent_id;
		
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
		
		
	}
	
	protected function delete()
	{
		
		
		
	}
}

?>
