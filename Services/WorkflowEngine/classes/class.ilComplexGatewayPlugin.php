<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/Component/classes/class.ilPlugin.php';

/**
* Abstract parent class for all ComplexGateway plugin classes.
*
* @author Maximilian Becker <mbecker@databay.de>
* @version $Id$
*
* @ingroup ServicesWorkflowEngine
*/
abstract class ilComplexGatewayPlugin extends ilPlugin
{
    /**
     * Get Component Type
     *
     * @return string Component Type (Service)
     */
    final public function getComponentType()
    {
        return IL_COMP_SERVICE;
    }

    /**
     * Get Component Name.
     *
     * @return string Component Name (WorkflowEngine)
     */
    final public function getComponentName()
    {
        return 'WorkflowEngine';
    }

    /**
     * Get Slot Name.
     *
     * @return string Slot Name (ComplexGateway)
     */
    final public function getSlot()
    {
        return 'ComplexGateway';
    }

    /**
     * Get Slot ID.
     *
     * @return string Slot Id (wfecg)
     */
    final public function getSlotId()
    {
        return "wfecg";
    }

    /**
     * Object initialization done by slot.
     */
    final protected function slotInit()
    {
    }

    /**
     * This method is called by the workflow engine during the transition attempt.
     * Here, the plugin delivers the black box which is called "complex" as the gateway.
     * Return boolean to the engine, if the standard flow should be activated or the "else"-path.
     *
     * @param ilNode $context
     *
     * @return boolean
     */
    abstract public function evaluate(ilNode $context);
}
