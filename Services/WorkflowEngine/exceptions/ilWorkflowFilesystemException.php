<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/exceptions/ilWorkflowEngineException.php';

/**
 * ilWorkflowFilesystemException is part of the petri net based workflow engine.
 *
 * This exception class is used for all exceptions that are thrown when exceptions
 * regarding accesses to the file system fail.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowFilesystemException extends ilWorkflowEngineException
{
}
