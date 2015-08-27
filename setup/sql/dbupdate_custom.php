<#1>
<?php
	if (!$ilDB->tableColumnExists("booking_schedule", "av_from"))
	{
		$ilDB->addTableColumn("booking_schedule", "av_from", array(
			"type" => "integer",
			"notnull" => false,
			"length" => 4
		));
	}
	if (!$ilDB->tableColumnExists("booking_schedule", "av_to"))
	{
		$ilDB->addTableColumn("booking_schedule", "av_to", array(
			"type" => "integer",
			"notnull" => false,
			"length" => 4
		));
	}
?>