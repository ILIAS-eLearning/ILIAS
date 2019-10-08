<?php
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

    /**
     * @var C\Signal
     */
    private $signal;

    /**
     * @var string
     */
    private $event;

    /**
     * @param C\Signal $signal
     * @param string $event
     */
    public function __construct(C\Signal $signal, $event)
    {
        $this->signal = $signal;
        $this->event = $event;
    }

    /**
     * @inheritdoc
     */
    public function getSignal()
    {
        return $this->signal;
    }

    /**
     * @inheritdoc
     */
    public function getEvent()
    {
        return $this->event;
    }
}
