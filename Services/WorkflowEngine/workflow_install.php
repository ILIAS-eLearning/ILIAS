<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * workflow_install.php is part of the petri net based workflow engine.
 * 
 * This commandline tool is used to install the necessary tables for the 
 * workflow engine during development and/or for a manual install.
 * 
 * Usage:
 * (from Ilias root directory)
 * workflow_install.php username password client
 * 
 * @author Maximilian Becker <mbecker@databay.de>
 * 
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
if($_SERVER['argc'] < 4)
{
	die("Usage: <file>.php username password client\n");
}

chdir(dirname(__FILE__));
chdir('../../');

include_once 'Services/Authentication/classes/class.ilAuthFactory.php';
ilAuthFactory::setContext(ilAuthFactory::CONTEXT_CRON);

$_COOKIE["ilClientId"] = $_SERVER['argv'][3];
$_POST['username'] = $_SERVER['argv'][1];
$_POST['password'] = $_SERVER['argv'][2];

include_once './include/inc.header.php';

global $ilDB;
echo "[INSTALLING / UPDATING WorkflowEngine Database Tables]\r\n";
// -----------------------------------------------------------------------------

if (!$ilDB->tableExists('wfe_workflows'))
{
	$fields = array (
	'workflow_id'		=> array('type' => 'integer', 'length' => 4, 'notnull' => true),
	'workflow_type'		=> array('type' => 'text',	  'length' => 255),
	'workflow_content'	=> array('type' => 'text',	  'length' => 255),
	'workflow_class'	=> array('type' => 'text',	  'length' => 255),
	'workflow_location' => array('type' => 'text',	  'length' => 255),
	'subject_type'		=> array('type' => 'text',	  'length' => 30),
	'subject_id'		=> array('type' => 'integer', 'length' => 4),
	'context_type'		=> array('type' => 'text',    'length' => 30),
	'context_id'		=> array('type' => 'integer', 'length' => 4),
	'workflow_instance'	=> array('type' => 'clob',	  'notnull' => false, 'default' => null),
	'active'			=> array('type' => 'integer', 'length' => 4)
	);

	$ilDB->createTable('wfe_workflows', $fields);
	$ilDB->addPrimaryKey('wfe_workflows', array('workflow_id'));
	$ilDB->createSequence('wfe_workflows');
}

if (!$ilDB->tableExists('wfe_det_listening'))
{
	$fields = array (
	'detector_id'		=> array('type' => 'integer', 'length' => 4, 'notnull' => true),
	'workflow_id'		=> array('type' => 'integer', 'length' => 4, 'notnull' => true),
	'type'				=> array('type' => 'text',	  'length' => 255),
	'content'			=> array('type' => 'text',	  'length' => 255),
	'subject_type'		=> array('type' => 'text',	  'length' => 30),
	'subject_id'		=> array('type' => 'integer', 'length' => 4),
	'context_type'		=> array('type' => 'text',    'length' => 30),
	'context_id'		=> array('type' => 'integer', 'length' => 4),
	'listening_start'	=> array('type' => 'integer', 'length' => 4),
	'listening_end'		=> array('type' => 'integer', 'length' => 4)
	);
	
	$ilDB->createTable('wfe_det_listening', $fields);
	$ilDB->addPrimaryKey('wfe_det_listening', array('detector_id'));
	$ilDB->createSequence('wfe_det_listening');
}

if(false) // Just once
{
	require_once './Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';
	ilDBUpdateNewObjectType::addAdminNode('wfe', 'WorkflowEngine');
}

if(!$ilDB->tableExists('wfe_startup_events'))
{
	$fields = array (
		'event_id'		=> array('type' => 'integer',	'length' => 4, 	'notnull' => true),
		'workflow_id'	=> array('type' => 'text',		'length' => 60, 'notnull' => true),
		'type'			=> array('type' => 'text',		'length' => 255),
		'content'		=> array('type' => 'text',		'length' => 255),
		'subject_type'	=> array('type' => 'text',		'length' => 30),
		'subject_id'	=> array('type' => 'integer',	'length' => 4),
		'context_type'	=> array('type' => 'text',		'length' => 30),
		'context_id'	=> array('type' => 'integer',	'length' => 4)
	);

	$ilDB->createTable('wfe_startup_events', $fields);
	$ilDB->addPrimaryKey('wfe_startup_events', array('event_id'));
	$ilDB->createSequence('wfe_startup_events');
}

if(!$ilDB->tableExists('wfe_static_inputs'))
{
	$fields = array (
		'input_id'		=> array('type' => 'integer', 'length' => 4, 'notnull' => true),
		'event_id'		=> array('type' => 'integer', 'length' => 4, 'notnull' => true),
		'name'			=> array('type' => 'text',	  'length' => 255),
		'value'			=> array('type' => 'clob')
	);

	$ilDB->createTable('wfe_static_inputs', $fields);
	$ilDB->addPrimaryKey('wfe_static_inputs', array('input_id'));
	$ilDB->createSequence('wfe_static_inputs');
}

// -----------------------------------------------------------------------------
echo "[DONE INSTALLING / UPDATING WorkflowEngine Database Tables]\r\n";
echo "Thanks for your using this awesome RocketScience Product (tm)\r\n";