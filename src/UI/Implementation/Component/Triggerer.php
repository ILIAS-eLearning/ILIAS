<?php
namespace ILIAS\UI\Implementation\Component;

use ILIAS\UI\Component;

/**
 * Trait Triggerer
 *
 * Provides helper methods and default implementation for components acting as triggerer
 *
 * TODO: This is missing tests!
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component
 */
trait Triggerer
{

    /**
     * @var \ILIAS\UI\Implementation\Component\TriggeredSignal[]
     */
    private $triggered_signals = array();

    /**
     * Append a triggered signal to other signals of the same event
     *
     * @param Component\Signal $signal
     * @param string $event
     * @return $this
     */
    protected function appendTriggeredSignal(Component\Signal $signal, $event)
    {
        $clone = clone $this;
        if (!isset($clone->triggered_signals[$event])) {
            $clone->triggered_signals[$event] = array();
        }
        $clone->triggered_signals[$event][] = new TriggeredSignal($signal, $event);
        return $clone;
    }

    /**
     * Add a triggered signal, replacing any other signals registered on the same event
     *
     * @param Component\Signal $signal
     * @param string $event
     * @return $this
     */
    protected function withTriggeredSignal(Component\Signal $signal, $event)
    {
        $clone = clone $this;
        $clone->setTriggeredSignal($signal, $event);
        return $clone;
    }

    /**
     * Add a triggered signal, replacing any othe signals registered on the same event.
     *
     * ATTENTION: This mutates the original object and should only be used when there
     * is no other possibility.
     *
     * @param	Component\Signal 	$signal
     * @param	string	$event
     * @return	void
     */
    protected function setTriggeredSignal(Component\Signal $signal, $event)
    {
        $this->triggered_signals[$event] = array();
        $this->triggered_signals[$event][] = new TriggeredSignal($signal, $event);
    }

    /**
     * @return \ILIAS\UI\Implementation\Component\TriggeredSignal[]
     */
    public function getTriggeredSignals()
    {
        return $this->flattenArray($this->triggered_signals);
    }

    /**
     * Get signals that are triggered for a certain event.
     *
     * @param	string
     * @return \ILIAS\UI\Component\Signal[]
     */
    public function getTriggeredSignalsFor($event)
    {
        if (!isset($this->triggered_signals[$event])) {
            return [];
        }
        return array_map(
            function ($ts) {
                return $ts->getSignal();
            },
            $this->triggered_signals[$event]
        );
    }

    /**
     * @return $this
     */
    public function withResetTriggeredSignals()
    {
        $clone = clone $this;
        $clone->triggered_signals = array();
        return $clone;
    }

    /**
     * Flatten a multidimensional array to a single dimension
     *
     * @param array $array
     * @return array
     */
    private function flattenArray(array $array)
    {
        $flatten = array();
        array_walk_recursive($array, function ($a) use (&$flatten) {
            $flatten[] = $a;
        });
        return $flatten;
    }
}
