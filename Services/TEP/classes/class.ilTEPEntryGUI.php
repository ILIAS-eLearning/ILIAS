<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/TEP/classes/class.ilTEPEntry.php";

/**
 * TEP entry GUI 
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTEP
 * 
 * @ilCtrl_Calls ilTEPEntryGUI: 
 */
class ilTEPEntryGUI
{
	protected $permissions; // [ilTEPPermissions]
	
	/**
	 * Constructor
	 * 
	 * @param ilTEPPermissions $a_permissions
	 * @return self
	 */
	public function __construct(ilTEPPermissions $a_permissions)
	{
		$this->setPermissions($a_permissions);
	}
	
	// 
	// properties
	//
	
	/**
	 * Set permissions
	 * 
	 * @param ilTEPPermissions $a_perms
	 */
	protected function setPermissions(ilTEPPermissions $a_perms)
	{
		$this->permissions = $a_perms;
	}
	
	/**
	 * Get permissions
	 * 
	 * @return ilTEPPermissions 
	 */	
	protected function getPermissions()
	{
		return $this->permissions;
	}
	
	/**
	 * Set entry
	 * 
	 * @param ilTEPEntry $a_entry
	 */
	protected function setEntry(ilTEPEntry $a_entry)
	{
		$this->entry = $a_entry;
	}
	
	/**
	 * Get entry
	 * 
	 * @return ilTEPEntry 
	 */	
	protected function getEntry()
	{
		return $this->entry;
	}
	
	/**
	 * Set form defaults
	 * 
	 * @param ilDate $a_date
	 * @param int $a_tutor_id
	 */
	public function setDefaults(ilDate $a_date, $a_tutor_id)
	{
		$this->defaults["date"] = $a_date;
		$this->defaults["tutor"] = $a_tutor_id;
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
		global $ilCtrl;
	
		$ilCtrl->saveParameter($this, "eid");		
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("showEntry");

		switch($next_class)
		{			
			default:
				$this->$cmd();
				break;
		}
	}
	
	/**
	 * Return to parent GUI
	 */
	protected function returnToParent()
	{
		global $ilCtrl;
		
		$ilCtrl->returnToParent($this);
	}
	
	
	//
	// create/edit/show
	// 	
	
