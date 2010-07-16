<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./classes/class.ilObjectGUI.php";

/**
* Class ilObjBookingPoolGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
* 
* @ilCtrl_Calls ilObjBookingPoolGUI: ilPermissionGUI, ilBookingTypeGUI, ilBookingObjectGUI, ilBookingScheduleGUI
* @ilCtrl_IsCalledBy ilObjBookingPoolGUI: ilRepositoryGUI, ilAdministrationGUI
*/
class ilObjBookingPoolGUI extends ilObjectGUI
{
	/**
	* Constructor
	*
	*/
	function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
	{
		$this->type = "book";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
		$this->lng->loadLanguageModule("book");
	}

	/**
	 * main switch
	 */
	function executeCommand()
	{
		global $tpl, $ilTabs;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		if(!$next_class && $cmd == 'render')
		{
			$this->ctrl->setCmdClass('ilBookingTypeGUI');
			$next_class = $this->ctrl->getNextClass($this);
		}

		if(substr($cmd, 0, 4) == 'book')
		{
			$next_class = '';
		}

		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case 'ilbookingtypegui':
				$this->tabs_gui->setTabActive('render');
				include_once("Modules/BookingManager/classes/class.ilBookingTypeGUI.php");
				$type_gui =& new ilBookingTypeGUI($this);
				$ret =& $this->ctrl->forwardCommand($type_gui);
				break;

			case 'ilbookingobjectgui':
				$this->tabs_gui->setTabActive('render');
				include_once("Modules/BookingManager/classes/class.ilBookingObjectGUI.php");
				$object_gui =& new ilBookingObjectGUI($this);
				$ret =& $this->ctrl->forwardCommand($object_gui);
				break;

			case 'ilbookingschedulegui':
				$this->tabs_gui->setTabActive('schedules');
				include_once("Modules/BookingManager/classes/class.ilBookingScheduleGUI.php");
				$schedule_gui =& new ilBookingScheduleGUI($this);
				$ret =& $this->ctrl->forwardCommand($schedule_gui);
				break;
			
			default:
				$cmd = $this->ctrl->getCmd();
				$cmd .= 'Object';
				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	 * Display creation form
	 */
	function createObject()
	{
		global $tpl;

		$form = $this->initForm();
		$tpl->setContent($form->getHTML());
	}

	/**
	 * Display update form
	 */
	function editObject()
	{
		global $tpl;

		$this->tabs_gui->setTabActive('edit');

		$form = $this->initForm("edit");
		$tpl->setContent($form->getHTML());
	}

	/**
	* Init property form
	* @return	object
	*/
	function initForm($a_mode = "create")
	{
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");

		$form_gui = new ilPropertyFormGUI();

		$title = new ilTextInputGUI($this->lng->txt("title"), "standard_title");
		$title->setRequired(true);
		$title->setSize(40);
		$title->setMaxLength(120);
		$form_gui->addItem($title);
		
		if ($a_mode == "edit")
		{
			$form_gui->setTitle($this->lng->txt("settings"));
			$title->setValue($this->object->getTitle());
			$form_gui->addCommandButton("update", $this->lng->txt("save"));
			$form_gui->addCommandButton("render", $this->lng->txt("cancel"));
		}
		else
		{
			$form_gui->setTitle($this->lng->txt("book_create_title"));
			$form_gui->addCommandButton("save", $this->lng->txt("save"));
			$form_gui->addCommandButton("cancel", $this->lng->txt("cancel"));
		}
		$form_gui->setFormAction($this->ctrl->getFormAction($this));

		return $form_gui;
	}
	
	/**
	* create new dataset
	*/
	function saveObject()
	{
		global $rbacadmin, $ilUser, $tpl;

		$form = $this->initForm();
		if($form->checkInput())
		{
			$_POST["new_type"] = "book";
			$_POST["Fobject"]["title"] = $form->getInput("standard_title");

			// always call parent method first to create an object_data entry & a reference
			$newObj = parent::saveObject();

			// always send a message
			ilUtil::sendSuccess($this->lng->txt("book_pool_added"),true);

			// BEGIN ChangeEvent: Record object creation
			global $ilUser;
			require_once('Services/Tracking/classes/class.ilChangeEvent.php');
			if (ilChangeEvent::_isActive())
			{
				ilChangeEvent::_recordWriteEvent($newObj->getId(), $ilUser->getId(), 'create');
			}
			// END ChangeEvent: Record object creation

			$this->redirectToRefId($_GET["ref_id"]);
		}
		else
		{
			$form->setValuesByPost();
			$tpl->setContent($form->getHTML());
		}
	}
	
	/**
	* update dataset
	*/
	function updateObject()
	{
		global $rbacadmin, $ilUser, $tpl, $ilCtrl;

		$form = $this->initForm("edit");
		if($form->checkInput())
		{
			$_POST["Fobject"]["title"] = $form->getInput("standard_title");

			parent::updateObject();

			$ilCtrl->redirect($this, "render");
		}
		else
		{
			$this->tabs_gui->setTabActive('edit');
			$form->setValuesByPost();
			$tpl->setContent($form->getHTML());
		}
	}

	function afterUpdate()
	{
		
	}

	/**
	* get tabs
	*/
	function setTabs()
	{
		global $ilAccess;
		
		if (in_array($this->ctrl->getCmd(), array("create", "save")))
		{
			return;
		}

		$this->tabs_gui->addTab("render",
				$this->lng->txt("book_booking_types"),
				$this->ctrl->getLinkTarget($this, "render"));
		
		if ($ilAccess->checkAccess('write', '', $this->object->getRefId()))
		{
			$this->tabs_gui->addTab("schedules",
				$this->lng->txt("book_schedules"),
				$this->ctrl->getLinkTargetByClass("ilbookingschedulegui", "render"));

			$this->tabs_gui->addTab("edit",
				$this->lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, "edit"));
		}

		if($ilAccess->checkAccess('edit_permission', '', $this->object->getRefId()))
		{
			$this->tabs_gui->addTab("perm_settings",
				$this->lng->txt("perm_settings"),
				$this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm"));
		}
	}

	/**
	 * First step in booking process
	 */
	function bookObject()
	{
		$this->tabs_gui->setTabActive('render');

		if(isset($_GET['object_id']))
		{
			$this->renderBookingByObject($_GET['object_id']);
		}
		else
		{
			$this->renderBookingByType($_GET['type_id']);
		}
	}

	/**
	 *
	 *
	 */
	protected function renderBookingByObject($a_object_id)
    {
		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		include_once 'Modules/BookingManager/classes/class.ilBookingType.php';
		$obj = new ilBookingObject($a_object_id);

	}

	/**
	 *
	 *
	 */
	protected function renderBookingByType($a_type_id)
    {
		include_once 'Modules/BookingManager/classes/class.ilBookingType.php';
		$type = new ilBookingType($a_type_id);
		
	}
}

?>