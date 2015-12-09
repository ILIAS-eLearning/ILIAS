<?php
// IMPORTANT: Inform the lead developer, if you want to add any steps here.
//
// This is the hotfix file for ILIAS 5.0.x DB fixes
// This file should be used, if bugfixes need DB changes, but the
// main db update script cannot be used anymore, since it is
// impossible to merge the changes with the trunk.
//
// IMPORTANT: The fixes done here must ALSO BE reflected in the trunk.
// The trunk needs to work in both cases !!!
// 1. If the hotfixes have been applied.
// 2. If the hotfixes have not been applied.
?>
<#1>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#2>
<?php

$query = "
	UPDATE tst_rnd_quest_set_qpls SET pool_title = (
		COALESCE(
			(SELECT title FROM object_data WHERE obj_id = pool_fi), %s 
		)
	) WHERE pool_title IS NULL OR pool_title = %s
";

$ilDB->manipulateF($query, array('text', 'text'), array('*** unknown/deleted ***', ''));

?>
<#3>
<?php

if( !$ilDB->tableColumnExists('tst_tests', 'broken'))
{
	$ilDB->addTableColumn('tst_tests', 'broken',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => null
		)
	);

	$ilDB->queryF("UPDATE tst_tests SET broken = %s", array('integer'), array(0));
}

?>
<#4>
<?php
$ilDB->manipulate("UPDATE style_data SET ".
	" uptodate = ".$ilDB->quote(0, "integer")
	);
?>
<#5>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#6>
<?php
$ilDB->manipulate("UPDATE tst_active SET last_finished_pass = (tries - 1) WHERE last_finished_pass IS NULL");
?>
<#7>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#8>
<?php

if($ilDB->tableExists('sysc_groups'))
{
	$ilDB->dropTable('sysc_groups');
}

if(!$ilDB->tableExists('sysc_groups'))
{
	$fields = array (
    'id'    => array(
    		'type' => 'integer',
    		'length'  => 4,
    		'notnull' => true),
	'component' => array(
			"type" => "text",
			"notnull" => false,
		 	"length" => 16,
		 	"fixed" => true),

	'last_update' => array(
			"type" => "timestamp",
			"notnull" => false),
		
	'status' => array(
			"type" => "integer",
			"notnull" => true,
			'length' => 1,
			'default' => 0)
	  );
  $ilDB->createTable('sysc_groups', $fields);
  $ilDB->addPrimaryKey('sysc_groups', array('id'));
  $ilDB->createSequence("sysc_groups");
}
?>
<#9>
<?php

if(!$ilDB->tableExists('sysc_tasks'))
{
	$fields = array (
    'id'    => array(
    		'type' => 'integer',
    		'length'  => 4,
    		'notnull' => true),
	'grp_id' => array(
			"type" => "integer",
			"notnull" => TRUE,
		 	"length" => 4),

	'last_update' => array(
			"type" => "timestamp",
			"notnull" => false),
		
	'status' => array(
			"type" => "integer",
			"notnull" => true,
			'length' => 1,
			'default' => 0),
	'identifier' => array(
			"type" => "text",
			"notnull" => FALSE,
			'length' => 64)
	  );
	$ilDB->createTable('sysc_tasks', $fields);
	$ilDB->addPrimaryKey('sysc_tasks', array('id'));
	$ilDB->createSequence("sysc_tasks");
}
?>
<#10>
<?php
	$ilDB->modifyTableColumn('il_dcl_field', 'description', array("type" => "clob"));
?>
<#11>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#12>
<?php
	if(!$ilDB->indexExistsByFields('page_question',array('question_id')))
	{
		$ilDB->addIndex('page_question',array('question_id'),'i2');
	}
?>
<#13>
<?php
	if(!$ilDB->indexExistsByFields('help_tooltip', array('tt_id', 'module_id')))
	{
		$ilDB->addIndex('help_tooltip', array('tt_id', 'module_id'), 'i1');
	}
?>
<#14>
<?php
$delQuery = "
	DELETE FROM tax_node_assignment
	WHERE node_id = %s
	AND component = %s
	AND obj_id = %s
	AND item_type = %s
	AND item_id = %s
";

$types = array('integer', 'text', 'integer', 'text', 'integer');

$selQuery = "
	SELECT tax_node_assignment.* FROM tax_node_assignment
	LEFT JOIN qpl_questions ON question_id = item_id
	WHERE component = %s
	AND item_type = %s
	AND question_id IS NULL
";

$res = $ilDB->queryF($selQuery, array('text', 'text'), array('qpl', 'quest'));

while($row = $ilDB->fetchAssoc($res))
{
	$ilDB->manipulateF($delQuery, $types, array(
		$row['node_id'], $row['component'], $row['obj_id'], $row['item_type'], $row['item_id']
	));
}
?>