<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Component/classes/class.ilPlugin.php';

/**
 * Abstract parent class for all calendar custom modals plugin classes.
 * @author  Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
abstract class ilAppointmentCustomModalPlugin extends ilPlugin
{
	/**
	 * @var ilCalendarEntry $appointment
	 */
	protected $appointment;

	/**
	 * @var DateTime $start_date
	 */
	protected $start_date;

	/** @var \ilCalendarAppointmentPresentationGUI */
	//protected $GUIObject;

	/**
	 * @param \ilCalendarAppointmentPresentationGUI $GUIObject
	 */
	//public function setGUIObject($GUIObject)
	//{
		//$this->GUIObject = $GUIObject;
	//}

	/**
	 * @return \ilCalendarAppointmentPresentationGUI
	 */
	//public function getGUIObject()
	//{
		//return $this->GUIObject;
	//}

	/**
	* @param ilCalendarEntry $a_appointment
	* @param $a_start_date //todo date format here.
	* appointment contains the calendarEntry object + relevant information like start date, end date, calendar id etc.
	*/
	public function setAppointment(ilCalendarEntry $a_appointment, $a_start_date)
	{
		$this->appointment = $a_appointment;
		$this->start_date = $a_start_date;
	}

	public function getAppointment()
	{
		return $this->appointment;
	}

	/**
	 * @return DateTime
	 * This is the date of the calendar entry, it's not the appointment start date.
	 * This is important because an appointment can be recursive (e.g. 11 july, 12 july, 13, july)
	 * The appointment start date is always 11 July but for an entry it can be 11,12 or 13)
	 * When routing it is used to set up the parameter "dt"
	 */
	public function getStartDate()
	{
		return $this->start_date;
	}

	final public function getComponentType()
	{
		return IL_COMP_SERVICE;
	}

	final public function getComponentName()
	{
		return "Calendar";
	}

	final public function getSlot()
	{
		return "AppointmentCustomModal";
	}

	final public function getSlotId()
	{
		return "capm";
	}

	final public function slotInit()
	{
		//nothing to do here.
	}

	abstract function replaceContent();

	abstract function addExtraContent();

	abstract function infoscreenAddContent(ilInfoScreenGUI $a_info);

	abstract function toolbarAddItems(ilToolbarGUI $a_toolbar);

	abstract function toolbarReplaceContent();

}