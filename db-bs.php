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
	$query = "
		SELECT ud1.usr_id 'u1', ud2.usr_id 'u2'
		FROM addressbook a1
		INNER JOIN usr_data ud1 ON ud1.usr_id = a1.user_id
		INNER JOIN usr_data ud2 ON ud2.login = a1.login
		INNER JOIN addressbook a2 ON a2.user_id = ud2.usr_id AND a2.login = ud1.login
		WHERE ud1.usr_id != ud2.usr_id
	";
	$res = $ilDB->query($query);
	while($row = $ilDB->fetchAssoc($res))
	{
		$this->db->replace(
			'buddylist',
			array(
				'usr_id'       => array('integer', $row['u1']),
				'buddy_usr_id' => array('integer', $row['u2'])
			),
			array(
				'ts' => array('integer', time())
			)
		);

		$this->db->replace(
			'buddylist',
			array(
				'usr_id'       => array('integer', $row['u2']),
				'buddy_usr_id' => array('integer', $row['u1'])
			),
			array(
				'ts' => array('integer', time())
			)
		);
	}

	$query = "
		SELECT ud1.usr_id 'u1', ud2.usr_id 'u2'
		FROM addressbook a1
		INNER JOIN usr_data ud1 ON ud1.usr_id = a1.user_id
		INNER JOIN usr_data ud2 ON ud2.login = a1.login
		LEFT JOIN addressbook a2 ON a2.user_id = ud2.usr_id AND a2.login = ud1.login
		WHERE a2.addr_id IS NULL AND ud1.usr_id != ud2.usr_id
	";
	$res = $ilDB->query($query);
	while($row = $ilDB->fetchAssoc($res))
	{
		$this->db->replace(
			'buddylist_requests',
			array(
				'usr_id'       => array('integer', $row['u1']),
				'buddy_usr_id' => array('integer', $row['u2'])
			),
			array(
				'ts'      => array('integer', time()),
				'ignored' => array('integer', 0)
			)
		);
	}

	$ilDB->dropTable('addressbook');
}
if($ilDB->sequenceExists('addressbook'))
{
	$ilDB->dropSequence('addressbook');
}

$res = $ilDB->queryF(
	'SELECT * FROM notification_usercfg WHERE usr_id = %s AND module = %s AND channel = %s',
	array('integer', 'integer', 'text'),
	array(-1,  'buddysystem_request', 'mail')
);
if(!$ilDB->numRows($res))
{
	$ilDB->insert(
		'notification_usercfg',
		array(
			'usr_id'  => array('integer', -1),
			'module'  => array('text', 'buddysystem_request'),
			'channel' => array('text', 'mail')
		)
	);
}

$res = $ilDB->queryF(
	'SELECT * FROM notification_usercfg WHERE usr_id = %s AND module = %s AND channel = %s',
	array('integer', 'integer', 'text'),
	array(-1,  'buddysystem_request', 'osd')
);
if(!$ilDB->numRows($res))
{
	$ilDB->insert(
		'notification_usercfg',
		array(
			'usr_id'  => array('integer', -1),
			'module'  => array('text', 'buddysystem_request'),
			'channel' => array('text', 'osd')
		)
	);
}
?>