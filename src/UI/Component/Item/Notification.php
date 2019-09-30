<?php

/* Copyright (c) 2019 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Item;

use ILIAS\UI\Component\Signal;

/**
 * Interface Notification
 * @package ILIAS\UI\Component\Item
 */
interface Notification extends Item {

	/**
	 * Get a Notification Item like this but with additional content bellow
	 * the description
	 *
	 * @param \ILIAS\UI\Component\Component $component
	 * @return self
	 */
	public function withAdditionalContent(\ILIAS\UI\Component\Component $component): Notification;

	/**
	 * Get the additional content of the item or null.
	 *
	 * @return \ILIAS\UI\Component\Component|null $component
	 */
	public function getAdditionalContent();

	/**
	 * Get an Item like this to be fired async, when to close button is pressed.
	 * With this interaction, information may be stored persistently in the DB without interrupting the workflow
	 * of the user (e.g. setting a flag, that the message was consulted).
	 *
	 * @param string|Signal		$action
	 * @return self
	 */
	public function withCloseAction($action): Notification;

	/**
	 * Get the signal attached to this Notification Item
	 *
	 * @return self
	 */
	public function getCloseAction();

	/**
	 * Get an Notification Item like this, but with a set of
	 * Notifications, this Notification Item will aggregate.
	 *
	 * @param Notification[] $sub
	 * @return self
	 */
	public function withAggregateNotification($sub): Notification;

	/**
	 * Get the list of Notification Items, this Notification Item
	 * aggregates or an empty list.
	 *
	 * @return Notification[] $sub
	 */
	public function getAggregateNotification(): array;

	/**
	 * Get icon as lead. Note that Notifications only accept Icons as lead,
	 * this is different from the standard Item.
	 *
	 * @return \ILIAS\UI\Component\Symbol\Icon\Icon
	 */
	public function getLeadIcon():\ILIAS\UI\Component\Symbol\Icon\Icon;
}
