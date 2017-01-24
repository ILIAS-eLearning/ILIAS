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