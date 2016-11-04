<#1>
<?php
	if($ilDB->tableExists('svy_qst_oblig'))
		$ilDB->dropTable('svy_qst_oblig');
	if($ilDB->tableExists('svy_qst_oblig_seq'))
		$ilDB->dropTable('svy_qst_oblig_seq');
?>
</#1>