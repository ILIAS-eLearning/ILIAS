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

