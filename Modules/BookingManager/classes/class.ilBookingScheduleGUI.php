<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilBookingScheduleGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
* 
* @ilCtrl_Calls ilBookingScheduleGUI:
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
		global $tpl, $lng, $ilCtrl, $ilAccess;

		include_once 'Modules/BookingManager/classes/class.ilBookingSchedulesTableGUI.php';
		$table = new ilBookingSchedulesTableGUI($this, 'render', $this->ref_id);
		
		if ($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			// if we have schedules but no objects - show info
			if(sizeof($table->getData()))
			{
				include_once "Modules/BookingManager/classes/class.ilBookingObject.php";
				if(!sizeof(ilBookingObject::getList(ilObject::_lookupObjId($this->ref_id))))
				{
					ilUtil::sendInfo($lng->txt("book_type_warning"));
				}
			}
			
			include_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
			$bar = new ilToolbarGUI;
			$bar->addButton($lng->txt('book_add_schedule'), $ilCtrl->getLinkTarget($this, 'create'));
			$bar = $bar->getHTML();
		}
		
		$tpl->setContent($bar.$table->getHTML());
	}

	/**
	 * Render creation form
	 */
	function create()
    {
		global $tpl, $ilCtrl, $ilTabs, $lng, $ilHelp;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt('book_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));
		$ilHelp->setScreenIdComponent("book");
		$ilHelp->setScreenId("schedules");
		$ilHelp->setSubScreenId("create");

		$form = $this->initForm();
		$tpl->setContent($form->getHTML());
	}

	/**
	 * Render edit form
	 */
	function edit()
    {
		global $tpl, $ilCtrl, $ilTabs, $lng, $ilHelp;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt('book_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));
		$ilHelp->setScreenIdComponent("book");
		$ilHelp->setScreenId("schedules");
		$ilHelp->setSubScreenId("edit");

		$form = $this->initForm('edit', (int)$_GET['schedule_id']);
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

		$lng->loadLanguageModule("dateplaner");

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");

		$form_gui = new ilPropertyFormGUI();

		$title = new ilTextInputGUI($lng->txt("title"), "title");
		$title->setRequired(true);
		$title->setSize(40);
		$title->setMaxLength(120);
		$form_gui->addItem($title);

		/*
		$type = new ilRadioGroupInputGUI($lng->txt("book_schedule_type"), "type");
		$type->setRequired(true);
		$form_gui->addItem($type);
		$fix = new ilRadioOption($lng->txt("book_schedule_type_fix"), "fix");
		$fix->setInfo($lng->txt("book_schedule_type_fix_info"));
		$type->addOption($fix);
		$flex = new ilRadioOption($lng->txt("book_schedule_type_flexible"), "flexible");
		$flex->setInfo($lng->txt("book_schedule_type_flexible_info"));
		$type->addOption($flex);

		$raster = new ilNumberInputGUI($lng->txt("book_schedule_raster"), "raster");
		$raster->setRequired(true);
		$raster->setInfo($lng->txt("book_schedule_raster_info"));
		$raster->setMinValue(1);
		$raster->setSize(3);
		$raster->setMaxLength(3);
		$raster->setSuffix($lng->txt("book_minutes"));
		$flex->addSubItem($raster);

		$rent_min = new ilNumberInputGUI($lng->txt("book_schedule_rent_min"), "rent_min");
		$rent_min->setInfo($lng->txt("book_schedule_rent_info"));
		$rent_min->setMinValue(1);
		$rent_min->setSize(3);
		$rent_min->setMaxLength(3);
		$flex->addSubItem($rent_min);

		$rent_max = new ilNumberInputGUI($lng->txt("book_schedule_rent_max"), "rent_max");
		$rent_max->setInfo($lng->txt("book_schedule_rent_info"));
		$rent_max->setMinValue(1);
		$rent_max->setSize(3);
		$rent_max->setMaxLength(3);
		$flex->addSubItem($rent_max);

		$break = new ilNumberInputGUI($lng->txt("book_schedule_break"), "break");
		$break->setInfo($lng->txt("book_schedule_break_info"));
		$break->setMinValue(1);
		$break->setSize(3);
		$break->setMaxLength(3);
		$flex->addSubItem($break);
		*/
		
		include_once "Modules/BookingManager/classes/class.ilScheduleInputGUI.php";
		$definition = new ilScheduleInputGUI($lng->txt("book_schedule_days"), "days");
		$definition->setInfo($lng->txt("book_schedule_days_info"));
		$definition->setRequired(true);
		$form_gui->addItem($definition);

		$deadline = new ilNumberInputGUI($lng->txt("book_deadline"), "deadline");
		$deadline->setInfo($lng->txt("book_deadline_info"));
		$deadline->setSuffix($lng->txt("book_hours"));
		$deadline->setMinValue(0);
		$deadline->setSize(3);
		$deadline->setMaxLength(3);
		$form_gui->addItem($deadline);
	
		if ($a_mode == "edit")
		{
			$form_gui->setTitle($lng->txt("book_edit_schedule"));

			$item = new ilHiddenInputGUI('schedule_id');
			$item->setValue($id);
			$form_gui->addItem($item);

			include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
			$schedule = new ilBookingSchedule($id);
			$title->setValue($schedule->getTitle());
			$deadline->setValue($schedule->getDeadline());

			/*
			if($schedule->getRaster())
			{
				$type->setValue("flexible");
				$raster->setValue($schedule->getRaster());
				$rent_min->setValue($schedule->getMinRental());
				$rent_max->setValue($schedule->getMaxRental());
				$break->setValue($schedule->getAutoBreak());
			}
			else
			{
				$type->setValue("fix");
			}
			*/

			$definition->setValue($schedule->getDefinitionBySlots());

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
		global $tpl, $lng;

		$form = $this->initForm();
		if($form->checkInput())
		{
			include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
			$obj = new ilBookingSchedule;
			$this->formToObject($form, $obj);
			$obj->save();

			ilUtil::sendSuccess($lng->txt("book_schedule_added"));
			$this->render();
		}
		else
		{
			$form->setValuesByPost();
			$this->setDefinitionFromPost($form);
			$tpl->setContent($form->getHTML());
		}
	}

	/**
	 * Update dataset
	 */
	function update()
	{
		global $tpl, $lng;

		$form = $this->initForm('edit', (int)$_POST['schedule_id']);
		if($form->checkInput())
		{
			include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
			$obj = new ilBookingSchedule((int)$_POST['schedule_id']);
			$this->formToObject($form, $obj);
			$obj->update();

			ilUtil::sendSuccess($lng->txt("book_schedule_updated"));
			$this->render();
		}
		else
		{
			$form->setValuesByPost();
			$this->setDefinitionFromPost($form);
			$tpl->setContent($form->getHTML());
		}
	}

	/**
	 * Reload definition values from post data
	 *
	 * @param ilPropertyFormGUI $form
	 */
	protected function setDefinitionFromPost(ilPropertyFormGUI $form)
	{
		$days = $form->getInput("days");
		if($days)
		{
			$days_group = $form->getItemByPostVar("days");
			foreach($days_group->getOptions() as $option)
			{
				$days_fields[$option->getValue()] = $option;
			}
			
			foreach($days as $day)
			{
				$slot = $form->getInput($day."_slot");
				$subs = $days_fields[$day]->getSubItems();
				if($slot[0])
				{
					$subs[0]->setValue($slot[0]);
				}
				if($slot[1])
				{
					$subs[1]->setValue($slot[1]);
				}
			}
		}
	}

	/**
	 * Convert incoming form data to schedule object
	 * @param	object	$form
	 * @param	object	$schedule
	 */
	protected function formToObject($form, $schedule)
	{
		global $ilObjDataCache;
		
		$schedule->setTitle($form->getInput("title"));
		$schedule->setPoolId($ilObjDataCache->lookupObjId($this->ref_id));
		$schedule->setDeadline($form->getInput("deadline"));

		/*
		if($form->getInput("type") == "flexible")
		{
			$schedule->setRaster($form->getInput("raster"));
			$schedule->setMinRental($form->getInput("rent_min"));
			$schedule->setMaxRental($form->getInput("rent_max"));
			$schedule->setAutoBreak($form->getInput("break"));
		}
		else
		{
			$schedule->setRaster(NULL);
			$schedule->setMinRental(NULL);
			$schedule->setMaxRental(NULL);
			$schedule->setAutoBreak(NULL);
		}
		*/
		
		$schedule->setDefinitionBySlots(ilScheduleInputGUI::getPostData("days"));
	}

	/**
	 * Confirm delete
	 */
	function confirmDelete()
	{
		global $ilCtrl, $lng, $tpl, $ilTabs, $ilHelp;

		$ilHelp->setSubScreenId("delete");


		include_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
		$conf = new ilConfirmationGUI();
		$conf->setFormAction($ilCtrl->getFormAction($this));
		$conf->setHeaderText($lng->txt('book_confirm_delete'));

		include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
		$type = new ilBookingSchedule((int)$_GET['schedule_id']);
		$conf->addItem('schedule_id', (int)$_GET['schedule_id'], $type->getTitle());
		$conf->setConfirm($lng->txt('delete'), 'delete');
		$conf->setCancel($lng->txt('cancel'), 'render');

		$tpl->setContent($conf->getHTML());
	}

	/**
	 * Delete schedule
	 */
	function delete()
	{
		global $ilCtrl, $lng;

		include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
		$obj = new ilBookingSchedule((int)$_POST['schedule_id']);
		$obj->delete();

		ilUtil::sendSuccess($lng->txt('book_schedule_deleted'), true);
		$ilCtrl->redirect($this, 'render');
	}
}

?>