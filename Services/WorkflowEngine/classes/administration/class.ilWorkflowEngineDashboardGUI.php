<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilWorkflowEngineDashboardGUI
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowEngineDashboardGUI
{
    /** @var  ilObjWorkflowEngineGUI */
    protected $parent_gui;

    /**
     * ilWorkflowEngineDashboardGUI constructor.
     *
     * @param ilObjWorkflowEngineGUI $parent_gui
     */
    public function __construct(ilObjWorkflowEngineGUI $parent_gui)
    {
        $this->parent_gui = $parent_gui;
    }

    /**
     * @param string $command
     *
     * @return string
     */
    public function handle($command)
    {
        return "";
    }
}
