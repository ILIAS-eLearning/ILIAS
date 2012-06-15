<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilBookingObjectGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*/
class ilBookingObjectGUI
{
	protected $ref_id; // [int]
	protected $pool_id; // [int]
	protected $pool_has_schedule; // [bool]
	
	/**
	 * Constructor
	 * @param	object	$a_parent_obj
	 */
	function __construct($a_parent_obj)
	{
		$this->ref_id = $a_parent_obj->ref_id;
		$this->pool_id = $a_parent_obj->object->getId();		
		$this->pool_has_schedule = 
			($a_parent_obj->object->getScheduleType() != ilObjBookingPool::TYPE_NO_SCHEDULE);
	}

	/**
	 * main switch
	 */
	function executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass($this);
		
		switch($next_class)
		{
			default:
				$cmd = $ilCtrl->getCmd("render");
				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	 * Render list of booking objects
	 *
	 * uses ilBookingObjectsTableGUI
	 */
	function render()
	{
		global $tpl, $ilCtrl, $lng, $ilAccess;

		if ($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			include_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
			$bar = new ilToolbarGUI;
			$bar->addButton($lng->txt('book_add_object'), $ilCtrl->getLinkTarget($this, 'create'));
			$bar = $bar->getHTML();
		}

		include_once 'Modules/BookingManager/classes/class.ilBookingObjectsTableGUI.php';
		$table = new ilBookingObjectsTableGUI($this, 'listItems', $this->ref_id, $this->pool_id, $this->pool_has_schedule);
		$tpl->setContent($bar.$table->getHTML());
	}

	/**
	 * Render creation form
	 */
	function create()
	{
		global $ilCtrl, $tpl, $lng, $ilTabs;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt('book_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));

		$form = $this->initForm();
		$tpl->setContent($form->getHTML());
	}

	/**
	 * Render edit form
	 */
	function edit()
    {
		global $tpl, $ilCtrl, $ilTabs, $lng;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt('book_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));

		$form = $this->initForm('edit', (int)$_GET['object_id']);
		$tpl->setContent($form->getHTML());
	}

	/**
	 * Build property form
	 * @param	string	$a_mode
	 * @param	int		$id
	 * @return	object
	 */
	function initForm($a_mode = "create", $id = NULL)
	{
		global $lng, $ilCtrl, $ilObjDataCache;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");

		$form_gui = new ilPropertyFormGUI();

		$title = new ilTextInputGUI($lng->txt("title"), "title");
		$title->setRequired(true);
		$title->setSize(40);
		$title->setMaxLength(120);
		$form_gui->addItem($title);
		
		$desc = new ilTextAreaInputGUI($lng->txt("description"), "desc");
		$desc->setCols(40);
		$desc->setRows(2);
		$form_gui->addItem($desc);
		
		$nr = new ilNumberInputGUI($lng->txt("booking_nr_of_items"), "items");
		$nr->setRequired(true);
		$nr->setSize(3);
		$nr->setMaxLength(3);
		$form_gui->addItem($nr);
		
		if($this->pool_has_schedule)
		{
			$options = array();
			include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
			foreach(ilBookingSchedule::getList($ilObjDataCache->lookupObjId($this->ref_id)) as $schedule)
			{
				$options[$schedule["booking_schedule_id"]] = $schedule["title"];
			}	
			$schedule = new ilSelectInputGUI($lng->txt("book_schedule"), "schedule");
			$schedule->setRequired(true);
			$schedule->setOptions($options);
			$form_gui->addItem($schedule);
		}

		if ($a_mode == "edit")
		{
			$form_gui->setTitle($lng->txt("book_edit_object"));

			$item = new ilHiddenInputGUI('object_id');
			$item->setValue($id);
			$form_gui->addItem($item);

			include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
			$obj = new ilBookingObject($id);
			$title->setValue($obj->getTitle());
			$desc->setValue($obj->getDescription());
			$nr->setValue($obj->getNrOfItems());
			
			if(isset($schedule))
			{
				$schedule->setValue($obj->getScheduleId());
			}
			
			$form_gui->addCommandButton("update", $lng->txt("save"));
		}
		else
		{
			$form_gui->setTitle($lng->txt("book_add_object"));
			$form_gui->addCommandButton("save", $lng->txt("save"));
			$form_gui->addCommandButton("render", $lng->txt("cancel"));
		}
		$form_gui->setFormAction($ilCtrl->getFormAction($this));

		return $form_gui;
	}

	/**
	 * Create new object dataset
	 */
	function save()
	{
		global $ilCtrl, $tpl, $lng, $ilTabs;

		$form = $this->initForm();
		if($form->checkInput())
		{
			include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
			$obj = new ilBookingObject;
			$obj->setPoolId($this->pool_id);
			$obj->setTitle($form->getInput("title"));
			$obj->setDescription($form->getInput("desc"));
			$obj->setNrOfItems($form->getInput("items"));
			
			if($this->pool_has_schedule)
			{
				$obj->setScheduleId($form->getInput("schedule"));
			}
			
			$obj->save();

			ilUtil::sendSuccess($lng->txt("book_object_added"));
			$this->render();
		}
		else
		{			
			$ilTabs->clearTargets();
			$ilTabs->setBackTarget($lng->txt('book_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));

			$form->setValuesByPost();
			$tpl->setContent($form->getHTML());
		}
	}

	/**
	 * Update object dataset
	 */
	function update()
	{
		global $tpl, $lng;

		$form = $this->initForm('edit', (int)$_POST['object_id']);
		if($form->checkInput())
		{
			include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
			$obj = new ilBookingObject((int)$_POST['object_id']);
			$obj->setTitle($form->getInput("title"));
			$obj->setDescription($form->getInput("desc"));
			$obj->setNrOfItems($form->getInput("items"));
			
			if($this->pool_has_schedule)
			{
				$obj->setScheduleId($form->getInput("schedule"));
			}
			
			$obj->update();

			ilUtil::sendSuccess($lng->txt("book_object_updated"));
			$this->render();
		}
		else
		{
			$form->setValuesByPost();
			$tpl->setContent($form->getHTML());
		}
	}

	/**
	 * Confirm delete
	 */
	function confirmDelete()
	{
		global $ilCtrl, $lng, $tpl, $ilTabs;
		
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt('book_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));

		include_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
		$conf = new ilConfirmationGUI();
		$conf->setFormAction($ilCtrl->getFormAction($this));
		$conf->setHeaderText($lng->txt('book_confirm_delete'));

		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		$type = new ilBookingObject((int)$_GET['object_id']);
		$conf->addItem('object_id', (int)$_GET['object_id'], $type->getTitle());
		$conf->setConfirm($lng->txt('delete'), 'delete');
		$conf->setCancel($lng->txt('cancel'), 'render');

		$tpl->setContent($conf->getHTML());
	}

	/**
	 * Delete object
	 */
	function delete()
	{
		global $ilCtrl, $lng;

		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		$obj = new ilBookingObject((int)$_POST['object_id']);
		$obj->delete();

		ilUtil::sendSuccess($lng->txt('book_object_deleted'), true);
		$ilCtrl->redirect($this, 'render');
	}
}

?>