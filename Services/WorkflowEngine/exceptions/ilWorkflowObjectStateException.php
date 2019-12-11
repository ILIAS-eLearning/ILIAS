<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/exceptions/ilWorkflowEngineException.php';

/**
 * ilWorkflowObjectStateException is part of the petri net based workflow engine.
 *
 * This exception is raised, when actions are not permitted due to an invalid
 * object state.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowObjectStateException extends ilWorkflowEngineException
{
}
