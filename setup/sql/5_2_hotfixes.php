<?php
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
$ilDB->modifyTableColumn(
	'wiki_stat_page',
	'num_ratings',
	array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true,
		'default' => 0
	)
);
?>
<#3>
<?php
$ilDB->modifyTableColumn(
	'wiki_stat_page',
	'avg_rating',
	array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true,
		'default' => 0
	)
);
?>
<#4>
<?php
$query = "SELECT value FROM settings WHERE module = %s AND keyword = %s";
$res = $ilDB->queryF($query, array('text', 'text'), array("mobs", "black_list_file_types"));
if (!$ilDB->fetchAssoc($res))
{
	$mset = new ilSetting("mobs");
	$mset->set("black_list_file_types", "html");
}
?>
<#5>
<?php
// #0020342
$query = $ilDB->query('SELECT 
    stloc.*
FROM
    il_dcl_stloc2_value stloc
        INNER JOIN
    il_dcl_record_field rf ON stloc.record_field_id = rf.id
        INNER JOIN
    il_dcl_field f ON rf.field_id = f.id
WHERE
    f.datatype_id = 3
ORDER BY stloc.id ASC');

while ($row = $query->fetchAssoc()) {
	$query2 = $ilDB->query('SELECT * FROM il_dcl_stloc1_value WHERE record_field_id = ' . $ilDB->quote($row['record_field_id'], 'integer'));
	if ($ilDB->numRows($query2)) {
		$rec = $ilDB->fetchAssoc($query2);
		if ($rec['value'] != null) {
			continue;
		}
	}

	$id = $ilDB->nextId('il_dcl_stloc1_value');
	$ilDB->insert('il_dcl_stloc1_value', array(
		'id' => array('integer', $id),
		'record_field_id' => array('integer', $row['record_field_id']),
		'value' => array('text', $row['value']),
	));
	$ilDB->manipulate('DELETE FROM il_dcl_stloc2_value WHERE id = ' . $ilDB->quote($row['id'], 'integer'));
}
?>
<#6>
<?php

$ilDB->manipulate('update grp_settings set registration_start = '. $ilDB->quote(null, 'integer').', '.
	'registration_end = '.$ilDB->quote(null, 'integer') .' '.
	'where registration_unlimited = '.$ilDB->quote(1,'integer')
);
?>

<#7>
<?php
$ilDB->manipulate('update crs_settings set '
	.'sub_start = ' . $ilDB->quote(null,'integer').', '
	.'sub_end = '.$ilDB->quote(null,'integer').' '
	.'WHERE sub_limitation_type != '.$ilDB->quote(2,'integer')
);
	
?>