<#1>
<?php
//create il translation table to store translations for title and descriptions
if(!$ilDB->tableExists('il_translations'))
{
	$fields = array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
			),
		'id_type' => array(
			'type' => 'text',
			'length' => 50,
			'notnull' => true
			),
		'lang_code' => array(
			'type' => 'text',
			'length' => 2,
			'notnull' => true
		),
		'title' => array(
			'type' => 'text',
			'length' => 256,
			'fixed' => false,
		),
		'description' => array(
			'type' => 'text',
			'length' => 512,
		),
		'lang_default' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true
		)
	);
	$ilDB->createTable('il_translations', $fields);
	$ilDB->addPrimaryKey("il_translations", array("id", "id_type", "lang_code"));
}
?>
<#2>
<?php
//data migration didactic templates to il_translation
if($ilDB->tableExists('didactic_tpl_settings') && $ilDB->tableExists('il_translations'))
{

	$ini = new ilIniFile(ILIAS_ABSOLUTE_PATH."/ilias.ini.php");

	$lang_default = $ini->readVariable("language","default");

	$ilSetting = new ilSetting();

	if ($ilSetting->get("language") != "")
	{
		$lang_default = $ilSetting->get("language");
	}

	$set = $ilDB->query("SELECT id, title, description".
		" FROM didactic_tpl_settings");

	while($row = $ilDB->fetchAssoc($set))
	{
		$fields = array("id" => array("integer", $row['id']),
			"id_type" => array("text", "dtpl"),
			"lang_code" => array("text", $lang_default),
			"title" => array("text", $row['title']),
			"description" => array("text", $row['description']),
			"lang_default" => array("integer", 1));

		$ilDB->insert("il_translations", $fields);
	}
}

?>
<#3>
<?php
//table to store "effective from" nodes for didactic templates
if(!$ilDB->tableExists('didactic_tpl_en'))
{		
	$fields = array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
			),
		'node' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
			)
	);
	$ilDB->createTable('didactic_tpl_en', $fields);
	$ilDB->addPrimaryKey("didactic_tpl_en", array("id", "node"));
}

?>
<#4>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5>
<?php
if(!$ilDB->tableColumnExists('grp_settings', 'show_members'))
{
	$ilDB->addTableColumn('grp_settings', 'show_members', array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => "1"
		,"type" => "integer"
	));
}
?>
<#6>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$type_id = ilDBUpdateNewObjectType::getObjectTypeId('crs');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_members');

if($type_id && $tgt_ops_id)
{
	ilDBUpdateNewObjectType::addRBACOperation($type_id, $tgt_ops_id);
}
?>
<#7>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$type_id = ilDBUpdateNewObjectType::getObjectTypeId('grp');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_members');

if($type_id && $tgt_ops_id)
{
	ilDBUpdateNewObjectType::addRBACOperation($type_id, $tgt_ops_id);
}
?>
<#8>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_members');
ilDBUpdateNewObjectType::cloneOperation('crs', $src_ops_id, $tgt_ops_id);

?>
<#9>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_members');
ilDBUpdateNewObjectType::cloneOperation('grp', $src_ops_id, $tgt_ops_id);

?>
<#10>
<?php
if(!$ilDB->tableColumnExists('didactic_tpl_settings', 'auto_generated'))
{
	$ilDB->addTableColumn('didactic_tpl_settings', 'auto_generated', array (
		"notnull" => true,
		"length" => 1,
		"default" => 0,
		"type" => "integer"
	));
}
?>
<#11>
<?php
if(!$ilDB->tableColumnExists('didactic_tpl_settings', 'exclusive_tpl'))
{
	$ilDB->addTableColumn('didactic_tpl_settings', 'exclusive_tpl', array (
		"notnull" => true,
		"length" => 1,
		"default" => 0,
		"type" => "integer"
	));
}
?>

<#12>
<?php
$id = $ilDB->nextId('didactic_tpl_settings');
$query = 'INSERT INTO didactic_tpl_settings (id,enabled,type,title, description,info,auto_generated,exclusive_tpl) values( '.
	$ilDB->quote($id, 'integer').', '.
	$ilDB->quote(1,'integer').', '.
	$ilDB->quote(1,'integer').', '.
	$ilDB->quote('grp_closed','text').', '.
	$ilDB->quote('grp_closed_info','text').', '.
	$ilDB->quote('','text').', '.
	$ilDB->quote(1,'integer').', '.
	$ilDB->quote(0,'integer').' '.
	')';
$ilDB->manipulate($query);

$query = 'INSERT INTO didactic_tpl_sa (id, obj_type) values( '.
	$ilDB->quote($id, 'integer').', '.
	$ilDB->quote('grp','text').
	')';
$ilDB->manipulate($query);


$aid = $ilDB->nextId('didactic_tpl_a');
$query = 'INSERT INTO didactic_tpl_a (id, tpl_id, type_id) values( '.
	$ilDB->quote($aid, 'integer').', '.
	$ilDB->quote($id, 'integer').', '.
	$ilDB->quote(1,'integer').
	')';
$ilDB->manipulate($query);

$query = 'select obj_id from object_data where type = '.$ilDB->quote('rolt','text').' and title = '.$ilDB->quote('il_grp_status_closed','text');
$res = $ilDB->query($query);
while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
{
	$closed_id = $row->obj_id;
}

$query = 'INSERT INTO didactic_tpl_alp (action_id, filter_type, template_type, template_id) values( '.
	$ilDB->quote($aid, 'integer').', '.
	$ilDB->quote(3, 'integer').', '.
	$ilDB->quote(2,'integer').', '.
	$ilDB->quote($closed_id,'integer').
	')';
$ilDB->manipulate($query);


$fid = $ilDB->nextId('didactic_tpl_fp');
$query = 'INSERT INTO didactic_tpl_fp (pattern_id, pattern_type, pattern_sub_type, pattern, parent_id, parent_type ) values( '.
	$ilDB->quote($fid, 'integer').', '.
	$ilDB->quote(1, 'integer').', '.
	$ilDB->quote(1,'integer').', '.
	$ilDB->quote('.*','text').', '.
	$ilDB->quote($aid,'integer').', '.
	$ilDB->quote('action','text').
	')';
$ilDB->manipulate($query);

?>
<#13>
<?php
$query =
	"SELECT id FROM didactic_tpl_settings ".
	"WHERE title = " . $ilDB->quote('grp_closed', 'text').
	" AND description = " . $ilDB->quote('grp_closed_info', 'text').
	" AND auto_generated = 1";

$closed_grp = $ilDB->query($query)->fetchRow(ilDBConstants::FETCHMODE_OBJECT)->id;

$query =
	"SELECT objr.obj_id obj_id, objr.ref_id ref_id ".
	"FROM (grp_settings grps JOIN object_reference objr ON (grps.obj_id = objr.obj_id)) ".
	"LEFT JOIN didactic_tpl_objs dtplo ON (dtplo.obj_id = objr.obj_id) ".
	"WHERE grps.grp_type = 1 ".
	"AND (dtplo.tpl_id IS NULL OR dtplo.tpl_id = 0)";
$res = $ilDB->query($query);

while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
{
	$query = 'INSERT INTO didactic_tpl_objs (obj_id,tpl_id,ref_id) '.
		'VALUES( '.
		$ilDB->quote($row->obj_id,'integer').', '.
		$ilDB->quote($closed_grp,'integer').', '.
		$ilDB->quote($row->ref_id,'integer').
		')';
	$ilDB->manipulate($query);
}

?>