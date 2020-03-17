<?php
/* Copyright (c) 2019 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Item;

use ILIAS\UI\Component\Item\Notification as INotification;
use \ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use \ILIAS\UI\Component\JavaScriptBindable as IJavaScriptBindable;

class Notification extends Item implements INotification, IJavaScriptBindable
{
    use JavaScriptBindable;
    /**
     * @var Legacy|null
     */
    protected $additional_content = null;
    /**
     * @var \ILIAS\UI\Component\Symbol\Icon\Icon
     */
    protected $lead_icon;
    /**
     * @var string
     */
    protected $close_action;
    /**
     * @var INotification[]
     */
    protected $aggregate_notifications = [];

    /**
     * @param                                      $title
     * @param \ILIAS\UI\Component\Symbol\Icon\Icon $icon
     */
    public function __construct($title, \ILIAS\UI\Component\Symbol\Icon\Icon $icon)
    {
        $this->lead_icon = $icon;
        parent::__construct($title);
    }

    /**
     * @inheritdoc
     */
    public function withAdditionalContent(Legacy $additional_content) : INotification
    {
        $clone = clone $this;
        $clone->additional_content = $additional_content;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalContent() : ?Legacy
    {
        return $this->additional_content;
    }

    /**
     * @inheritdoc
     */
    public function withCloseAction(string $url) : INotification
    {
        $clone = clone $this;
        $clone->close_action = $url;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getCloseAction() : ?string
    {
        return $this->close_action;
    }

    /**
     * @inheritdoc
     */
    public function withAggregateNotifications(array $aggregate_notifications) : INotification
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
    public function getAggregateNotifications() : array
    {
        return $this->aggregate_notifications;
    }

    /**
     * @inheritdoc
     */
    public function withLeadIcon(\ILIAS\UI\Component\Symbol\Icon\Icon $icon) : INotification
    {
        $clone = clone $this;
        $clone->lead_icon = $icon;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getLeadIcon() : \ILIAS\UI\Component\Symbol\Icon\Icon
    {
        return $this->lead_icon;
    }
}
