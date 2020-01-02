<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * workflow.php is part of the petri net based workflow engine.
 *
 * This commandline tool is used to run tutorial workflows and
 * diagnostic workflows.
 *
 * Usage:
 * (from Ilias root directory)
 * workflow.php username password client
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
if ($_SERVER['argc'] < 4) {
    die("Usage: workflow.php username password client\r\n");
}

chdir(dirname(__FILE__));
chdir('../../');

include_once './Services/Authentication/classes/class.ilAuthFactory.php';
ilAuthFactory::setContext(ilAuthFactory::CONTEXT_CRON);


$_COOKIE["ilClientId"] = $_SERVER['argv'][3];
$_POST['username'] = $_SERVER['argv'][1];
$_POST['password'] = $_SERVER['argv'][2];

include_once './include/inc.header.php';

echo "\r\n";
echo "-------------------------------------------------------------------------------\r\n";
echo "Executing tutorial workflow: ilPetriNetWorkflow1\r\n";
echo "-------------------------------------------------------------------------------\r\n";
require_once './Services/WorkflowEngine/classes/tutorial/class.ilPetriNetWorkflow1.php';
$workflow1 = new ilPetriNetWorkflow1();
echo "\r\n\r\n\r\n\r\n\r\n";


echo "\r\n";
echo "-------------------------------------------------------------------------------\r\n";
echo "Executing tutorial workflow: ilPetriNetWorkflow2\r\n";
echo "-------------------------------------------------------------------------------\r\n";
require_once './Services/WorkflowEngine/classes/tutorial/class.ilPetriNetWorkflow2.php';
$workflow2 = new ilPetriNetWorkflow2();
echo "\r\n\r\n\r\n\r\n\r\n";


echo "\r\n";
echo "-------------------------------------------------------------------------------\r\n";
echo "Executing tutorial workflow: ilPetriNetWorkflow3\r\n";
echo "-------------------------------------------------------------------------------\r\n";
require_once './Services/WorkflowEngine/classes/tutorial/class.ilPetriNetWorkflow3.php';
$workflow3 = new ilPetriNetWorkflow3();
echo "\r\n\r\n\r\n\r\n\r\n";


echo "\r\n";
echo "-------------------------------------------------------------------------------\r\n";
echo "Executing tutorial workflow: ilPetriNetWorkflow4\r\n";
echo "-------------------------------------------------------------------------------\r\n";

// Instantiate and configure:
require_once './Services/WorkflowEngine/classes/tutorial/class.ilPetriNetWorkflow4.php';
$workflow4 = new ilPetriNetWorkflow4();

// Start the workflow:
$workflow4->startWorkflow();

// Save the workflow to the database.
require_once './Services/WorkflowEngine/classes/utils/class.ilWorkflowDbHelper.php';
ilWorkflowDbHelper::writeWorkflow($workflow4);

// Remove this instance.
unset($workflow4);


// Now we send out the time_passed event.
require_once './Services/WorkflowEngine/classes/utils/class.ilWorkflowUtils.php';
#ilWorkflowUtils::handleTimePassedEvent();

require_once './Services/WorkflowEngine/classes/class.ilWorkflowEngine.php';
$engine = new ilWorkflowEngine();

// Set up params for the event, the event-detector is listening for:
$a_type = 'test';
$a_content = 'pass_passed';
$a_subject_type = 'usr';
$a_subject_id = 6;
$a_context_type = 'tst';
$a_context_id = 5803;

$engine->processEvent(
    $a_type,
    $a_content,
    $a_subject_type,
    $a_subject_id,
    $a_context_type,
    $a_context_id
);

echo "\r\n\r\n\r\n\r\n\r\n";
