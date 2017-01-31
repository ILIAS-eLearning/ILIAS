<#1>
<?php
if($ilDB->tableExists("exc_assignment"))
{
	if(!$ilDB->tableColumnExists('exc_assignment','portfolio_template'))
	{
		$ilDB->addTableColumn("exc_assignment", "portfolio_template", array("type" => "integer", "length" => 4));
	}
}
?>