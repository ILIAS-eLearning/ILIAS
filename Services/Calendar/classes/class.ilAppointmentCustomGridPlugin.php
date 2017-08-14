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
	 * @var ilCalendarEntry $appointment
	 */
	protected $appointment;

	/**
	 * @var DateTime $start_date
	 */
	protected $start_date;

	/**
	 * @param ilCalendarEntry $a_appointment
	 * @param $a_start_date //todo: should be a date format
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

	//Day, Week and Month views.
	abstract function replaceContent();

	abstract function addExtraContent();

	abstract function addGlyph();

	//List view.
	//abstract function replaceTitle();

	//abstract function replaceDescription();

	/**
	 * @param $shy
	 * @param $properties
	 * @param $color
	 * @return mixed
	 */
	abstract function editAgendaItem($shy, $properties, $color);

	abstract function editShyButtonTitle();
}