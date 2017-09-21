<#1>
<?php
	if (!$ilDB->tableColumnExists('event', 'tutor_source')){
		$ilDB->addTableColumn('event', 'tutor_source', array(
			"type" => "integer",
			'length' => 1,
			"notnull" => true,
			"default" => 0
		));
	}
?>
<#2>
<?php
	$table_name = 'event_tutors';
	if (!$ilDB->tableExists($table_name)){
		$fields = array(
			'id' => array(
				'type' 		=> 'integer',
				'length' 	=> 4,
				'notnull' 	=> true,
				'default' 	=> -1
			),
			'obj_id' => array(
				'type' 		=> 'integer',
				'length' 	=> 4,
				'notnull' 	=> true,
				'default' 	=> -1
			),
			'usr_id' => array(
				'type' 		=> 'integer',
				'length' 	=> 4,
				'notnull' 	=> true,
				'default' 	=> -1
			),

		);
		$ilDB->createTable($table_name, $fields);
		$ilDB->createSequence($table_name);
	}
?>
<#3>
<?php
	$ilDB->addPrimaryKey('event_tutors', array("id"));
?>
<#4>
<?php
	// cat-tms-patch start
	$ilDB->insert(
		'mail_tpl_ctx',
		array(
			'id' => array('text', 'crs_context_invitation'),
			'component' => array('text', 'Modules/Course'),
			'class' => array('text', 'ilCourseMailTemplateInvitationContext')
		)
	);
	// cat-tms-patch end
?>
<#5>
<?php
	// cat-tms-patch start
	$ilDB->insert(
		'mail_tpl_ctx',
		array(
			'id' => array('text', 'crs_context_automatic'),
			'component' => array('text', 'Modules/Course'),
			'class' => array('text', 'ilCourseMailTemplateAutomaticContext')
		)
	);
	// cat-tms-patch end
?>
