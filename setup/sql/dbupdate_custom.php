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
	//
?>
<#7>
<?php
if($ilDB->tableExists('cal_categories_hidden') )
{
	$ilDB->renameTable('cal_categories_hidden', 'cal_cat_visibility');
	$ilDB->addTableColumn('cal_cat_visibility', 'obj_id', array(
		"type" => "integer",
		"length" => 4,
		"notnull" => true,
		"default" => 0
	));
	$ilDB->addTableColumn('cal_cat_visibility', 'visible', array(
		"type" => "integer",
		"length" => 1,
		"notnull" => true,
		"default" => 0
	));
}
?>
<#8>
<?php
if($ilDB->tableExists('cal_cat_visibility'))
{
	$ilDB->dropPrimaryKey('cal_cat_visibility');
		$ilDB->addPrimaryKey('cal_cat_visibility', array('user_id','cat_id','obj_id'));
}
?>
