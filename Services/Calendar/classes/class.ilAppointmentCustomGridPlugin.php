<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Component/classes/class.ilPlugin.php';

/**
 * Abstract parent class for all calendar custom grid plugin classes.
 * @author  Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
abstract class ilAppointmentCustomGridPlugin extends ilPlugin
{
	/**
	 * @var array
	 */
	protected $appointment;

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
		return "AppointmentCustomGrid";
	}

	final public function getSlotId()
	{
		return "capg";
	}

	final public function slotInit()
	{
		//nothing to do here.
	}

	//todo define proper methods here
	//for day, week, month presentation

	abstract function replaceContent();

	abstract function addExtraContent();

	abstract function addGlyph();

	//for the list presentation

	abstract function replaceTitle();

	abstract function replaceDescription();
}