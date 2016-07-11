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
<#7>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#8>
<?php
if (!$ilDB->tableExists('glo_term_reference'))
{
	$ilDB->createTable('glo_term_reference', array(
		'glo_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'term_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));
	$ilDB->addPrimaryKey('glo_id', array('term_id'));
}
?>

