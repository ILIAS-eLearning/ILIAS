<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilBookingTypeGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
* 
* @ilCtrl_Calls ilBookingTypeGUI:
* @ilCtrl_IsCalledBy ilBookingTypeGUI:
*/
class ilBookingTypeGUI 
{
	/**
	 * Constructor
	 * @param	object	$a_parent_obj
	 */
	function __construct($a_parent_obj)
	{
		$this->ref_id = $a_parent_obj->ref_id;
		$this->obj_id = $a_parent_obj->object->getId();
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
	 * Render list of booking types
	 *
	 * uses ilBookingTypesTableGUI
	 */
	function render()
	{
		global $tpl, $lng, $ilCtrl, $ilAccess;

		if ($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			// if we have no schedules yet - show info
			include_once "Modules/BookingManager/classes/class.ilBookingSchedule.php";
			if(!ilBookingSchedule::hasExistingSchedules($this->obj_id))
			{
				ilUtil::sendFailure($lng->txt("book_schedule_warning"));
			}
			
			include_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
			$bar = new ilToolbarGUI;
			$bar->addButton($lng->txt('book_add_type'), $ilCtrl->getLinkTarget($this, 'create'));
			$bar = $bar->getHTML();
		}
		
		include_once 'Modules/BookingManager/classes/class.ilBookingTypesTableGUI.php';
		$table = new ilBookingTypesTableGUI($this, 'render', $this->ref_id);
		$tpl->setContent($bar.$table->getHTML());
	}

	/**
	 * Render creation form
	 */
	function create()
    {
		global $tpl, $ilCtrl, $ilTabs, $lng;

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

		$form = $this->initForm('edit', (int)$_GET['type_id']);
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

		include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
		$schedules = ilBookingSchedule::getList($this->obj_id);
	
		$group = new ilCheckboxInputGUI($lng->txt("book_group_objects"), "group");
		$group->setInfo($lng->txt("book_group_objects_info"));
		$form_gui->addItem($group);

		if(sizeof($schedules))
		{
			$options = array();
			foreach($schedules as $schedule)
			{
				$options[$schedule["booking_schedule_id"]] = $schedule["title"];
			}

			$schedule = new ilSelectInputGUI($lng->txt("book_schedule"), "schedule");
			$schedule->setRequired(true);
			$schedule->setOptions($options);
			$group->addSubItem($schedule);
		}
		else
		{
			$group->setDisabled(true);
		}

		if ($a_mode == "edit")
		{
			$form_gui->setTitle($lng->txt("book_edit_type"));

			$item = new ilHiddenInputGUI('type_id');
			$item->setValue($id);
			$form_gui->addItem($item);

			include_once 'Modules/BookingManager/classes/class.ilBookingType.php';
			$type = new ilBookingType($id);
			$title->setValue($type->getTitle());

			if($type->getScheduleId())
			{
				$schedule->setValue($type->getScheduleId());
				$group->setChecked(true);
			}

			$form_gui->addCommandButton("update", $lng->txt("save"));
		}
		else
		{
			$form_gui->setTitle($lng->txt("book_add_type"));
			$form_gui->addCommandButton("save", $lng->txt("save"));
			$form_gui->addCommandButton("render", $lng->txt("cancel"));
		}
		$form_gui->setFormAction($ilCtrl->getFormAction($this));

		return $form_gui;
	}

    /**
	 * Create new dataset
	 */
	function save()
	{
		global $tpl, $ilObjDataCache, $ilCtrl, $lng;

		$form = $this->initForm();
		if($form->checkInput())
		{
			include_once 'Modules/BookingManager/classes/class.ilBookingType.php';
			$obj = new ilBookingType;
			$obj->setTitle($form->getInput("title"));
			$obj->setPoolId($ilObjDataCache->lookupObjId($this->ref_id));

			if($form->getInput("group"))
			{
				$obj->setScheduleId($form->getInput("schedule"));
			}
			
			$obj->save();

			ilUtil::sendSuccess($lng->txt("book_type_added"));
			$ilCtrl->setParameterByClass('ilbookingobjectgui', 'type_id', $obj->getId());
			$ilCtrl->redirectByClass("ilbookingobjectgui", "create");
		}
		else
		{
			$form->setValuesByPost();
			$tpl->setContent($form->getHTML());
		}
	}

	/**
	 * Update dataset
	 */
	function update()
	{
		global $tpl, $ilObjDataCache, $lng;

		$form = $this->initForm('edit', (int)$_POST['type_id']);
		if($form->checkInput())
		{
			include_once 'Modules/BookingManager/classes/class.ilBookingType.php';
			$obj = new ilBookingType((int)$_POST['type_id']);
			$obj->setTitle($form->getInput("title"));
			$obj->setPoolId($ilObjDataCache->lookupObjId($this->ref_id));

			if($form->getInput("group"))
			{
				$obj->setScheduleId($form->getInput("schedule"));
			}
			else
			{
				$obj->setScheduleId(NULL);
			}
			
			$obj->update();

			ilUtil::sendSuccess($lng->txt("book_type_updated"));
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
		global $ilCtrl, $lng, $tpl;
		
		include_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
		$conf = new ilConfirmationGUI();
		$conf->setFormAction($ilCtrl->getFormAction($this));
		$conf->setHeaderText($lng->txt('book_confirm_delete'));

		include_once 'Modules/BookingManager/classes/class.ilBookingType.php';
		$type = new ilBookingType((int)$_GET['type_id']);
		$conf->addItem('type_id', (int)$_GET['type_id'], $type->getTitle());
		$conf->setConfirm($lng->txt('delete'), 'delete');
		$conf->setCancel($lng->txt('cancel'), 'render');

		$tpl->setContent($conf->getHTML());
	}

	/**
	 * Delete type
	 */
	function delete()
	{
		global $ilCtrl, $lng;
		
		include_once 'Modules/BookingManager/classes/class.ilBookingType.php';
		$type = new ilBookingType((int)$_POST['type_id']);
		$type->delete();

		ilUtil::sendSuccess($lng->txt('book_type_deleted'), true);
		$ilCtrl->redirect($this, 'render');
	}
}

?>