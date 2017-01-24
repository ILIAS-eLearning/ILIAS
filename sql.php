<#1>
<?php
// Delete before merge!!!!!!!!!!!
if(!$ilDB->tableColumnExists('chatroom_settings','online_status'))
{
	$ilDB->addTableColumn('chatroom_settings', 'online_status', array(
		'type'    => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0
	));
}

$ilDB->manipulateF("UPDATE chatroom_settings SET online_status = %s", array('integer'), array(1));
?>
<#2>
<?php
if(!$ilDB->tableColumnExists('chatroom_bans', 'actor_id'))
{
	$ilDB->addTableColumn('chatroom_bans', 'actor_id',
		array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => false,
			'default' => null
		)
	);
}
?>