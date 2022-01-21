<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component;

use ILIAS\UI\Component as C;

/**
 * Class TriggeredSignal
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component
 */
class TriggeredSignal
{
    private C\Signal $signal;
    private string $event;

    public function __construct(C\Signal $signal, string $event)
    {
        $this->signal = $signal;
        $this->event = $event;
    }

    /**
     * @inheritdoc
     */
    public function getSignal() : C\Signal
    {
        return $this->signal;
    }

    /**
     * @inheritdoc
     */
    public function getEvent() : string
    {
        return $this->event;
    }
}
