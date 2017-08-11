<#1>
<?php
if (!$ilDB->tableExists('cont_skills'))
{
	$ilDB->createTable('cont_skills', array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'skill_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'tref_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('cont_skills',array('id','skill_id','tref_id'));
}
?>
<#2>
<?php
if (!$ilDB->tableExists('cont_member_skills'))
{
	$ilDB->createTable('cont_member_skills', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'tref_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'skill_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'level_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'published' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('cont_member_skills',array('obj_id','user_id','skill_id', 'tref_id'));
}
?>
<#3>
<?php
	include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
	$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('grade', 'Grade', 'object', 2410);
	$type_id = ilDBUpdateNewObjectType::getObjectTypeId('crs');
	if($type_id && $new_ops_id)
	{
		ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);
	}
	$type_id2 = ilDBUpdateNewObjectType::getObjectTypeId('grp');
	if($type_id2 && $new_ops_id)
	{
		ilDBUpdateNewObjectType::addRBACOperation($type_id2, $new_ops_id);
	}
?>
<#4>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

	$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_members');
	$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('grade');
	ilDBUpdateNewObjectType::cloneOperation('crs', $src_ops_id, $tgt_ops_id);
	ilDBUpdateNewObjectType::cloneOperation('grp', $src_ops_id, $tgt_ops_id);
?>