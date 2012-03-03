<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilBookingObjectGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
* 
* @ilCtrl_Calls ilBookingObjectGUI:
* @ilCtrl_IsCalledBy ilBookingObjectGUI:
*/
class ilBookingObjectGUI
{
	/**
	 * Constructor
	 * @param	object	$a_parent_obj
	 */
	function __construct($a_parent_obj)
	{
		$this->ref_id = $a_parent_obj->ref_id;
	}

	/**
	 * main switch
	 */
	function executeCommand()
	{
		global $tpl, $ilTabs, $ilCtrl;

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
		global $tpl, $ilCtrl, $ilTabs, $lng, $ilAccess;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt('book_back_to_list'), $ilCtrl->getLinkTargetByClass('ilBookingTypeGUI', 'render'));

		$ilCtrl->setParameter($this, 'type_id', (int)$_GET['type_id']);

		if ($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			include_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
			$bar = new ilToolbarGUI;
			$bar->addButton($lng->txt('book_add_object'), $ilCtrl->getLinkTarget($this, 'create'));
			$bar = $bar->getHTML();
		}

		include_once 'Modules/BookingManager/classes/class.ilBookingObjectsTableGUI.php';
		$table = new ilBookingObjectsTableGUI($this, 'listItems', $this->ref_id, (int)$_GET['type_id']);
		$tpl->setContent($bar.$table->getHTML());
	}

	/**
	 * Render creation form
	 */
	function create()
	{
		global $ilCtrl, $tpl, $lng, $ilTabs;

		$ilCtrl->setParameter($this, 'type_id', (int)$_REQUEST['type_id']);
		
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

		$ilCtrl->setParameter($this, 'type_id', (int)$_REQUEST['type_id']);

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

		include_once 'Modules/BookingManager/classes/class.ilBookingType.php';
		$type = new ilBookingType((int)$_REQUEST['type_id']);
		if(!$type->getScheduleId())
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
			$form_gui->setTitle($lng->txt("book_edit_object").": ".$type->getTitle());

			$item = new ilHiddenInputGUI('object_id');
			$item->setValue($id);
			$form_gui->addItem($item);

			include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
			$type = new ilBookingObject($id);
			$title->setValue($type->getTitle());
			
			if(isset($schedule))
			{
				$schedule->setValue($type->getScheduleId());
			}
			
			$form_gui->addCommandButton("update", $lng->txt("save"));
		}
		else
		{
			$form_gui->setTitle($lng->txt("book_add_object").": ".$type->getTitle());
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
			$obj->setTitle($form->getInput("title"));
			$obj->setTypeId((int)$_REQUEST["type_id"]);
			$obj->setScheduleId($form->getInput("schedule"));
			$obj->save();

			ilUtil::sendSuccess($lng->txt("book_object_added"));
			$this->render();
		}
		else
		{
			$ilCtrl->setParameter($this, 'type_id', (int)$_REQUEST['type_id']);
			
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
		global $tpl, $ilObjDataCache, $lng;

		$form = $this->initForm('edit', (int)$_POST['object_id']);
		if($form->checkInput())
		{
			include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
			$obj = new ilBookingObject((int)$_POST['object_id']);
			$obj->setTitle($form->getInput("title"));
			$obj->setTypeId((int)$_REQUEST['type_id']);
			$obj->setScheduleId($form->getInput("schedule"));
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

		$ilCtrl->setParameter($this, 'type_id', (int)$_REQUEST['type_id']);

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

		$ilCtrl->setParameter($this, 'type_id', (int)$_REQUEST['type_id']);

		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		$obj = new ilBookingObject((int)$_POST['object_id']);
		$obj->delete();

		ilUtil::sendSuccess($lng->txt('book_object_deleted'), true);
		$ilCtrl->redirect($this, 'render');
	}
}

?>