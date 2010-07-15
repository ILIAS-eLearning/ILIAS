<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./classes/class.ilObjectGUI.php";

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
		global $tpl;

		include_once 'Modules/BookingManager/classes/class.ilBookingTypesTableGUI.php';
		$table = new ilBookingTypesTableGUI($this, 'render', $this->ref_id);
		$tpl->setContent($table->getHTML());
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

		$group = new ilCheckboxInputGUI($lng->txt("book_group_objects"), "group");
		$group->setInfo($lng->txt("book_group_objects_info"));
		$form_gui->addItem($group);

		$options = array();
		include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
		foreach(ilBookingSchedule::getList($ilObjDataCache->lookupObjId($this->ref_id)) as $schedule)
		{
			$options[$schedule["booking_schedule_id"]] = $schedule["title"];
		}
		
		$schedule = new ilSelectInputGUI($lng->txt("book_schedule"), "schedule");
		$schedule->setRequired(true);
		$schedule->setOptions($options);
		$group->addSubItem($schedule);

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
			$this->render();
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
}

?>