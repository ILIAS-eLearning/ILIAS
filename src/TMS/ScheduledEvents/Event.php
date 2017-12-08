<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\ScheduledEvents;

/**
 * An event is raised according to the schedule.
 * The Event holds the necessary data.
 * It MUST NOT be constructed separately, but only by the Schedule.
 */
class Event {
	/**
	 * @var id
	 */
	protected $id;

	/**
	 * @var int 	ref_id of issuing object
	 */
	protected $issuer;

	/**
	 * @var DateTime
	 */
	protected $due;

	/**
	 * @var string
	 */
	protected $component;

	/**
	 * @var string
	 */
	protected $event;

	/**
	 * @var array<string,string>
	 */
	protected $params;

	/**
	 * @param int 	$id
	 * @param int 	$issuer_ref
	 * @param DateTime 	$due
	 * @param string 	$component 	e.g. "Modules/Course"
	 * @param string 	$event 		e.g. "reached_end_of_booking_period"
	 * @param array<string,string> 	e.g. ['crs_ref_id' => '123', 'discard_waiting' => 'true']
	 */
	public function __construct($id, $issuer_ref, \DateTime $due, $component, $event, $params) {
		assert('is_int($id)');
		assert('is_int($issuer_ref)');
		assert('is_string($component)');
		assert('is_string($event)');
		$this->id = $id;
		$this->issuer = $issuer_ref;
		$this->due = $due;
		$this->component = $component;
		$this->event = $event;
		$this->params = $params;
	}

	/*
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/*
	 * @return int
	 */
	public function getIssuerRef() {
		return $this->issuer;
	}

	/*
	 * @return DateTime
	 */
	public function getDue() {
		return $this->due;
	}

	/*
	 * @return string
	 */
	public function getComponent() {
		return $this->component;
	}

	/*
	 * @return string
	 */
	public function getEvent() {
		return $this->event;
	}

	/*
	 * @return array<string, string>
	 */
	public function getParameters() {
		return $this->params;
	}

}