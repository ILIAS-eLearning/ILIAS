<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
     * @return bool
     */
    abstract public function evaluate(ilNode $context): bool;
}
