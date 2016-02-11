<#1>
<?php
$fields = 
	array('obj_id' => array(
			'type' 		=> 'integer',
			'length' 	=> 4,
			'notnull' 	=> false,
			'default' 	=> 0
		),
		'is_online' => array(
			'type' 		=> 'integer',
			'length' 	=> 1,
			'notnull' 	=> false,
			'default' 	=> 0
		),
		'last_changed_date' => array(
			'type' 		=> 'timestamp',
			'notnull' 	=> false,
			'default' 	=> "0000-00-00 00:00:00"
		),
		'last_changed_user' => array(
			'type' 		=> 'integer',
			'length' 	=> 4,
			'notnull' 	=> false,
			'default' 	=> 0
		)
	);

if(!$ilDB->tableExists("rep_obj_bbpool")) {
	$ilDB->createTable("rep_obj_bbpool", $fields);
	$ilDB->addPrimaryKey("rep_obj_bbpool", array("obj_id"));
}
?>

<#2>
<?php
$new_xbbp_ops = array(
	'use_building_block' => array('Use Building Block', 10001),
	'edit_building_blocks' => array('Edit Building Blocks', 10002)
);
require_once "Customizing/class.ilCustomInstaller.php";
ilCustomInstaller::addRBACOps('xbbp', $new_xbbp_ops);
?>