<#1>
<?php
if(!$ilDB->tableColumnExists('exc_assignment','peer_char'))
{
	$ilDB->addTableColumn('exc_assignment', 'peer_char', array(
		'type' => 'integer',
		'length' => 2,
		'notnull' => false
	));
}
?>
<#2>
<?php
if(!$ilDB->tableColumnExists('exc_assignment','peer_unlock'))
{
	$ilDB->addTableColumn('exc_assignment', 'peer_unlock', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 0
	));
}
?>
<#3>
<?php
if(!$ilDB->tableColumnExists('exc_assignment','peer_valid'))
{
	$ilDB->addTableColumn('exc_assignment', 'peer_valid', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 1
	));
}
?>
<#4>
<?php
if(!$ilDB->tableColumnExists('exc_assignment','team_tutor'))
{
	$ilDB->addTableColumn('exc_assignment', 'team_tutor', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 0
	));
}
?>
<#5>
<?php
if(!$ilDB->tableColumnExists('exc_assignment','max_file'))
{
	$ilDB->addTableColumn('exc_assignment', 'max_file', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false
	));
}
?>
<#6>
<?php
if(!$ilDB->tableColumnExists('exc_assignment','deadline2'))
{
	$ilDB->addTableColumn('exc_assignment', 'deadline2', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false
	));
}
?>
<#7>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#8>
<?php
if(!$ilDB->tableColumnExists('exc_returned','late'))
{
	$ilDB->addTableColumn('exc_returned', 'late', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 0
	));
}
?>
<#9>
<?php

if(!$ilDB->tableExists('exc_crit_cat'))
{
	$ilDB->createTable('exc_crit_cat', array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'parent' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
		),
		'pos' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));	
	$ilDB->addPrimaryKey('exc_crit_cat',array('id'));
	$ilDB->createSequence('exc_crit_cat');
}

?>
<#10>
<?php

if(!$ilDB->tableExists('exc_crit'))
{
	$ilDB->createTable('exc_crit', array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'parent' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'type' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
		),
		'title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
		),
		'descr' => array(
			'type' => 'text',
			'length' => 1000,
			'notnull' => false
		),
		'pos' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));	
	$ilDB->addPrimaryKey('exc_crit',array('id'));
	$ilDB->createSequence('exc_crit');
}

?>
<#11>
<?php

if(!$ilDB->tableColumnExists('exc_crit','required'))
{
	$ilDB->addTableColumn('exc_crit', 'required', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 0
	));
}

?>
<#12>
<?php

if(!$ilDB->tableColumnExists('exc_crit','def'))
{
	$ilDB->addTableColumn('exc_crit', 'def', array(
		'type' => 'text',
		'length' => 4000,
		'notnull' => false
	));
}

?>
<#13>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#14>
<?php

if(!$ilDB->tableColumnExists('exc_assignment','peer_text'))
{
	$ilDB->addTableColumn('exc_assignment', 'peer_text', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 1
	));
}

?>
<#15>
<?php

if(!$ilDB->tableColumnExists('exc_assignment','peer_rating'))
{
	$ilDB->addTableColumn('exc_assignment', 'peer_rating', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 1
	));
}

?>
<#16>
<?php

if(!$ilDB->tableColumnExists('exc_assignment','peer_crit_cat'))
{
	$ilDB->addTableColumn('exc_assignment', 'peer_crit_cat', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false
	));
}
