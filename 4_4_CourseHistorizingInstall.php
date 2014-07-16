<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Custom update script for feature branches and patches.
 * 
 * This commandline tool is used to install the necessary tables for the 
 * associated product during development and/or manual install.
 * 
 * If you do not exactly know why you want to run exactly this script, DO NOT RUN IT.
 * 
 * Usage:
 * ------
 * 
 * (from Ilias root directory)
 * <filename>.php username password client 
 * (Use credentials of a user with elevated permissions, preferrably root.
 * 
 * @author Maximilian Becker <mbecker@databay.de>
 * 
 * @version $Id$
 */

if($_SERVER['argc'] < 4)
{
	die("Usage: " . __FILE__ . " username password client\n");
}

chdir(dirname(__FILE__));

require_once './Services/Authentication/classes/class.ilAuthFactory.php';
ilAuthFactory::setContext(ilAuthFactory::CONTEXT_CRON);

$_POST['username'] 		= $_SERVER['argv'][1];
$_POST['password'] 		= $_SERVER['argv'][2];
$_COOKIE["ilClientId"] 	= $_SERVER['argv'][3];

require_once './include/inc.header.php';

/** @var ilDB $ilDB */
global $ilDB;

echo "[INSTALLING / UPDATING Database Tables]\r\n";

// The following code can easily be ported to a dbupdate-file. Please
// note that the code only executes, if the given table does not exist.
// This script however does not deal with step numberings.
// -----------------------------------------------------------------------------

if(!$ilDB->tableExists('hist_course'))
{
	$fields = array (
		'row_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'hist_version' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 1),
		'hist_historic' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'creator_user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'created_ts' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'crs_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'custom_id' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'template_title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'type' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'topic_set' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'begin_date' => array(
			'type' => 'date'),
		'end_date' => array(
			'type' => 'date'),
		'hours' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false,
			'default' => 0),
		'is_expert_course' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0),
		'venue' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false),
		'provider' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false),
		'tutor' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false),
		'max_credit_points' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false),
		'fee' => array(
			'type' => 'float',
			'notnull' => false)
	);
	$ilDB->createTable('hist_course', $fields);
	$ilDB->addPrimaryKey('hist_course', array('row_id'));
	$ilDB->createSequence('hist_course');
}

if(!$ilDB->tableExists('hist_topicset2topic'))
{
	$fields = array (
		'row_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'topic_set_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,),
		'topic_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,),
	);
	$ilDB->createTable('hist_topicset2topic', $fields);
	$ilDB->addPrimaryKey('hist_topicset2topic', array('row_id'));
	$ilDB->createSequence('hist_topicset2topic');
}

if(!$ilDB->tableExists('hist_topics'))
{
	$fields = array (
		'row_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'topic_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,),
		'topic_title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
	);
	$ilDB->createTable('hist_topics', $fields);
	$ilDB->addPrimaryKey('hist_topics', array('row_id'));
	$ilDB->createSequence('hist_topics');
}

// -----------------------------------------------------------------------------
// End

echo "[DONE INSTALLING / UPDATING Database Tables]\r\n";