	/**
	 * Init entry form
	 * 
	 * @param bool $a_update
	 * @param bool $a_read_only
	 * @param bool $a_force_derived_entries
	 * @return ilPropertyFormGUI
	 */
	protected function initEntryForm($a_update = false, $a_read_only = false, $a_force_derived_entries = false)
	{
		global $ilCtrl, $lng, $tpl;
	
		$lng->loadLanguageModule("crs");
		$lng->loadLanguageModule("dateplaner");

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		
		if(!$a_update)
		{
			$form->setFormAction($ilCtrl->getFormAction($this, "saveEntry"));
			$form->setTitle($lng->txt("tep_create_entry"));
			$form->addCommandButton("saveEntry", $lng->txt("tep_create_entry"));
		}
		else
		{
			$form->setTitle($lng->txt("tep_update_entry"));

			if(!$a_read_only)
			{
				$form->setFormAction($ilCtrl->getFormAction($this, "updateEntry"));
				$form->addCommandButton("updateEntry", $lng->txt("save"));
			}
			else
			{
				$form->setFormAction($ilCtrl->getFormAction($this, "view"));
			}
		}

		if(!$a_read_only)
		{
			$form->addCommandButton("returnToParent", $lng->txt("cancel"));
		}
		else
		{
			$form->addCommandButton("returnToParent", $lng->txt("back"));
		}

		$tutor_opts = ilTEP::getEditableTutorNames($this->getPermissions());
		
		// tutor
		$tut = new ilSelectInputGUI($lng->txt("tep_entry_owner"), "tutor");
		$tut->setRequired(true);		
		$tut->setOptions(($a_update || sizeof($tutor_opts) == 1)
			? $tutor_opts
			: array(""=>$lng->txt("please_select"))+$tutor_opts);
		$form->addItem($tut);

		// derived 
		if(($this->getPermissions()->mayEditOthers() && sizeof($tutor_opts) > 1) ||
			(bool)$a_force_derived_entries)
		{
			include_once "Services/Form/classes/class.ilMultiSelectInputGUI.php";
			$tuto = new ilMultiSelectInputGUI($lng->txt("tep_entry_derived"), "tutor_drv");			
			$tuto->setWidth(275);
			$tuto->setHeight(150);
			
			// when derived entries where created by someone else: see self::setEntryFormValues()
			if($this->getPermissions()->mayEditOthers() && sizeof($tutor_opts) > 1)
			{				
				$tuto->setOptions($tutor_opts);		
			}
			
			$form->addItem($tuto);								
		}

		// title
		$title = new ilTextInputGUI($lng->txt("tep_entry_title"), "title");
		$title->setRequired(true);
		$form->addItem($title);
		
		include_once "Services/TEP/classes/class.ilCalEntryType.php";
		$cal = new ilCalEntryType();
		$type_opts = $cal->getAllActive();
		// gev-patch start
		sort($type_opts,  SORT_NATURAL | SORT_FLAG_CASE);
		// gev-patch end
		
		// type		
		$etype = new ilSelectInputGUI($lng->txt("tep_entry_type"), "type");
		$etype->setRequired(true);	
		$etype->setOptions(($a_update || sizeof($type_opts) == 1) 
			? $type_opts
			: array(""=>$lng->txt("please_select"))+$type_opts);
		$form->addItem($etype);

		include_once "Services/Form/classes/class.ilDateDurationInputGUI.php";
		$tpl->addJavaScript('./Services/Form/js/date_duration.js');
		$period = new ilDateDurationInputGUI($lng->txt("tep_entry_period"), "period");
		$period->setRequired(true);
		$period->setShowDate(true);
		$period->setShowTime(true);
		$period->setMinuteStepSize(5);
		$period->setStartText($lng->txt('cal_start'));
		$period->setEndText($lng->txt('cal_end'));
		$period->enableToggleFullTime($lng->txt('cal_fullday_title'),
			(bool)$_POST["period"]["fulltime"]); // not reloaded properly
		$form->addItem($period);

		// venue
		$venue = new ilTextInputGUI($lng->txt("tep_entry_location"), "location");
		$form->addItem($venue);

		// desc
		$desc = new ilTextAreaInputGUI($lng->txt("description"), "desc");
		$form->addItem($desc);
				
		// disable all elements
		if($a_read_only)
		{	
			$tut->setDisabled(true);
			$title->setDisabled(true);
			$etype->setDisabled(true);
			$venue->setDisabled(true);
			$desc->setDisabled(true);
			$period->setDisabled(true);
			if($tuto)
			{
				$tuto->setDisabled(true);
			}
		}		
		
		return $form;
	}
	
	/**
	 * Import entry form post values
	 * 
	 * @param ilTEPEntry $a_entry
	 * @param ilPropertyFormGUI $a_form
	 */
	protected function importEntryFormValues(ilTEPEntry $a_entry, ilPropertyFormGUI $a_form)
	{					
		$a_entry->setOwnerId($a_form->getInput("tutor"));		
		$a_entry->setDerivedUsers($a_form->getInput("tutor_drv"));	
		
		$a_entry->setTitle($a_form->getInput("title"));
		$a_entry->setLocation($a_form->getInput("location"));
		$a_entry->setDescription($a_form->getInput("desc"));
		$a_entry->setType($a_form->getInput("type"));

		$period = $a_form->getInput("period");
		if($period["fulltime"])
		{
			$a_entry->setFullday(true);
			$start = $period["start"]["date"];
			$end = $period["end"]["date"];
			$a_entry->setStart(new ilDate($start, IL_CAL_DATE));
			$a_entry->setEnd(new ilDate($end, IL_CAL_DATE));
		}
		else
		{
			$a_entry->setFullday(false);
			$start = $period["start"]["date"]." ".$period["start"]["time"];
			$end = $period["end"]["date"]." ".$period["end"]["time"];
			$a_entry->setStart(new ilDateTime($start, IL_CAL_DATETIME));
			$a_entry->setEnd(new ilDateTime($end, IL_CAL_DATETIME));
		}	
	}
	
