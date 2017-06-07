<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Item;

use ILIAS\UI\Component as C;

class AppointmentItem extends Item implements C\Item\AppointmentItem {

	/**
	 * @var \ilDateTime
	 */
	protected $from;

	/**
	 * @var \ilDateTime
	 */
	protected $to;

	/**
	 * @var string
	 */
	protected $color;

	/**
	 * AppointmentItem constructor.
	 * @param string $title Title of the Appointment
	 * @param \ilDateTime $from Starting point of the appointment.
	 * @param \ilDateTime $to End point of the appointment.
	 * @param string $color Color of the calendar containing the item as color code (hex).
	 */
	public function __construct($title, \ilDateTime $from, \ilDateTime $to, $color) {
		$this->checkStringArg("color", $color);
		parent::__construct($title);
		$this->from = $from;
		$this->to = $to;
		$this->color = $color;
	}

	/**
	 * @inheritdoc
	 */
	public function getFrom(){
		return $this->from;
	}

	/**
	 * @inheritdoc
	 */
	public function getEnd(){
		return $this->to;
	}

	/**
	 * @inheritdoc
	 */
	public function getColor(){
		return $this->color;
	}
}
