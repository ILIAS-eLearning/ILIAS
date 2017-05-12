<#1>
<?php
if($ilDB->tableExists('svy_answer'))
{
	if($ilDB->tableColumnExists('svy_answer','textanswer'))
	{
		$ilDB->modifyTableColumn('svy_answer', 'textanswer', array(
			'type'	=> 'clob',
			'notnull' => false
		));
	}
}