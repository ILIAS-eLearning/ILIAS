<?php
// IMPORTANT: Inform the lead developer, if you want to add any steps here.
//
// This is the hotfix file for ILIAS 4.1.x DB fixes
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

	if(!$ilDB->tableColumnExists('ecs_import', 'sub_id'))
	{
		$ilDB->addTableColumn('ecs_import', 'sub_id',
				array(
					"type" => "text",
					"notnull" => false,
					"length" => 64
				)
		);
	}
?>
<#2>
<?php

	if(!$ilDB->tableColumnExists('ecs_course_assignments', 'cms_sub_id'))
	{
		$ilDB->addTableColumn('ecs_course_assignments', 'cms_sub_id',
				array(
					"type" => "integer",
					"notnull" => false,
					"length" => 4,
					'default' => 0
				)
		);
	}
?>
<#3>
<?php

	if(!$ilDB->tableColumnExists('ecs_import', 'ecs_id'))
	{
		$ilDB->addTableColumn('ecs_import', 'ecs_id',
				array(
					"type" => "integer",
					"notnull" => false,
					"length" => 4,
					'default' => 0
				)
		);
	}
?>