<?php
/* Copyright (c) 2019 Timnon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls\Slate;

use \ILIAS\UI\Component\MainControls\Slate as ISlate;
use ILIAS\UI\Component\Item\Notification as NotificationItem;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Symbol\Symbol;

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
        $notification_items,
        Symbol $symbol
    ) {
        $this->contents = $notification_items;
        parent::__construct($signal_generator, $name, $symbol);
    }

    /**
     * @inheritdoc
     */
    public function withAdditionalEntry(NotificationItem $entry) : ISlate\Notification
    {
        $clone = clone $this;
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

    public function withMappedSubNodes(callable $f)
    {
        return $this;
    }
}
