<?php
/* Copyright (c) 2019 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Item;

/**
 * Interface Notification
 * @package ILIAS\UI\Component\Item
 */
interface Notification extends Item
{
    /**
     * Get a Notification Item like this but with additional content bellow
     * the description. Note this should only be used, if the content section
     * needs to hold legacy content that currently does not have a place in the
     * UI components.
     * @param \ILIAS\UI\Component\Legacy\Legacy $component
     * @return self
     */
    public function withAdditionalContent(\ILIAS\UI\Component\Legacy\Legacy $component) : Notification;

    /**
     * Get the additional content of the item or null.
     * @return \ILIAS\UI\Component\Legacy\Legacy|null $component
     */
    public function getAdditionalContent();

    /**
     * Get an Item like this with an url to consulted async, when to close button is pressed.
     * With this url, information may be stored persistently in the DB without interrupting the workflow
     * of the user (e.g. setting a flag, that the message was consulted).
     * @param string $url
     * @return self
     */
    public function withCloseAction(string $url) : Notification;

    /**
     * Get the url attached to this Notification Item
     * @return self
     */
    public function getCloseAction();

    /**
     * Get an Notification Item like this, but with a set of
     * Notifications, this Notification Item will aggregate.
     * @param Notification[] $aggregate_notifications
     * @return self
     */
    public function withAggregateNotifications(array $aggregate_notifications) : Notification;

    /**
     * Get the list of Notification Items, this Notification Item
     * aggregates or an empty list.
     * @return Notification[] $sub
     */
    public function getAggregateNotifications() : array;

    /**
     * Set icon as lead
     * @param \ILIAS\UI\Component\Symbol\Icon\Icon $icon lead icon
     * @return Icon
     */
    public function withLeadIcon(\ILIAS\UI\Component\Symbol\Icon\Icon $icon);

    /**
     * Get icon as lead. Note that Notifications only accept Icons as lead,
     * this is different from the standard Item.
     * @return \ILIAS\UI\Component\Symbol\Icon\Icon
     */
    public function getLeadIcon() : \ILIAS\UI\Component\Symbol\Icon\Icon;
}