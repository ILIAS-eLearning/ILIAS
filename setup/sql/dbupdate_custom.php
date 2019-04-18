<#1>
<?php
// Empty
?>
<#2>
<?php
/** @var $ilDB \ilDBInterface */
if($ilDB->tableExists('copyright_tos_acpt_his'))
{
	$ilDB->dropTable('copyright_tos_acpt_his');
}
?>
<#3>
<?php
if($ilDB->sequenceExists('copyright_tos_acpt_his'))
{
	$ilDB->dropSequence('copyright_tos_acpt_his');
}
?>
<#4>
<?php
$ilDB->manipulateF('DELETE FROM il_plugin WHERE plugin_id = %s', array('text'), array('cpracpt'));
?>
<#5>
<?php
if($ilDB->sequenceExists('il_meta_description'))
{
	$ilDB->dropSequence('il_meta_description');
}

if($ilDB->sequenceExists('il_meta_description'))
{
	die("Sequence could not be dropped!");
}
else
{
	$query = 'SELECT MAX(meta_description_id) max_desc_id FROM il_meta_description';
	$res = $ilDB->query($query);
	$row = $ilDB->fetchAssoc($res);

	$start = (int)$row['max_desc_id'];

	$start = $start + 100; // add + 100 to be save

	$ilDB->createSequence('il_meta_description', $start);
}
?>
<#6>
<?php
if ($ilDB->tableExists('event'))
{
	$ilDB->addTableColumn(
		'event',
		'reg_notification', array(
			'type'    => 'integer',
			'notnull' => true,
			'default' => 0
		)
	);

	$ilDB->addTableColumn(
		'event',
		'notification_opt',
		array(
			'type'    => 'text',
			'length'  => '50',
			'notnull' => false,
			'default' => 'notification_option_manual' // ilSessionConstants::NOTIFICATION_INHERIT_OPTION
		)
	);
}
?>
<#7>
<?php
if ($ilDB->tableExists('event_participants'))
{
	$ilDB->addTableColumn(
		'event_participants',
		'notification_enabled', array(
			'type'    => 'integer',
			'notnull' => true,
			'default' => 0
		)
	);
}
?>

