<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\ScheduledEvents;

/**
 * Some actions in TMS must be carried out at a certain point in time,
 * e.g. the cancellation of a waitinglist or the sending of a reminder-mail
 * for a course.
 * In order to do that without querying the complete DB on every run of the cron,
 * Events are queued in the schedule.
 * A cron-job can then ask for due events and raise them.
 * In order to do that, a component providing such events must register them
 * here. The component is also responsible for keeping scheduled events up to date,
 * i.e. when a course-date changes, the component must notice and update the
 * schedule accordingly.
 * Finally, "only" an event is being raised when an event is due.
 * The Component (or any other listener) will carry out the action.
 */

interface DB {

	/**
	 * Create a new scheduled event.
	 *
	 * @param int 	$issuer_ref
	 * @param DateTime 	$due
	 * @param string 	$component 	e.g. "Modules/Course"
	 * @param string 	$event 		e.g. "reached_end_of_booking_period"
	 * @param array<string,string> 	e.g. ['crs_ref_id' => '123', 'discard_waiting' => 'true']
	 *
	 * @return \ILIAS\TMS\ScheduledEvents\Event
	 */
	public function create($issuer_ref, \DateTime $due, $component, $event, $params = array());

	/**
	 * Get all events.
	 *
	 * @return \ILIAS\TMS\ScheduledEvents\Event[]
	 */
	public function getAll();

	/**
	 * Get all events with dates before now.
	 *
	 * @return \ILIAS\TMS\ScheduledEvents\Event[]
	 */
	public function getAllDue();

	/**
	 * Get all events from this issuer;
	 * Filter more by giving $component and/or $event.
	 *
	 * @param int 	$ref_id
	 * @param string|null 	$component
	 * @param string|null 	$event
	 * @return \ILIAS\TMS\ScheduledEvents\Event[]
	 */
	public function getAllFromIssuer($ref_id, $component=null, $event=null);

	/**
	 * Declare these events as accounted for (i.e.:they were raised)
	 * Most likely: delete them from DB.
	 *
	 * @param \ILIAS\TMS\ScheduledEvents\Event[] $events
	 * @return void
	 */
	public function setAccountedFor($events);

	/**
	 * Delete those events.
	 *
	 * @param \ILIAS\TMS\ScheduledEvents\Event[] $events
	 * @return void
	 */
	public function delete($events);

}