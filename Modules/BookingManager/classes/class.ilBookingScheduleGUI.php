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

		$type = new ilRadioGroupInputGUI($lng->txt("book_schedule_type"), "type");
		$type->setRequired(true);
		$form_gui->addItem($type);
		$fix = new ilRadioOption($lng->txt("book_schedule_type_fix"), "fix");
		$fix->setInfo($lng->txt("book_schedule_type_fix_info"));
		$type->addOption($fix);
		$flex = new ilRadioOption($lng->txt("book_schedule_type_flexible"), "flexible");
		$flex->setInfo($lng->txt("book_schedule_type_flex_info"));
		$type->addOption($flex);

		$raster = new ilNumberInputGUI($lng->txt("book_raster"), "raster");
		$raster->setRequired(true);
		$raster->setInfo($lng->txt("book_raster_info"));
		$raster->setMinValue(1);
		$raster->setSize(3);
		$raster->setMaxLength(3);
		$raster->setSuffix($lng->txt("book_minutes"));
		$flex->addSubItem($raster);

		$rent_min = new ilNumberInputGUI($lng->txt("book_rent_min"), "rent_min");
		$rent_min->setInfo($lng->txt("book_rent_info"));
		$rent_min->setMinValue(1);
		$rent_min->setSize(3);
		$rent_min->setMaxLength(3);
		$flex->addSubItem($rent_min);

		$rent_max = new ilNumberInputGUI($lng->txt("book_rent_max"), "rent_max");
		$rent_max->setInfo($lng->txt("book_rent_info"));
		$rent_max->setMinValue(1);
		$rent_max->setSize(3);
		$rent_max->setMaxLength(3);
		$flex->addSubItem($rent_max);

		$break = new ilNumberInputGUI($lng->txt("book_break"), "break");
		$break->setInfo($lng->txt("book_break_info"));
		$break->setMinValue(1);
		$break->setSize(3);
		$break->setMaxLength(3);
		$flex->addSubItem($break);

		$definition = new ilCheckboxGroupInputGUI($lng->txt("book_days"), "days");
		$definition->setRequired(true);
		$form_gui->addItem($definition);

		$days = array('mo', 'tu', 'we', 'th', 'fr', 'sa', 'su');
		foreach($days as $day_id)
		{
			$day = new ilCheckboxOption($lng->txt(ucfirst($day_id)."_long"), $day_id);
			$definition->addOption($day);
			
			for($loop = 1; $loop < 5; $loop++)
		    {
				$hours[$day_id][$loop] = new ilTextInputGUI($lng->txt("book_slot")." ".$loop, $day_id."_slot[]");
				$hours[$day_id][$loop]->setSize(14);
				$hours[$day_id][$loop]->setMaxLength(14);
				$day->addSubItem($hours[$day_id][$loop]);
			}
		}

		$deadline = new ilNumberInputGUI($lng->txt("book_deadline"), "deadline");
		$deadline->setInfo($lng->txt("book_deadline_info"));
		$deadline->setSuffix($lng->txt("book_minutes"));
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

			$def = $schedule->getDefinition();
			$definition->setValue(array_keys($def));
			foreach($def as $day_id => $slots)
			{
				foreach($slots as $idx => $slot)
				{
					$hours[$day_id][$idx+1]->setValue($slot);
				}
			}

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
			$tpl->setContent($form->getHTML());
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

		$definition = array();
		foreach($form->getInput("days") as $day_id)
		{
			$day_slots = array();
			foreach($form->getInput($day_id."_slot") as $slot)
			{
				if(trim($slot))
				{
					$fromto = explode("-", $slot);
					if(sizeof($fromto) == 2)
					{
						$from = $this->parseTime($fromto[0]);
						$to = $this->parseTime($fromto[1]);
						$definition[$day_id][] = $from."-".$to;
					}
				}
			}
			if(!sizeof($definition[$day_id]))
			{
				$definition[$day_id] = array("00:00-23:59");
			}
		}
		$schedule->setDefinition($definition);
	}

	/**
	 * Parse/normalize incoming time values
	 * @param	string	$raw
	 */
	protected function parseTime($raw)
    {
		$raw = strtolower(trim($raw));
		$am = $pm = false;
		$min = 0;
		if(substr($raw, -2) == 'pm')
		{
			$pm = true;
			$raw = substr($raw, 0, -2);
		}
		if(substr($raw, -2) == 'am')
		{
			$am = true;
			$raw = substr($raw, 0, -2);
		}
		if($colon = strpos($raw, ':'))
		{
			$min = (int)substr($raw, $colon);
			$raw = substr($raw, 0, $colon);
		}
		$hours = (int)$raw;
		if(!$min)
		{
			$min = "0";
		}
		if($pm && $hours < 12)
		{
			$hours += 12;
		}
		else if($am && $hours == 12)
		{
			$hours -= 12;
		}
		return str_pad($hours, 2, "0", STR_PAD_LEFT).":".
			str_pad($min, 2, "0", STR_PAD_LEFT);
	}
}

?>