	/**
	 * Set form default values
	 * 
	 * @param ilPropertyFormGUI $a_form
	 */
	protected function setEntryFormDefaults(ilPropertyFormGUI $a_form)
	{						
		$view_tutor = $this->defaults["tutor"];		
		if($view_tutor)
		{			
			$a_form->getItemByPostVar("tutor")->setValue($view_tutor);
		}		

		$view_period = $this->defaults["date"];
		if($view_period)
		{
			$date = $view_period->get(IL_CAL_DATE);			
			$period = $a_form->getItemByPostVar("period");			
			$period->setStart(new ilDateTime($date." 09:00:00", IL_CAL_DATETIME));								
			$period->setEnd(new ilDateTime($date." 17:00:00", IL_CAL_DATETIME));
		}		
	}
	
	/**
	 * Create new entry form
	 * 
	 * @param ilPropertyFormGUI $a_form
	 */
	protected function createEntry(ilPropertyFormGUI $a_form = null)
	{
		global $tpl;
		
		if(!$a_form)
		{
			$a_form = $this->initEntryForm();
			$this->setEntryFormDefaults($a_form);
		}
		
		$tpl->setContent($a_form->getHTML());		
	}
	
	/**
	 * Create new entry
	 */
	protected function saveEntry()
	{
		global $lng, $ilErr;
		
		$form = $this->initEntryForm();
		if($form->checkInput())
		{
			$entry = new ilTEPEntry();
			$this->importEntryFormValues($entry, $form);			
			
			if($entry->save())
			{								
				ilUtil::sendSuccess($lng->txt("tep_entry_created"), true);
				$this->returnToParent();
			}
			else
			{
				ilUtil::sendFailure($ilErr->getMessage());
			}	
		}
		
		$form->setValuesByPost();
		$this->createEntry($form);
	}
	
	/**
	 * Add entry values to form
	 * 
	 * @param ilTEPEntry $a_entry
	 * @param ilPropertyFormGUI $a_form
	 */
	protected function setEntryFormValues(ilTEPEntry $a_entry, ilPropertyFormGUI $a_form)
	{		
		global $lng;
		
		$tutor = $a_form->getItemByPostVar("tutor");	
		$tut_value = $a_entry->getOwnerId();
		
		// existing owner might not be available for current user
		if(!in_array($tut_value, array_keys($tutor->getOptions())))
		{
			$tut_name = ilObjUser::_lookupName($tut_value);
			$tutor->setOptions(array($tut_value => $tut_name["lastname"].", ".$tut_name["firstname"]));			
		}
							
		$tutor->setValue($tut_value);
		
		$a_form->getItemByPostVar("title")->setValue($a_entry->getTitle());
		$a_form->getItemByPostVar("type")->setValue($a_entry->getType());
		$a_form->getItemByPostVar("location")->setValue($a_entry->getLocation());
		$a_form->getItemByPostVar("desc")->setValue($a_entry->getDescription());
		
		$period = $a_form->getItemByPostVar("period");
		$period->setStart($a_entry->getStart());
		$period->setEnd($a_entry->getEnd());
		$period->enableToggleFullTime($lng->txt('cal_fullday_title'),
			$a_entry->isFullday()); // no way to set it otherwise
			
		// derived
		$derived = $a_entry->getDerivedUsers();				
		if($derived)
		{			
			$tutors_drv = $a_form->getItemByPostVar("tutor_drv");
			$tutors_drv->setValue($derived);
			
			// if derived entries were created by someone else, we have to keep them		
			if(!$tutors_drv->getOptions())
			{
				$tutors_drv->setDisabled(true);
						
				$opts = array();
				$tutors_map = ilTEP::getUserNames($derived);
				foreach($derived as $drv_id)
				{
					$opts[$drv_id] = $tutors_map[$drv_id];
				}
				$tutors_drv->setOptions($opts);

				// block derived entries from update
				$tuto_bl = new ilHiddenInputGUI("kpdrv");
				$tuto_bl->setValue(1);
				$a_form->addItem($tuto_bl);
			}
		}
	}
	
