<#2949>
<?php
	// empty step
?>
<#2950>
<?php
	$ilDB->modifyTableColumn('table_properties', 'value',
		array("type" => "text", "length" => 4000, "notnull" => true));
?>