<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/MaterialList/classes/class.ilMaterialList.php";
require_once "./Services/MaterialList/classes/class.ilMaterialListPermissions.php";

/**
 * Class ilMaterialListGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * $Id: class.ilObjFolderGUI.php 25134 2010-08-13 14:22:11Z smeyer $
 *
 * @ilCtrl_Calls ilMaterialListGUI: 
 * @ingroup ServicesMaterialList
 */
class ilMaterialListGUI
{
	protected $course; // [ilObjCourse] 
	protected $permissions; // [ilMaterialListPermissions]
	
	/**
	 * Init GUI
	 * 
	 * @param ilObjCourse $a_course
	 */
	protected function init(ilObjCourse $a_course)
	{
		global $lng, $ilTabs, $ilCtrl;
		
		$this->setCourse($a_course);				
		$this->setPermissions(ilMaterialListPermissions::getInstance($a_course));
						
		$lng->loadLanguageModule("matlist");	
		
		$ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "returnToParent"));
		$ilTabs->addTab("matlist", $lng->txt("matlist_tab"), $ilCtrl->getLinkTarget($this, ""));
	}
	
	
	//
	// GUI basics
	//
	
	/**
	 * Execute request command
	 * 
	 * @return boolean
	 */
	public function executeCommand()
	{
		global $ilCtrl, $tpl;
		
		$tpl->getStandardTemplate();
				
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("listMaterial");
		
		switch($next_class)
		{						
			default:											
				$ref_id = $_GET["ref_id"];
				if(!$ref_id)
				{
					throw new ilException("ilMaterialListGUI - no ref_id");
				}
				$ilCtrl->saveParameter($this, "ref_id", $ref_id);			

				require_once "Modules/Course/classes/class.ilObjCourse.php";
				$course = new ilObjCourse($ref_id); 
				
				$this->setCoursePageTitleAndLocator($course);
				$this->init($course);
												
				$this->$cmd();
				break;
		}
		
		$tpl->show();
	}
			
	/**
	 * Set page title, description and locator
	 * 
	 * @param ilObjCourse $a_course
	 */
	protected function setCoursePageTitleAndLocator(ilObjCourse $a_course)
	{
		global $tpl, $ilLocator, $lng;
		
		// see ilObjectGUI::setTitleAndDescription()
				
		$tpl->setTitle($a_course->getPresentationTitle());
		$tpl->setDescription($a_course->getLongDescription());
		$tpl->setTitleIcon(ilUtil::getImagePath("icon_crs_b.png"),
			$lng->txt("obj_crs"));

		include_once './Services/Object/classes/class.ilObjectListGUIFactory.php';
		$lgui = ilObjectListGUIFactory::_getListGUIByType("crs");
		$lgui->initItem($a_course->getRefId(), $a_course->getId());
		$tpl->setAlertProperties($lgui->getAlertProperties());	

		// see ilObjectGUI::setLocator()

		$ilLocator->addRepositoryItems($a_course->getRefId());
		$tpl->setLocator();
	}		
	
	/**
	 * Return to parent GUI
	 */
	protected function returnToParent()
	{
		global $ilCtrl;
		
		$ilCtrl->redirectByClass(array("ilRepositoryGUI", "ilObjCourseGUI"), "edit");		
		// $ilCtrl->returnToParent($this);
	}
	
	
	//
	// properties
	//
	
	/**
	 * Set course
	 * 
	 * @param ilObjCourse $a_course
	 */
	protected function setCourse(ilObjCourse $a_course)
	{
		$this->course = $a_course;
	}
	
	/**
	 * Get course
	 * 
	 * @return ilObjCourse 
	 */	
	protected function getCourse()
	{
		return $this->course;
	}
	
	/**
	 * Set permissions
	 * 
	 * @param ilMaterialListPermissions $a_perms
	 */
	protected function setPermissions(ilMaterialListPermissions $a_perms)
	{
		$this->permissions = $a_perms;
	}
	
	/**
	 * Get permissions
	 * 
	 * @return ilMaterialListPermissions 
	 */	
	protected function getPermissions()
	{
		return $this->permissions;
	}
	
			
	//
	// actions
	//
	
	/**
	 * List material for course 
	 *
	 * @param array $a_post
	 * @param type $a_add
	 */
	protected function listMaterial(array $a_post = null, $a_add = null)
	{
		global $tpl, $ilToolbar, $lng, $ilCtrl, $rbacsystem, $ilUser;	
						
		if(!$this->getPermissions()->viewMaterialList())
		{
			ilUtil::sendFailure($lng->txt("permission_denied"), true);
			$this->returnToParent();
		}
		
		$has_edit = $this->getPermissions()->editMaterialList();	
						
		if($has_edit)
		{
			$ilToolbar->setFormAction($ilCtrl->getFormAction($this, "listMaterial"));
			$ilToolbar->setCloseFormTag(false);

			include_once "Services/Form/classes/class.ilTextInputGUI.php";
			$number = new ilTextInputGUI("", "addnum");
			$number->setSize(2);
			$number->setValue(1);
			$ilToolbar->addInputItem($number);

			$ilToolbar->addFormButton($lng->txt("matlist_add"),
				"addMaterial");		
			
			$ilToolbar->addSeparator();
		}
		
		// download/export
		include_once "Services/MaterialList/classes/class.ilMaterialList.php";
		// gev-patch start
		$has_items = ilMaterialList::hasItems($this->getCourse()->getId());
		if($has_items)
		// gev-patch end
		{														
			$ilToolbar->addButton($lng->txt("matlist_download"), 
					$ilCtrl->getLinkTarget($this, "exportList"));										
		}
			
		if(!$a_add && (int)$_REQUEST["anm"])
		{
			$a_add = (int)$_REQUEST["anm"];
		}
		
		// gev-patch start
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$utils = gevCourseUtils::getInstanceByObj($this->getCourse());
		if ($utils->isTemplate() && $has_items) {
			$ilToolbar->addButton($lng->txt("gev_update_matlists"),
					$ilCtrl->getLinkTarget($this, "confirmUpdateLists"));
		}
		
		// gev-patch end
		
		include_once "Services/MaterialList/classes/class.ilMaterialListTableGUI.php";
		$table = new ilMaterialListTableGUI($this, "listMaterial", $this->getCourse()->getId(), 
			!$has_edit, $a_add, $a_post);
		
		if($has_edit)
		{
			$table->setOpenFormTag(false);
		}
		
		$tpl->setContent($table->getHTML());
	}
	
	/**
	 * Add material row
	 */
	protected function addMaterial()
	{
		global $lng, $ilCtrl;
		
		if(!$this->getPermissions()->editMaterialList())
		{
			ilUtil::sendFailure($lng->txt("permission_denied"), true);
			$ilCtrl->redirect($this, "listMaterial");
		}
		
		// #844 / #931
		$ilCtrl->setParameter($this, "anm", (int)$_POST["addnum"]);
		$this->updateMaterial();
	}
	
	/**
	 * Update material data
	 * 
	 * @param bool $a_return
	 * @return boolean
	 */
	protected function updateMaterial($a_return = false)
	{
		global $lng, $ilCtrl;
		
		if(!$this->getPermissions()->editMaterialList())
		{
			ilUtil::sendFailure($lng->txt("permission_denied"), true);
			$ilCtrl->redirect($this, "listMaterial");
		}
		
		$post = $this->handleMaterialPostData();		
		if(!sizeof($post["errors"]))
		{
			include_once "Services/MaterialList/classes/class.ilMaterialList.php";
			foreach($post["data"] as $id => $item)
			{
				$obj = new ilMaterialList($this->getCourse()->getId(), $id);
				$obj->setQuantityParent($item["ccnt"]);
				$obj->setQuantityParticipant($item["pcnt"]);
				$obj->setMaterialNumber($item["prod"]);
				$obj->setDescription($item["title"]);
				if($id > 0)
				{
					$obj->update();
				}
				else
				{
					$obj->save();
				}
			}
			
			$this->raiseEvent("updated");
			
			if(!$a_return)
			{
				ilUtil::sendSuccess($lng->txt("matlist_updated"), true);
				$ilCtrl->redirect($this, "listMaterial");
			}
			else
			{
				// see confirmMaterialDelete()
				return true;
			}
		}
		
		ilUtil::sendFailure($lng->txt("form_input_not_valid"), true);		
		$this->listMaterial($post);	
		
		// see confirmMaterialDelete()
		return false;
	}
	
	/**
	 * Raise event
	 * 	 
	 * @param string $a_event
	 */
	protected function raiseEvent($a_event)
	{
		global $ilAppEventHandler;
	
		$params = array();			
		$params["crs_obj_id"] = $this->getCourse()->getId();					
		
		$ilAppEventHandler->raise("Services/MaterialList", $a_event, $params);
	}
	
	/**
	 * Material deletion confirmation
	 */
	protected function confirmMaterialDelete()
	{
		global $tpl, $lng, $ilCtrl;
		
		if(!$this->getPermissions()->editMaterialList())
		{
			ilUtil::sendFailure($lng->txt("permission_denied"), true);
			$ilCtrl->redirect($this, "listMaterial");
		}		
		
		$ids = $_POST["id"];		
		
		// all entries must be valid to delete anything
		if($this->updateMaterial(true))
		{					
			// nothing to do?
			if(!sizeof($ids))
			{
				ilUtil::sendFailure($lng->txt("select_one"));
				return $this->listMaterial();
			}
									
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setHeaderText($lng->txt("matlist_delete_sure"));

			$cgui->setFormAction($ilCtrl->getFormAction($this, "delete"));
			$cgui->setCancel($lng->txt("cancel"), "listMaterial");
			$cgui->setConfirm($lng->txt("confirm"), "deleteMaterial");

			include_once "Services/MaterialList/classes/class.ilMaterialList.php";
			foreach ($ids as $id)
			{			
				// new entry cannot be deleted
				if($id > 0)
				{					
					$cgui->addItem("id[]", $id, ilMaterialList::lookupListTitle($id));	
				}
			}

			$tpl->setContent($cgui->getHTML());
		}		
	}
	
	/**
	 * Delete material
	 */
	protected function deleteMaterial()
	{
		global $lng, $ilCtrl;
		
		if(!$this->getPermissions()->editMaterialList())
		{
			ilUtil::sendFailure($lng->txt("permission_denied"), true);
			$ilCtrl->redirect($this, "listMaterial");
		}	
		
		$ids = $_POST["id"];
			
		if(!sizeof($ids))
		{
			return $this->listMaterial();
		}
		
		include_once "Services/MaterialList/classes/class.ilMaterialList.php";
		foreach ($ids as $id)
		{			
			$obj = new ilMaterialList($this->getCourse()->getId(), $id);
			$obj->delete();			
		}
		
		ilUtil::sendSuccess($lng->txt("matlist_deleted"), true);
		$ilCtrl->redirect($this, "listMaterial");
	}
	
	/**
	 * Export list
	 */
	protected function exportList()
	{
		global $lng, $ilCtrl;
		
		if(!$this->getPermissions()->viewMaterialList())
		{
			ilUtil::sendFailure($lng->txt("permission_denied"), true);
			$this->returnToParent();
		}
		
		include_once "Services/MaterialList/classes/class.ilMaterialList.php";
		if(!ilMaterialList::hasItems($this->getCourse()->getId()))
		{	
			$ilCtrl->redirect($this, "listMaterial");
		}
		
		$list = new ilMaterialList($this->getCourse()->getId());
		
		// :TODO: filename?
		$filename = $lng->txt("obj_crs")." - ".
			$this->getCourse()->getTitle()." - ".
			$lng->txt("matlist_xls_list_header");
		
		$list->buildXLS($filename);		
	}
	
	
	//
	// helper
	//
	
	/**
	 * Import and validate post data
	 * 
	 * @return array
	 */
	protected function handleMaterialPostData()
	{	
		$post = $errors = array();
		if(sizeof($_POST["mat"]))
		{			
			foreach($_POST["mat"] as $id => $item)
			{								
				$post[$id]["pcnt"] = trim($item["pcnt"]);
				$post[$id]["ccnt"] = trim($item["ccnt"]);
				$post[$id]["prod"] = trim($item["prod"]);
				$post[$id]["title"] = trim($item["title"]);
				
				if($id < 0 &&
					!$post[$id]["pcnt"] && 
					!$post[$id]["ccnt"] &&
					!$post[$id]["prod"] &&
					!$post[$id]["title"])
				{
					unset($post[$id]);
					continue;
				}
				
				if(($post[$id]["pcnt"] && !is_numeric($post[$id]["pcnt"])) || 
					$post[$id]["pcnt"] < 0)
				{
					$errors[$id][] = "pcnt";
				}
				if(($post[$id]["ccnt"] && !is_numeric($post[$id]["ccnt"])) ||
					$post[$id]["ccnt"] < 0)
				{
					$errors[$id][] = "ccnt";
				}
				
				if(!$post[$id]["pcnt"] && !$post[$id]["ccnt"])
				{
					$errors[$id][] = "pcnt";
					$errors[$id][] = "ccnt";
				}
				
				if(!$post[$id]["prod"])
				{
					$errors[$id][] = "prod";
				}
				
				if(!$post[$id]["title"])
				{
					$errors[$id][] = "title";
				}
				
				// :TODO: product id should be unique ?!
			}											
		}
		
		return array("data" => $post, "errors" => $errors);
	}
	
	// gev-patch start
	protected function confirmUpdateLists() {
		global $tpl, $lng, $ilCtrl;
		
		require_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		
		$utils = gevCourseUtils::getInstanceByObj($this->getCourse());
		if (!$utils->isTemplate()) {
			throw new Exception("Course ".$utils->getId()." is no template.");
		}
		
		$ids = $utils->getDerivedCourseIds(true);
		
		$cgui = new ilConfirmationGUI();
		$cgui->setHeaderText($lng->txt("gev_update_matlists_confirmation"));

		$cgui->setFormAction($ilCtrl->getFormAction($this, "updateLists"));
		$cgui->setCancel($lng->txt("cancel"), "listMaterial");
		$cgui->setConfirm($lng->txt("confirm"), "updateLists");
		
		foreach ($ids as $id) {
			$u = gevCourseUtils::getInstance($id);
			$p = ilMaterialListPermissions::getInstanceByRefId(gevObjectUtils::getRefId($id));
			
			if (!$p->editMaterialList() || $u->isMaterialListSend()) {
				continue;
			}
			
			$start = $u->getFormattedStartDate();
			$end = $u->getFormattedEndDate();
			if ($start !== null) {
				if ($start == $end) {
					$time = " (".$start.")";
				}
				else {
					$time = " (".$start." - ".$end.")";
				}
			}
			else {
				$time = "";
			}
			$cgui->addItem("id[]", $id, $u->getTitle().$time);
		}
		
		$tpl->setContent($cgui->getHTML());
	}
	
	protected function updateLists() {
		global $lng;
		
		$ids = $_POST["id"];
		
		$utils = gevCourseUtils::getInstanceByObj($this->getCourse());
		if (!$utils->isTemplate()) {
			throw new Exception("Course ".$utils->getId()." is no template.");
		}
		
		$list = new ilMaterialList($utils->getId());
		
		foreach ($ids as $id) {
			$u = gevCourseUtils::getInstance($id);
			$p = ilMaterialListPermissions::getInstanceByRefId(gevObjectUtils::getRefId($id));
			
			if (!$p->editMaterialList()) {
				continue;
			}
			
			$list->copyTo($id);
		}
		
		ilUtil::sendSuccess($lng->txt("gev_updated_matlists"));
		
		$this->listMaterial();
	}
	
	// gev-patch end
}
