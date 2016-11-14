<#1>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addAdminNode('ltis', 'LTI Settings');

if (!$ilDB->tableExists('lti_ext_consumer'))
{
	$ilDB->createTable('lti_ext_consumer', array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true,
		),
		'description' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true,
		),
		'prefix' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true,
		),
		'consumer_key' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true,
		),
		'consumer_secret' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true,
		),
		'user_language' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true,
		),
		'globalrole_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'active' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)
	));
	$ilDB->addPrimaryKey('lti_ext_consumer',array('id'));
	$ilDB->createSequence('lti_ext_consumer');
}

if (!$ilDB->tableExists('lti_ext_consumer_otype'))
{
	$ilDB->createTable('lti_ext_consumer_otype', array(
		'consumer_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'object_type' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true
		),
	));
	$ilDB->addPrimaryKey('lti_ext_consumer_otype',array('consumer_id', 'object_type'));
}
?>