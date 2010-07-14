<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilBookingScheduleGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
* 
* @ilCtrl_Calls ilBookingScheduleGUI:
* @ilCtrl_IsCalledBy ilBookingScheduleGUI:
*/
class ilBookingScheduleGUI
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
	 * Render list of booking schedules
	 *
	 * uses ilBookingSchedulesTableGUI
	 */
	function render()
	{
		global $tpl;

		include_once 'Modules/BookingManager/classes/class.ilBookingSchedulesTableGUI.php';
		$table = new ilBookingSchedulesTableGUI($this, 'render', $this->ref_id);
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
			include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
			$type = new ilBookingSchedule($id);

			$form_gui->setTitle($lng->txt("book_edit_schedule"));
			$title->setValue($type->getTitle());
			$form_gui->addCommandButton("update", $lng->txt("save"));
		}
		else
		{
			$form_gui->setTitle($lng->txt("book_add_schedule"));
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
			include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
			$obj = new ilBookingSchedule;
			$obj->setTitle($form->getInput("title"));
			$obj->setPoolId($ilObjDataCache->lookupObjId($this->ref_id));
			$obj->save();

			ilUtil::sendSuccess($lng->txt("book_schedule_added"));
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
			include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
			$obj = new ilBookingSchedule((int)$_POST['type_id']);
			$obj->setTitle($form->getInput("title"));
			$obj->setPoolId($ilObjDataCache->lookupObjId($this->ref_id));
			$obj->update();

			ilUtil::sendSuccess($lng->txt("book_schedule_updated"));
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