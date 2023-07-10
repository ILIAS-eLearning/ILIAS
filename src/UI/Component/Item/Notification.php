<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\UI\Component\Item;

use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Dropdown\Standard as DropdownStandard;

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
     */
    public function withAdditionalContent(Legacy $component): Notification;

    /**
     * Get the additional content of the item or null.
     */
    public function getAdditionalContent(): ?Legacy;

    /**
     * Get an Item like this with an url to consulted async, when to close button is pressed.
     * With this url, information may be stored persistently in the DB without interrupting the workflow
     * of the user (e.g. setting a flag, that the message was consulted).
     */
    public function withCloseAction(string $url): Notification;

    /**
     * Get the url attached to this Notification Item
     */
    public function getCloseAction(): ?string;

    /**
     * Get an Notification Item like this, but with a set of
     * Notifications, this Notification Item will aggregate.
     * @param Notification[] $aggregate_notifications
     */
    public function withAggregateNotifications(array $aggregate_notifications): Notification;

    /**
     * Get the list of Notification Items, this Notification Item
     * aggregates or an empty list.
     * @return Notification[] $sub
     */
    public function getAggregateNotifications(): array;

    /**
     * Set icon as lead
     */
    public function withLeadIcon(Icon $lead): Notification;

    /**
     * Get icon as lead. Note that Notifications only accept Icons as lead,
     * this is different from the standard Item.
     */
    public function getLeadIcon(): Icon;

    /**
     * Create a new appointment item with a set of actions to perform on it.
     */
    public function withActions(DropdownStandard $actions): Notification;

    /**
     * Get the actions of the item.
     */
    public function getActions(): ?DropdownStandard;
}
