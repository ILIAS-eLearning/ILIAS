<#1>
#intial release of database
<#2>
# add column in rbac_fa
ALTER TABLE rbac_fa ADD (`parent_obj` INT(11));
<?php
$query = "SELECT * FROM rbac_fa";
$res = $ilias->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$parent[] = $row->parent;
}
foreach($parent as $par)
{
	$query = "SELECT * FROM tree WHERE child = '".$par."'";
	$res2 = $ilias->db->query($query);
	while($row = $res2->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$parent_obj = $row->parent;
	}
	$ilias->db->query("UPDATE rbac_fa SET parent_obj = '".$parent_obj."' ".
		"WHERE parent = '".$par."'");
}
?>
<#3>
#changing of user preferences handling: only strings
ALTER TABLE user_pref DROP value_int;
ALTER TABLE user_pref CHANGE value_str value CHAR(40) DEFAULT NULL;