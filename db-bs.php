<?php
if(!$ilDB->tableExists('buddylist'))
{
	$ilDB->createTable('buddylist', array(
		'usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'buddy_usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'ts' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));
	$ilDB->addPrimaryKey('buddylist', array('usr_id', 'buddy_usr_id'));
}

if(!$ilDB->tableExists('buddylist_requests'))
{
	$ilDB->createTable('buddylist_requests', array(
		'usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'buddy_usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'ignored' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		),
		'ts' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));
	$ilDB->addPrimaryKey('buddylist_requests', array('usr_id', 'buddy_usr_id'));
	$ilDB->addIndex('buddylist_requests', array('buddy_usr_id', 'ignored'), 'i1');
}


$ilDB->manipulate('DELETE FROM addressbook_mlist_ass');
if($ilDB->tableColumnExists('addressbook_mlist_ass', 'addr_id'))
{
	$ilDB->renameTableColumn('addressbook_mlist_ass', 'addr_id', 'usr_id');
}
if($ilDB->tableExists('addressbook'))
{
	$ilDB->dropTable('addressbook');
}
if($ilDB->sequenceExists('addressbook'))
{
	$ilDB->dropSequence('addressbook');
}
?>