<#1>
<?php
if(!$ilDB->tableExists('osc_activity'))
{
	$ilDB->createTable(
		'osc_activity',
		array(
			'conversation_id' => array(
				'type'    => 'text',
				'length'  => 255,
				'notnull' => true
			),
			'user_id'         => array(
				'type'    => 'integer',
				'length'  => 4,
				'notnull' => true,
				'default' => 0
			),
			'timestamp'      => array(
				'type'    => 'integer',
				'length'  => 8,
				'notnull' => true,
				'default' => 0
			)
		)
	);
}
?>
<#2>
<?php
if(!$ilDB->tableExists('osc_messages'))
{
	$ilDB->createTable(
		'osc_messages',
		array(
			'id'             => array(
				'type'    => 'text',
				'length'  => 255,
				'notnull' => true
			),
			'conversation_id' => array(
				'type'    => 'text',
				'length'  => 255,
				'notnull' => true
			),
			'user_id'         => array(
				'type'    => 'integer',
				'length'  => 4,
				'notnull' => true,
				'default' => 0
			),
			'message'        => array(
				'type'    => 'clob',
				'notnull' => false,
				'default' => null
			),
			'timestamp'      => array(
				'type'    => 'integer',
				'length'  => 8,
				'notnull' => true,
				'default' => 0
			)
		)
	);
	$ilDB->addPrimaryKey('osc_messages', array('id'));
}
?>
<#3>
<?php
if(!$ilDB->tableExists('osc_conversation'))
{
	$ilDB->createTable(
		'osc_conversation',
		array(
			'id'             => array(
				'type'    => 'text',
				'length'  => 255,
				'notnull' => true
			),
			'is_group' => array(
				'type'    => 'integer',
				'length'  => 1,
				'notnull' => true,
				'default' => 0
			),
			'participants' => array(
				'type'    => 'text',
				'length'  => 4000,
				'notnull' => false,
				'default' => null
			)
		)
	);
	$ilDB->addPrimaryKey('osc_conversation', array('id'));
}
?>
<#4>
<?php
if(!$ilDB->uniqueConstraintExists('osc_activity', array('conversation_id', 'user_id')))
{
	$ilDB->addUniqueConstraint('osc_activity', array('conversation_id', 'user_id'), 'uc1');
}
?>
<#5>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#6>
<?php
if($ilDB->uniqueConstraintExists('osc_activity', array('conversation_id', 'user_id')))
{
	$ilDB->dropUniqueConstraintByFields('osc_activity', array('conversation_id', 'user_id'));
}
?>
<#7>
<?php
$ilDB->addPrimaryKey('osc_activity', array('conversation_id', 'user_id'));
?>