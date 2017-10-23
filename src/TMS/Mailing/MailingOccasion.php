<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;
use CaT\Ente\Component;

/**
 * This is a component-interface for automails.
 */
interface MailingOccasion extends Component {

	/**
	 * Does the instance provide mails for this event?
	 *
	 * @param string 	$event
	 * @return bool
	 */
	public function doesProvideMailForEvent($event);

	/**
	 * Get the full list of events this Occasion
	 * provides mails for.
	 *
	 * @return string[]
	 */
	public function listEvents();

	/**
	 * Every Occasion maps to exactly ONE mailtemplate.
	 * This is its ident.
	 *
	 * @return string
	 */
	public function templateIdent();

	/**
	 * Get mails for this occasion.
	 *
	 * @param string 	$event
	 * @param array<string, mixed> 	$parameter
	 * @return Mail[]
	 */
	public function getMails($event, $parameter);

	/**
	 * Occasions might be scheduled; get the next date the event is due.
	 *
	 * @return \DateTime | null
	 */
	public function getNextScheduledDate();
}
