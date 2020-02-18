<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/classes/workflows/class.ilBaseWorkflow.php';

/**
 * ilEmptyWorkflow is part of the petri net based workflow engine.
 *
 * The empty workflow class is used for workflows with completely runtime generated
 * configurations, such as workflows for use in tests.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilEmptyWorkflow extends ilBaseWorkflow
{
    /**
     * ilEmptyWorkflow constructor.
     */
    public function __construct()
    {
        $this->workflow_type				= 'Empty';
        $this->workflow_content				= 'nothing';
        $this->workflow_subject_type		= 'none';
        $this->workflow_subject_identifier	= '0';
        $this->workflow_context_type		= 'none';
        $this->workflow_context_id			= '0';
        $this->workflow_class				= 'class.ilEmptyWorkflow.php';
        $this->workflow_location			= 'Services/WorkflowEngine/classes/workflows';
    }
}
