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

namespace ILIAS\UI\Implementation\Component\Item;

use ILIAS\UI\Component\Item\Notification as INotification;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Component\JavaScriptBindable as IJavaScriptBindable;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Link;
use ILIAS\UI\Component as C;

class Notification extends Item implements INotification, IJavaScriptBindable
{
    use JavaScriptBindable;

    protected ?Legacy $additional_content = null;
    protected Icon $lead_icon;
    protected ?string $close_action = null;

    /**
     * @var INotification[]
     */
    protected array $aggregate_notifications = [];

    /**
     * @param Shy|Link\Standard|string $title
     * @param Icon $icon
     */
    public function __construct($title, Icon $icon)
    {
        $this->lead_icon = $icon;
        parent::__construct($title);
    }

    /**
     * @inheritdoc
     */
    public function withAdditionalContent(Legacy $additional_content): INotification
    {
        $clone = clone $this;
        $clone->additional_content = $additional_content;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalContent(): ?Legacy
    {
        return $this->additional_content;
    }

    /**
     * @inheritdoc
     */
    public function withCloseAction(string $url): INotification
    {
        $clone = clone $this;
        $clone->close_action = $url;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getCloseAction(): ?string
    {
        return $this->close_action;
    }

    /**
     * @inheritdoc
     */
    public function withAggregateNotifications(array $aggregate_notifications): INotification
    {
        $classes = [
            INotification::class
        ];
        $this->checkArgListElements("Notification Item", $aggregate_notifications, $classes);
        $clone = clone $this;
        $clone->aggregate_notifications = $aggregate_notifications;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getAggregateNotifications(): array
    {
        return $this->aggregate_notifications;
    }

    /**
     * @inheritdoc
     */
    public function withLeadIcon(Icon $icon): INotification
    {
        $clone = clone $this;
        $clone->lead_icon = $icon;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getLeadIcon(): Icon
    {
        return $this->lead_icon;
    }

    /**
     * @inheritdoc
     */
    public function withActions(C\Dropdown\Standard $actions): C\Item\Notification
    {
        $clone = clone $this;
        $clone->actions = $actions;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getActions(): ?C\Dropdown\Standard
    {
        return $this->actions;
    }
}
