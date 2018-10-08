<#1>
<?php
include_once './Services/Migration/DBUpdate_5295/classes/class.ilMDCreator.php';
include_once './Services/Migration/DBUpdate_5295/classes/class.ilMD.php';

ilMD::_deleteAllByType('grp');

$group_ids = [];
$query = 'SELECT obd.obj_id, title, od.description FROM object_data obd '.
	'JOIN object_description od on obd.obj_id = od.obj_id '.
	'WHERE type = '.$ilDB->quote('grp','text');
$res = $ilDB->query($query);
while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
{
	$md_creator = new ilMDCreator($row->obj_id, $row->obj_id, 'grp');
	$md_creator->setTitle($row->title);
	$md_creator->setTitleLanguage('en');
	$md_creator->setDescription($row->description);
	$md_creator->setDescriptionLanguage('en');
	$md_creator->setKeywordLanguage('en');
	$md_creator->setLanguage('en');

	$md_creator->create();
}
?>