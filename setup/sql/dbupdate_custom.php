<#1>
<?php
//
?>
<#2>
<?php
//
?>
<#3>
<?php
//
?>
<#4>
<?php
//
?>
<#5>
//
?>
<#6>
<?php
//
?>
<#7>
<?php
//
?>
<#8>
<?php
//
?>
<#9>
<?php
//
?>
<#10>
<?php
//
?>
<#11>
<?php
//
?>
<#12>
//
?>
<#13>
<?php
//
?>
<#14>
<?php
if (!$ilDB->tableExists('user_action_activation'))
{
	$ilDB->createTable('user_action_activation', array(
		'context_comp' => array(
			'type' => 'text',
			'length' => 30,
			'notnull' => true
		),
		'context_id' => array(
			'type' => 'text',
			'length' => 30,
			'notnull' => true
		),
		'action_comp' => array(
			'type' => 'text',
			'length' => 30,
			'notnull' => true
		),
		'action_type' => array(
			'type' => 'text',
			'length' => 30,
			'notnull' => true
		),
		'active' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('user_action_activation', array('context_comp', 'context_id', 'action_comp', 'action_type'));
}
?>

