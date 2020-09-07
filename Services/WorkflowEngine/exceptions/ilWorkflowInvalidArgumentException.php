<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/exceptions/ilWorkflowEngineException.php';

/**
 * ilWorkflowInvalidArgumentException
 *
 * This exception is raised, when actions are not permitted due to an invalid
 * argument or an invalid combination of argumets.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowInvalidArgumentException extends ilWorkflowEngineException
{
}
