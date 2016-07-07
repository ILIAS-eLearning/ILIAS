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
<?php
	//
?>
<#6>
<?php
if (!$ilDB->tableExists('glo_glossaries'))
{
	$ilDB->createTable('glo_glossaries', array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'glo_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));
}
?>