<?php

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
 * Class ilWorkflowEngineInstancesGUI
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowEngineInstancesGUI
{
    protected ilObjWorkflowEngineGUI $parent_gui;

    public function __construct(ilObjWorkflowEngineGUI $parent_gui)
    {
        $this->parent_gui = $parent_gui;
    }

    public function handle(string $command) : string
    {
        return "Hello, world";
    }
}
