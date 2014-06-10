<?php
chdir(dirname(__FILE__));
$ilias_main_directory = './';
while(!file_exists($ilias_main_directory . 'ilias.ini.php'))
{
	$ilias_main_directory .= '../';
}
chdir($ilias_main_directory);

$_GET['baseClass'] = 'ilStartupGUI';

include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_SESSION_REMINDER);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

/**
 * @var $ilDB ilDB
 */
global $ilDB;

echo "Started installing mail templates service...<br />";

// Move these steps to separate database update steps in a dbupdate_custom.php file
/****************************************************************************/
if(!$ilDB->tableExists('cat_mail_templates'))
{
	$fields = array (
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'category_name' => array(
			'type' => 'text',
			'length' => 255),
		'template_type' => array(
			'type' => 'text',
			'length' => 255),
		'consumer_location' => array(
			'type' => 'text',
			'length' => 255)
	);
	$ilDB->createTable('cat_mail_templates', $fields);
	$ilDB->addPrimaryKey('cat_mail_templates', array('id'));
	$ilDB->createSequence('cat_mail_templates');
}
/****************************************************************************/
if(!$ilDB->tableExists('cat_mail_variants#'))
{
	$fields = array (
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'mail_types_fi' => array(
			'type' => 'integer',
			'length' => 4),
		'language' => array(
			'type' => 'text',
			'length' => 255),
		'message_subject' => array(
			'type' => 'text',
			'length' => 255),
		'message_plain' => array(
			'type' => 'clob'),
		'message_html' => array(
			'type' => 'clob'),
		'created_date' => array(
			'type' => 'integer',
			'length' => 4),
		'updated_date' => array(
			'type' => 'integer',
			'length' => 4),
		'updated_usr_fi' => array(
			'type' => 'integer',
			'length' => 4),
		'template_active' => array(
			'type' => 'integer',
			'length' => 4)
	);
	$ilDB->createTable('cat_mail_variants', $fields);
	$ilDB->addPrimaryKey('cat_mail_variants', array('id'));
	$ilDB->createSequence('cat_mail_variants');
}
/****************************************************************************/
// BEGIN: Remove the following lines when moving the statements to a dbupdate_custom.php file
require_once 'setup/classes/class.ilCtrlStructureReader.php';
global $ilClientIniFile, $ilErr;
$ilCtrlStructureReader = new ilCtrlStructureReader();
$ilCtrlStructureReader->setErrorObject($ilErr);
$ilCtrlStructureReader->setIniFile($ilClientIniFile);
// :END
$ilCtrlStructureReader->getStructure();
$ilCtrlStructureReader->readStructure(true);
/****************************************************************************/
echo "Finished";