	/**
	 * Get current entry id (from request)
	 * 
	 * @param bool $a_needs_write_permission 
	 * @return ilTEPEntry
	 */
	protected function getCurrentEntry($a_needs_write_permission = false)
	{		
		global $ilUser;
		
		$entry_id = (int)$_REQUEST["eid"];
		if(!$entry_id)
		{
			$this->returnToParent();
		}
				
		$entry = new ilTEPEntry($entry_id);
		
		if($a_needs_write_permission)
		{
			if($ilUser->getId() != $entry->getOwnerId() &&
				!$this->getPermissions()->mayEditOthers())
			{
				$this->returnToParent();
			}								
		}	
		// this should never happen in TEP context, but who knows
		else if(!$this->getPermissions()->mayViewOthers())
		{
			// current user should be part of derived entries
			$derived = $entry->getDerivedUsers();
			if(!$derived || !in_array($ilUser->getId(), $derived))
			{
				$this->returnToParent();
			}
		}
	
		return $entry;
	}
	
	/**
	 * Update entry form
	 * 
	 * @param ilPropertyFormGUI $a_form
	 */
	protected function editEntry(ilPropertyFormGUI $a_form = null)
	{
		global $tpl, $ilCtrl, $ilToolbar, $lng;
								
		$entry = $this->getCurrentEntry(true);
				
		$ilCtrl->setParameter($this, "eid", $entry->getEntryId());

		$ilToolbar->addButton($lng->txt("tep_delete_entry"),
			$ilCtrl->getLinkTarget($this, "confirmDeleteEntry"));
		
		if(!$a_form)
		{						
			$a_form = $this->initEntryForm($entry, false, (bool)$entry->getDerivedUsers());		
			$this->setEntryFormValues($entry, $a_form);
		}
		
		$tpl->setContent($a_form->getHTML());		
	}
	
	/**
	 * Update entry 
	 */
	protected function updateEntry()
	{
		global $lng, $ilErr;
		
		$entry = $this->getCurrentEntry(true);
		
		$form = $this->initEntryForm(true, false, (bool)$entry->getDerivedUsers());
		if($form->checkInput())
		{			
			$this->importEntryFormValues($entry, $form);
			
			// do not handle derived when disabled in form (see above)
			if($entry->update(!$form->getInput("kpdrv")))
			{								
				ilUtil::sendSuccess($lng->txt("tep_entry_updated"), true);
				$this->returnToParent();
			}
			else
			{
				ilUtil::sendFailure($ilErr->getMessage());
			}	
		}
		
		$form->setValuesByPost();
		$this->editEntry($form);		
	}
	
	/**
	 * Show entry
	 */
	protected function showEntry()
	{
		global $tpl;

		$entry = $this->getCurrentEntry();
		
		$a_form = $this->initEntryForm($entry, true, (bool)$entry->getDerivedUsers());		
		$this->setEntryFormValues($entry, $a_form);		
		
		$tpl->setContent($a_form->getHTML());		
	}	
	
	/**
	 * Confirmation for entry deletion
	 */
	protected function confirmDeleteEntry()
	{
		global $lng, $tpl, $ilCtrl;

		$entry = $this->getCurrentEntry(true);

		$ilCtrl->setParameter($this, "eid", $entry->getEntryId());

		require_once "Services/Utilities/classes/class.ilConfirmationGUI.php";
		$cgui = new ilConfirmationGUI();
		$cgui->setHeaderText($lng->txt("tep_entry_delete_sure"));

		$cgui->setFormAction($ilCtrl->getFormAction($this));
		$cgui->setCancel($lng->txt("cancel"), "returnToParent");
		$cgui->setConfirm($lng->txt("tep_delete_entry"), "deleteEntry");

		$tpl->setContent($cgui->getHTML());
	}

	/**
	 * Delete entry
	 */
	protected function deleteEntry()
	{
		global $lng;

		$entry = $this->getCurrentEntry(true);
		$entry->delete();

		ilUtil::sendSuccess($lng->txt("tep_entry_deleted"), true);
		$this->returnToParent();
	}
}