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
	protected $appointment;

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
	* @param array $a_appointment
	* appointment contains the calendarEntry object + relevant information like start date, end date, calendar id etc.
	*/
	public function setAppointment($a_appointment)
	{
		$this->appointment = $a_appointment;
	}

	public function getAppointment()
	{
		return $this->appointment;
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