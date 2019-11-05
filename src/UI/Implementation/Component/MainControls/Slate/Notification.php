<?php
/* Copyright (c) 2019 Timnon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls\Slate;

use \ILIAS\UI\Component\MainControls\Slate as ISlate;
use ILIAS\UI\Component\Item\Notification as NotificationItem;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Class Notification
 * @package ILIAS\UI\Implementation\Component\MainControls\Slate
 */
class Notification extends Slate implements ISlate\Notification
{
    /**
     * @var array<Slate|Bulky>
     */
    protected $contents = [];

    public function __construct(
        SignalGeneratorInterface $signal_generator,
        string $name,
        $notification_items
    ) {
        $this->name             = $name;
        $this->signal_generator = $signal_generator;
        $this->contents         = $notification_items;
        $this->initSignals();
    }

    /**
     * @inheritdoc
     */
    public function withAdditionalEntry(NotificationItem $entry) : ISlate\Notification
    {
        $clone             = clone $this;
        $clone->contents[] = $entry;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getContents() : array
    {
        return $this->contents;
    }
}