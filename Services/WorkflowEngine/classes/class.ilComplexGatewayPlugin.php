<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Abstract parent class for all ComplexGateway plugin classes.
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup ServicesWorkflowEngine
 */
abstract class ilComplexGatewayPlugin extends ilPlugin
{
    /**
     * This method is called by the workflow engine during the transition attempt.
     * Here, the plugin delivers the black box which is called "complex" as the gateway.
     * Return boolean to the engine, if the standard flow should be activated or the "else"-path.
     *
     * @param ilNode $context
     *
     * @return boolean
     */
    abstract public function evaluate(ilNode $context) : bool;
}
