<?php

namespace ILIAS\UI\Component\Trigger;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * Interface TriggerAction
 */
interface TriggerAction
{

    /**
     * Get the component executing this action
     *
     * @return \ILIAS\UI\Component\Component
     */
    public function getComponent();

    /**
     * Get the event triggering this action
     *
     * @return string
     */
    public function getEvent();


    /**
     * @param string $event
     */
    public function setEvent($event);


    /**
     * @param \Closure $closure
     */
    public function setJavascriptBinding(\Closure $closure);


    /**
     * @return \Closure
     */
    public function getJavascriptBinding();
}