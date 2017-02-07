<?php

namespace ILIAS\UI\Component\Trigger;

/**
 * Interface Triggerer
 *
 * Any component that can trigger other components must implement this interface
 */
interface Triggerer
{

    /**
     * Trigger an action of another component
     *
     * @param TriggerAction $action
     * @param string $event
     * @return self
     */
    public function triggerAction(TriggerAction $action, $event = 'click');


    /**
     * Get all actions of components triggered by this component
     *
     * @return TriggerAction[]
     */
    public function getTriggerActions();

}