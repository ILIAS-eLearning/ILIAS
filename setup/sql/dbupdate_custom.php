<#1>
<?php

if(!$ilDB->tableColumnExists('loc_settings','it_type')) 
{
    $ilDB->addTableColumn(
        'loc_settings',
        'it_type',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 5
        ));
}
?>
<#2>
<?php

if(!$ilDB->tableColumnExists('loc_settings','qt_type')) 
{
    $ilDB->addTableColumn(
        'loc_settings',
        'qt_type',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 1
        ));
}

?>

<#3>
<?php

if(!$ilDB->tableColumnExists('loc_settings','it_start')) 
{
    $ilDB->addTableColumn(
        'loc_settings',
        'it_start',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 1
        ));
}

?>

<#4>
<?php

if(!$ilDB->tableColumnExists('loc_settings','qt_start')) 
{
    $ilDB->addTableColumn(
        'loc_settings',
        'qt_start',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 1
        ));
}
?>

<#5>
<?php


$query = 'UPDATE loc_settings SET it_type = '.$ilDB->quote(1,'integer').' WHERE type = '.$ilDB->quote(1,'integer');
$res = $ilDB->manipulate($query);

?>

<#6>
<?php


$query = 'UPDATE loc_settings SET qt_start = '.$ilDB->quote(0,'integer').' WHERE type = '.$ilDB->quote(4,'integer');
$res = $ilDB->manipulate($query);

?>

<#7>
<?php

if(!$ilDB->tableExists('loc_tst_assignments'))
{
	$ilDB->createTable('loc_tst_assignments', array(
		'assignment_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'container_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'assignment_type' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		),
		'objective_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'tst_ref_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('loc_tst_assignments', array('assignment_id'));
	$ilDB->createSequence('loc_tst_assignments');

}
?>

<<<<<<< .working
=======
<#8>
<?php
if( !$ilDB->tableExists('tst_seq_qst_optional') )
{
	$ilDB->createTable('tst_seq_qst_optional', array(
		'active_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'pass' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'question_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));
}
?>

<#9>
<?php
if( !$ilDB->tableColumnExists('tst_sequence', 'ans_opt_confirmed') )
{
	$ilDB->addTableColumn('tst_sequence', 'ans_opt_confirmed', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 0
	));
}
?>

<#10>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#11>
<?php

if(!$ilDB->tableColumnExists('loc_settings','passed_obj_mode')) 
{
    $ilDB->addTableColumn(
        'loc_settings',
        'passed_obj_mode',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 1
        ));
}
?>


<#13>
<?php
$ilCtrlStructureReader->getStructure();
?>

<<<<<<< .working
>>>>>>> .merge-rechts.r57038
=======

>>>>>>> .merge-rechts.r57653
