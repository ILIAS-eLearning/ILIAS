<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./classes/class.ilObjectGUI.php";

/**
* Class ilObjBookingPoolGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
* 
* @ilCtrl_Calls ilObjBookingPoolGUI: ilPermissionGUI
* @ilCtrl_IsCalledBy ilObjBookingPoolGUI: ilRepositoryGUI, ilAdministrationGUI
*/
class ilObjBookingPoolGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
	{
		$this->type = "booking";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
	}

	function executeCommand()
	{
		global $tpl, $ilTabs;

		$next_class = $this->ctrl->getNextClass($this);
		
		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->prepareOutput();
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
			
			default:
				$this->prepareOutput();
				$cmd = $this->ctrl->getCmd("view");
				$cmd .= "Object";
				$this->$cmd();
				break;
		}
		return true;
	}

	function createObject()
	{
		global $tpl;

		$form = $this->initForm();
		$tpl->setContent($form->getHTML());
	}

	function editObject()
	{
		global $tpl;

		$this->tabs_gui->setTabActive('edit');

		$form = $this->initForm("edit");
		$tpl->setContent($form->getHTML());
	}

	/**
	* Init creation form
	*/
	function initForm($a_mode = "create")
	{
		global $lng, $ilCtrl;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");

		$form_gui = new ilPropertyFormGUI();

		$title = new ilTextInputGUI($lng->txt("title"), "standard_title");
		$title->setRequired(true);
		$title->setSize(40);
		$title->setMaxLength(120);
		$form_gui->addItem($title);
		
		if ($a_mode == "edit")
		{
			$form_gui->setTitle($lng->txt("settings"));
			$title->setValue($this->object->getTitle());
			$form_gui->addCommandButton("update", $lng->txt("save"));
			$form_gui->addCommandButton("render", $lng->txt("cancel"));
		}
		else
		{
			$form_gui->setTitle($lng->txt("book_create_title"));
			$form_gui->addCommandButton("save", $lng->txt("save"));
			$form_gui->addCommandButton("cancel", $lng->txt("cancel"));
		}
		$form_gui->setFormAction($ilCtrl->getFormAction($this));

		return $form_gui;
	}
	
	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin, $ilUser, $tpl;

		$form = $this->initForm();
		if($form->checkInput())
		{
			$_POST["new_type"] = "booking";
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
	* update object
	* @access	public
	*/
	function updateObject()
	{
		global $rbacadmin, $ilUser, $tpl;

		$form = $this->initForm("edit");
		if($form->checkInput())
		{
			$_POST["Fobject"]["title"] = $form->getInput("standard_title");

			parent::updateObject();

			$this->renderObject();
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
	* @access	public
	*/
	function setTabs()
	{
		global $ilAccess, $ilCtrl, $ilTabs, $lng;
		
		if (in_array($ilCtrl->getCmd(), array("create", "save")))
		{
			return;
		}

		$ilTabs->addTab("render",
				$lng->txt("book_booking_list"),
				$this->ctrl->getLinkTarget($this, "render"));
		
		if ($ilAccess->checkAccess('write', '', $this->object->getRefId()))
		{
			$ilTabs->addTab("edit",
				$lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, "edit"));
		}

		if($ilAccess->checkAccess('edit_permission', '', $this->object->getRefId()))
		{
			$ilTabs->addTab("perm_settings",
				$lng->txt("perm_settings"),
				$this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm"));
		}
	}

	/**
	 *
	 *
	 */
	function renderObject()
	{
		global $tpl;
		
		$this->tabs_gui->setTabActive('render');

		include_once 'Modules/BookingManager/classes/class.ilBookingTypesTableGUI.php';
		$table = new ilBookingTypesTableGUI($this, 'render', $this->ref_id);
		$tpl->setContent($table->getHTML());
	}

	/**
	 *
	 *
	 */
	function addTypeObject()
    {
		global $tpl, $ilCtrl;

		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt('book_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));

		$form = $this->initTypeForm();
		$tpl->setContent($form->getHTML());
	}

	/**
	 *
	 *
	 */
	function editTypeObject()
    {
		global $tpl, $ilCtrl;

		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt('book_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));

		$form = $this->initTypeForm('edit', (int)$_GET['type_id']);
		$tpl->setContent($form->getHTML());
	}

	function initTypeForm($a_mode = "create", $id = NULL)
	{
		global $lng, $ilCtrl;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");

		$form_gui = new ilPropertyFormGUI();

		$title = new ilTextInputGUI($lng->txt("title"), "title");
		$title->setRequired(true);
		$title->setSize(40);
		$title->setMaxLength(120);
		$form_gui->addItem($title);

		if ($a_mode == "edit")
		{
			$item = new ilHiddenInputGUI('type_id');
			$item->setValue($id);
			$form_gui->addItem($item);
			include_once 'Modules/BookingManager/classes/class.ilBookingType.php';
			$type = new ilBookingType($id);

			$form_gui->setTitle($lng->txt("book_edit_type"));
			$title->setValue($type->getTitle());
			$form_gui->addCommandButton("updateType", $lng->txt("save"));
		}
		else
		{
			$form_gui->setTitle($lng->txt("book_add_type"));
			$form_gui->addCommandButton("saveType", $lng->txt("save"));
			$form_gui->addCommandButton("render", $lng->txt("cancel"));
		}
		$form_gui->setFormAction($ilCtrl->getFormAction($this));

		return $form_gui;
	}

   /**
	* save object
	* @access	public
	*/
	function saveTypeObject()
	{
		global $rbacadmin, $ilUser, $tpl, $ilObjDataCache;

		$form = $this->initTypeForm();
		if($form->checkInput())
		{
			include_once 'Modules/BookingManager/classes/class.ilBookingType.php';
			$obj = new ilBookingType;
			$obj->setTitle($form->getInput("title"));
			$obj->setPoolId($ilObjDataCache->lookupObjId($this->ref_id));
			$obj->save();

			ilUtil::sendSuccess($this->lng->txt("book_type_added"));
			$this->renderObject();
		}
		else
		{
			$form->setValuesByPost();
			$tpl->setContent($form->getHTML());
		}
	}

	/**
	* save object
	* @access	public
	*/
	function updateTypeObject()
	{
		global $rbacadmin, $ilUser, $tpl, $ilObjDataCache;

		$form = $this->initTypeForm('edit', (int)$_POST['type_id']);
		if($form->checkInput())
		{
			include_once 'Modules/BookingManager/classes/class.ilBookingType.php';
			$obj = new ilBookingType((int)$_POST['type_id']);
			$obj->setTitle($form->getInput("title"));
			$obj->setPoolId($ilObjDataCache->lookupObjId($this->ref_id));
			$obj->update();

			ilUtil::sendSuccess($this->lng->txt("book_type_updated"));
			$this->renderObject();
		}
		else
		{
			$form->setValuesByPost();
			$tpl->setContent($form->getHTML());
		}
	}

	/**
	 *
	 *
	 */
	function listItemsObject()
	{
		global $tpl, $ilCtrl;

		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt('book_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));

		include_once 'Modules/BookingManager/classes/class.ilBookingObjectsTableGUI.php';
		$table = new ilBookingObjectsTableGUI($this, 'listItems', $this->ref_id);
		$tpl->setContent($table->getHTML());
	}
}

?>