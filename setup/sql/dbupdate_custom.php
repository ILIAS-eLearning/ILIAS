<#1>
<?php

if( !$ilDB->tableExists('adv_md_values_text') )
{
	$ilDB->renameTable('adv_md_values', 'adv_md_values_text');
}

?>
<#2>
<?php

if( !$ilDB->tableExists('adv_md_values_int') )
{
	$ilDB->createTable('adv_md_values_int', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'value' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_int', array('obj_id', 'sub_type', 'sub_id'));
}

?>
<#3>
<?php

if( !$ilDB->tableExists('adv_md_values_float') )
{
	$ilDB->createTable('adv_md_values_float', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'value' => array(
			'type' => 'float',			
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_float', array('obj_id', 'sub_type', 'sub_id'));
}

?>
<#4>
<?php

if( !$ilDB->tableExists('adv_md_values_date') )
{
	$ilDB->createTable('adv_md_values_date', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'value' => array(
			'type' => 'date',			
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_date', array('obj_id', 'sub_type', 'sub_id'));
}

?>
<#5>
<?php

if( !$ilDB->tableExists('adv_md_values_datetime') )
{
	$ilDB->createTable('adv_md_values_datetime', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'value' => array(
			'type' => 'timestamp',			
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_datetime', array('obj_id', 'sub_type', 'sub_id'));
}

?>
<#6>
<?php

if( !$ilDB->tableExists('adv_md_values_location') )
{
	$ilDB->createTable('adv_md_values_location', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'loc_lat' => array(
			'type' => 'float',			
			'notnull' => false
		),
		'loc_long' => array(
			'type' => 'float',			
			'notnull' => false
		),
		'loc_zoom' => array(
			'type' => 'integer',			
			'length' => 1,
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_location', array('obj_id', 'sub_type', 'sub_id'));
}

?>
<#7>
<?php

	if (!$ilDB->tableColumnExists('adv_md_values_location', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_location', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	if (!$ilDB->tableColumnExists('adv_md_values_datetime', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_datetime', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	if (!$ilDB->tableColumnExists('adv_md_values_date', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_date', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	if (!$ilDB->tableColumnExists('adv_md_values_float', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_float', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	if (!$ilDB->tableColumnExists('adv_md_values_int', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_int', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	
?>
<#8>
<?php

// #6/#7/#8

$ilDB->dropPrimaryKey('adv_md_values_int');
$ilDB->addPrimaryKey('adv_md_values_int', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
$ilDB->dropPrimaryKey('adv_md_values_float');
$ilDB->addPrimaryKey('adv_md_values_float', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
$ilDB->dropPrimaryKey('adv_md_values_date');
$ilDB->addPrimaryKey('adv_md_values_date', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
$ilDB->dropPrimaryKey('adv_md_values_datetime');
$ilDB->addPrimaryKey('adv_md_values_datetime', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
$ilDB->dropPrimaryKey('adv_md_values_location');
$ilDB->addPrimaryKey('adv_md_values_location', array('obj_id', 'sub_type', 'sub_id', 'field_id'));

?>

<#9>
<?php

// Copied from CourseBooking install skript

require_once "Customizing/class.ilCustomInstaller.php";

if(!$ilDB->tableExists('crs_book'))
{
	$ilDB->createTable('crs_book', array(
		'crs_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
		,'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
		,'status' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)
		,'status_changed_by' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
		,'status_changed_on' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('crs_book', array('crs_id', 'user_id'));
}

$new_crs_ops = array(
	'view_bookings' => array('View Bookings', 2900)
	,'book_users' => array('Book Users', 3500)
	,'cancel_bookings' => array('Cancel Bookings', 3800)
);
ilCustomInstaller::addRBACOps('crs', $new_crs_ops);

$new_org_ops = array(
	'view_employee_bookings' => array('View Employee Bookings', 2901)
	,'view_employee_bookings_rcrsv' => array('View Employee Bookings (recursive)', 2902)
	,'book_employees' => array('Book Employees', 3501)
	,'book_employees_rcrsv' => array('Book Employees (recursive)', 3502)
	,'cancel_employee_bookings' => array('Cancel Employee Bookings', 3801)
	,'cancel_employee_bookings_rcrsv' => array('Cancel Employee Bookings (recursive)', 3802)
);
ilCustomInstaller::addRBACOps('orgu', $new_org_ops);

$lang_data = array(
	"admin_tab_list_bookings" => array("Buchungen", "Bookings")
	,"admin_tab_list_cancellations" => array("Stornierungen", "Cancellations")
	,"admin_add_group" => array("Gruppe hinzufügen", "Add Group")
	,"admin_add_org_unit" => array("Org-Einheit hinzufügen", "Add Org-Unit")
	,"admin_status_booked" => array("Gebucht", "Booked")
	,"admin_status_waiting" => array("Auf Warteliste", "Waiting")
	,"admin_status_cancelled_with_costs" => array("Mit Kosten storniert", "Cancelled with Costs")
	,"admin_status_cancelled_without_costs" => array("Ohne Kosten storniert", "Cancelled without Costs")
	,"admin_assign_confirm" => array("Bestätigen Sie bitte die Buchung der folgenden Teilnehmer.", "Please confirm the booking of the following members.")
	,"admin_assign_cancelled_user" => array("Wurde bereits storniert", "Was already cancelled")
	,"admin_assign_not_bookable_user" => array("Ist nicht buchbar", "Is not bookable")
	,"admin_assign_already_assigned" => array("Ist bereits gebucht (oder auf der Warteliste)", "Is already booked (or waiting)")
	,"admin_add_participants" => array("Teilnehmer hinzufügen", "Add Participants")
	,"admin_status" => array("Buchungsstatus", "Booking Status")
	,"admin_do_not_add" => array("Nicht hinzufügen", "Do not Add")
	,"admin_group_has_no_members" => array("Die gewählte Gruppe hat keine Mitglieder", "The selected group has no members")
	,"admin_status_change" => array("Änderung des Buchungsstatus", "Booking Status Change")
	,"admin_action_to_waiting_list" => array("Auf die Warteliste", "To Waiting List")
	,"admin_action_book" => array("Als Teilnehmer hinzufügen", "Add to Participants")
	,"admin_action_cancel_without_costs" => array("Ohne Kosten stornieren", "Cancel without Costs")
	,"admin_action_cancel_with_costs" => array("Mit Kosten stornieren", "Cancel with Costs")
	,"admin_user_action_confirm_ToWaitingList" => array("Wollen Sie den Teilnehmer wirklich auf die Warteliste verschieben?", "Do you really want to put the participant on the waiting list?")
	,"admin_user_action_confirm_Book" => array("Wollen Sie die Teilnahme wirklich buchen?", "Do you really want to book the user?")
	,"admin_user_action_confirm_CancelWithoutCosts" => array("Wollen Sie den Teilnahme wirklich ohne Kosten stornieren?", "Do you really want to cancel without costs?")
	,"admin_user_action_confirm_CancelWithCosts" => array("Wollen Sie den Teilnahme wirklich mit Kosten stornieren", "Do you really want to cancel with costs?")
	,"admin_user_action_done" => array("Der Status des Benutzer wurde erfolgreich geändert.", "User status changed succesfully.")
	,"admin_org_add_recursive" => array("Mitglieder von Untereinheiten auswählen", "Include Members of Sub-Org-Units")
	,"admin_org_unit_has_no_members" => array("Die gewählte Org-Einheit hat keine Mitglieder", "The selected org-unit has no members")
	,"admin_assign_overlapping_user" => array("Folgende Buchungen überschneiden sich", "The following bookings are overlapping")
);

ilCustomInstaller::addLangData("crsbook", array("de", "en"), $lang_data, "patch generali - course booking");

?>

<#10>
<?php
if(!$ilDB->tableExists('orgu_types')) {
    $fields = array (
        'id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true, 'default' => 0),
        'default_lang'   => array ('type' => 'text', 'notnull' => true, 'length' => 4, 'fixed' => false),
        'icon'    => array ('type' => 'text', 'length'  => 256, 'notnull' => false),
        'owner' => array('type' => 'integer', 'notnull' => true, 'length' => 4),
        'create_date'  => array ('type' => 'timestamp'),
        'last_update' => array('type' => 'timestamp'),
    );
    $ilDB->createTable('orgu_types', $fields);
    $ilDB->addPrimaryKey('orgu_types', array('id'));
    $ilDB->createSequence('orgu_types');
}
?>

<#11>
<?php
if(!$ilDB->tableExists('orgu_data')) {
    $fields = array (
        'orgu_id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true, 'default' => 0),
        'orgu_type_id'   => array ('type' => 'integer', 'notnull' => false, 'length' => 4),
    );
    $ilDB->createTable('orgu_data', $fields);
    $ilDB->addPrimaryKey('orgu_data', array('orgu_id'));
}
?>

<#12>
<?php
if(!$ilDB->tableExists('orgu_types_trans')) {
    $fields = array (
        'orgu_type_id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true),
        'lang'   => array ('type' => 'text', 'notnull' => true, 'length' => 4),
        'member'    => array ('type' => 'text', 'length'  => 32, 'notnull' => true),
        'value' => array('type' => 'text', 'length' => 4000, 'notnull' => false),
    );
    $ilDB->createTable('orgu_types_trans', $fields);
    $ilDB->addPrimaryKey('orgu_types_trans', array('orgu_type_id', 'lang', 'member'));
}
?>

<#13>
<?php
if(!$ilDB->tableExists('orgu_types_adv_md_rec')) {
    $fields = array (
        'type_id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true),
        'rec_id'   => array ('type' => 'integer', 'notnull' => true, 'length' => 4),
    );
    $ilDB->createTable('orgu_types_adv_md_rec', $fields);
    $ilDB->addPrimaryKey('orgu_types_adv_md_rec', array('type_id', 'rec_id'));
}
?>

<#14>
<?php

// copied from MailTemplates install

if(!$ilDB->tableExists('cat_mail_templates'))
{
	$fields = array (
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'category_name' => array(
			'type' => 'text',
			'length' => 255),
		'template_type' => array(
			'type' => 'text',
			'length' => 255),
		'consumer_location' => array(
			'type' => 'text',
			'length' => 255)
	);
	$ilDB->createTable('cat_mail_templates', $fields);
	$ilDB->addPrimaryKey('cat_mail_templates', array('id'));
	$ilDB->createSequence('cat_mail_templates');
}
?>

<#15>
<?php

// copied from MailTemplates install

if(!$ilDB->tableExists('cat_mail_variants'))
{
	$fields = array (
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'mail_types_fi' => array(
			'type' => 'integer',
			'length' => 4),
		'language' => array(
			'type' => 'text',
			'length' => 255),
		'message_subject' => array(
			'type' => 'text',
			'length' => 255),
		'message_plain' => array(
			'type' => 'clob'),
		'message_html' => array(
			'type' => 'clob'),
		'created_date' => array(
			'type' => 'integer',
			'length' => 4),
		'updated_date' => array(
			'type' => 'integer',
			'length' => 4),
		'updated_usr_fi' => array(
			'type' => 'integer',
			'length' => 4),
		'template_active' => array(
			'type' => 'integer',
			'length' => 4)
	);
	$ilDB->createTable('cat_mail_variants', $fields);
	$ilDB->addPrimaryKey('cat_mail_variants', array('id'));
	$ilDB->createSequence('cat_mail_variants');
}

?>

<#16>
<?php

// copied from installer of ParticipationStatus

// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

//
// create database tables
// 

if(!$ilDB->tableExists('crs_pstatus_crs'))
{
	$ilDB->createTable('crs_pstatus_crs', array(
		'crs_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
		,'state' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)
		,'alist' => array(
			'type' => 'text',
			'length' => 1000,
			'notnull' => false,
			'fixed' => false
		)			
	));

	$ilDB->addPrimaryKey('crs_pstatus_crs', array('crs_id'));
	
	
	$ilDB->createTable('crs_pstatus_usr', array(
		'crs_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
		,'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
		,'status' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
		)
		,'cpoints' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false,
		)			
		,'changed_by' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
		,'changed_on' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('crs_pstatus_usr', array('crs_id', 'user_id'));
}


//
// create RBAC permissions (incl. org unit?)
//

$new_crs_ops = array(
	'view_participation_status' => array('View Participation Status', 2902)
	,'set_participation_status' => array('Set Participation Status', 3502)
	,'review_participation_status' => array('Review Participation Status', 3503)
);
ilCustomInstaller::addRBACOps('crs', $new_crs_ops);


//
// lng variables
//

$lang_data = array(
	"status_not_set" => array("Nicht gesetzt", "Not set")
	,"status_successful" => array("Erfolgreich", "Successful")
	,"status_absent_excused" => array("Abwesend (entschuldigt)", "Absent (excused)")
	,"status_absent_not_excused" => array("Abwesend (unentschuldigt)", "Absent (not excused)")	
	,"admin_tab_list_status" => array("Teilnahmestatus", "Participation Status")	
	,"admin_start_date_not_reached" => array("Der Teilnahmestatus kann erst ab %s gesetzt werden.", "The participation status can be set after %s.")	
	,"admin_status" => array("Teilnahmestatus", "Participation Status")	
	,"admin_credit_points" => array("Bildungspunkte", "Credit Points")	
	,"admin_finalize" => array("Finalisieren", "Finalize")	
	,"admin_changed_by" => array("Letzte Änderung", "Last Change")	
	,"admin_finalize_need_attendance_list" => array("Eine Anwesenheitsliste wird noch benötigt.", "An attendance list is required.")	
	,"admin_finalize_need_not_status_set" => array("Der Teilnahmestatus muss für jeden Teilnehmer gesetzt sein.", "The participation status has to be set for all members.")	
	,"admin_confirm_finalize" => array("Wollen Sie wirklich den Teilnahmestatus finalisieren?", "Are you sure you really want to finalize the participation status?")
	,"admin_attendance_list" => array("Anwesenheitsliste", "Attendance List")
	,"admin_view_attendance_list" => array("Anwesenheitsliste anzeigen", "View Attendance List")
	,"admin_no_attendance_list" => array("Es wurde keine Anwesenheitsliste hochgeladen.", "No attendance list uploaded.")
	
);

ilCustomInstaller::addLangData("ptst", array("de", "en"), $lang_data, "patch generali - participation status");

?>

<#17>
<?php

// Automail-Info for the Generali

$fields = array (
     'crs_id' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
         ),
     'mail_id' => array(
         "type" => "text",
         "length" => "50",
         "notnull" => true
         ),
     'last_send' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true,
         )
);

$ilDB->createTable('gev_automail_info', $fields);
$ilDB->addPrimaryKey('gev_automail_info', array('crs_id', 'mail_id'));

?>

<#18>
<?php

$fields = array (
     'id' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
         ),
     'obj_id' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
         ),
     'moment' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
         ),
     'occasion' => array(
         "type" => "text",
         "length" => "100",
         "notnull" => true
         ),
     'mail_to' => array(
         "type" => "text",
         "length" => "200",
         "notnull" => true
         ),
     'mail_from' => array(
         "type" => "text",
         "length" => "200",
         "notnull" => true
         ),
     'cc' => array(
         "type" => "clob"
         ),
     'bcc' => array(
         "type" => "clob"
         ),
     'subject' => array(
         "type" => "text",
         "length" => "200",
         "notnull" => true
         ),
     'message' => array(
         "type" => "clob"
         ),
     'attachments' => array(
         "type" => "clob"
         )
);

$ilDB->createTable('mail_log', $fields);
$ilDB->addPrimaryKey('mail_log', array('id'));
$ilDB->createSequence("mail_log");

?>

<#19>
<?php

$fields = array (
     'obj_id' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
         ),
     'filename' => array(
         "type" => "text",
         "length" => "255",
         "notnull" => true
         ),
     'lock_count' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
     )
);


$ilDB->createTable('mail_attachment_locks', $fields);

# set collation since mysql complains about the length of primary 
# key for some collations.
$ilDB->manipulate("ALTER TABLE `mail_attachment_locks` CHANGE `filename` `filename` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ");

$ilDB->addPrimaryKey('mail_attachment_locks', array('obj_id', 'filename'));

?>

<#20>
<?php

$fields = array (
     'crs_id' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
         ),
     'send_list_to_accom' => array(
         'type' => 'integer',
         'length' => 1,
         'notnull' => true
         ),
     'send_list_to_venue' => array(
         'type' => 'integer',
         'length' => 1,
         'notnull' => true
         )
);

$ilDB->createTable('gev_crs_addset', $fields);
$ilDB->addPrimaryKey('gev_crs_addset', array('crs_id'));

?>

<#21>
<?php

// INVITATION_MAIL_SETTINGS for the VoFue

$fields = array (
     'crs_id' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
         ),
     'function_name' => array(
         "type" => "text",
         "length" => "50",
         "notnull" => true
         ),
     'template_id' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true,
         ),
     'attachments' => array(
         "type" => "clob"
         )
);

$ilDB->createTable('gev_crs_invset', $fields);
$ilDB->addPrimaryKey('gev_crs_invset', array('crs_id', 'function_name'));

?>

<#22>
<?php

$ilDB->addTableColumn( "gev_crs_addset"
					 , "inv_mailing_date"
					 , array( "type" 	=> "integer"
					 		, "length"	=> 4
					 		, "notnull"	=> true
					 		)
					 );

?>

<#23>
<?php

// GEV_USER_REGISTRATION_TOKENS for GEV

$fields = array (
     'token' => array(
         'type' => 'text',
         'length' => 32,
         'notnull' => true
         ),
     'stelle' => array(
         "type" => "text",
         "length" => 6,
         "notnull" => true
         ),
     'username' => array(
         'type' => 'text',
         'length' => 100,
         'notnull' => true
         ),
     'email' => array(
         'type' => 'text',
         'length' => 100,
         'notnull' => true
         ),
     'email_sent' => array(
         'type' => 'timestamp',
         'notnull' => false
         ),
     'token_used' => array(
         'type' => 'timestamp',
         'notnull' => false
         ),
     'password_changed' => array(
         'type' => 'timestamp',
         'notnull' => false
         ),
     "firstname" => array(
     	"type" 	=> "text",
		"length"	=> 32,
		"notnull"	=> true
		),
     "lastname" => array(
     	"type" 	=> "text",
		"length"	=> 32,
		"notnull"	=> true
		),
	"gender" => array(
		"type" 	=> "text",
		"length"	=> 1,
		"notnull"	=> true
		)
);

$ilDB->createTable('gev_user_reg_tokens', $fields);
$ilDB->addPrimaryKey('gev_user_reg_tokens', array('token'));

?>


<#24>
<?php

// copied from install of Accomodations

require_once "Customizing/class.ilCustomInstaller.php";

//
// create database tables
// 

if(!$ilDB->tableExists('crs_acco'))
{
	$ilDB->createTable('crs_acco', array(
		'crs_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
		,'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
		,'night' => array(
			'type' => 'date',			
			'notnull' => true
		)			
	));

	$ilDB->addPrimaryKey('crs_acco', array('crs_id', 'user_id', 'night'));
}


//
// create RBAC permissions 
//

$new_crs_ops = array(
	'view_own_accomodations' => array('View Own Accomodations', 2906)
	,'set_own_accomodations' => array('Set Own Accomodations', 2907)
	,'view_others_accomodations' => array('View Others Accomodations', 3507)
	,'set_others_accomodations' => array('Set Others Accomodations', 3508)		
);
ilCustomInstaller::addRBACOps('crs', $new_crs_ops);

//
// lng variables
//

$lang_data = array(
	"tab_list_accomodations" => array("Übernachtungen", "Accomodations")	
	,"accomodations" => array("Übernachtungen", "Accomodations")	
	,"edit_user_accomodations" => array("Teilnehmer bearbeiten", "Edit participant")	
	,"period_input_from" => array("von", "from")	
	,"period_input_to" => array("bis", "to")	
	,"period_input_from_first" => array("Vorabend", "Previous Evening")	
	,"period_input_to_last" => array("nächster Morgen", "Next Morning")	
);

ilCustomInstaller::addLangData("acco", array("de", "en"), $lang_data, "patch generali - accomodations");

?>

<#25>
<?php

// copied from install script of TEP

// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

//
// create database tables
// 

if(!$ilDB->tableExists('cal_derived_entry'))
{
	// global TEP calendar
	$crs_set = new ilSetting("TEP");
	$crs_cal_id = $crs_set->get("crs_calendar");
	if(!$crs_cal_id)
	{
		$calcat_id = $ilDB->nextId('cal_categories');

		$query = "INSERT INTO cal_categories (cat_id,obj_id,color,type,title) ".
			"VALUES ( ".
			$ilDB->quote($calcat_id, 'integer').", ".
			$ilDB->quote(0, 'integer').", ".
			$ilDB->quote('#000000', 'text').", ".
			$ilDB->quote(3, 'integer').", ".  // global
			$ilDB->quote('TEP', 'text')." ".
			")";
		$ilDB->manipulate($query);

		$crs_set->set("crs_calendar", $calcat_id);
	}
}
	
// derived calender entries
if (!$ilDB->tableExists('cal_derived_entry'))
{
	$fields = array (
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),		    
		'master_cal_entry' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),		
		'cat_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0)
	);
	$ilDB->createTable('cal_derived_entry', $fields);
	$ilDB->addPrimaryKey('cal_derived_entry', array('id'));
	$ilDB->createSequence('cal_derived_entry');		
}
	
// calendar entry type
if(!$ilDB->tableColumnExists('cal_entries', 'entry_type'))
{
	$ilDB->addTableColumn('cal_entries', 'entry_type', 
		array(
			'type' => 'text', 
			'length' => 100, 
			'notnull' => false, 
			'default' => ''
	));			
}

// calendar entry types
if (!$ilDB->tableExists('tep_type'))
{
	$fields = array (
		'id' => array(
			'type' => 'text',
			'length' => 100,
			'notnull' => true,
			'default' => ''),		    
		'title' => array(
			'type' => 'text',
			'length' => 1000,
			'notnull' => true),		
		'bg_color' => array(
			'type' => 'text',
			'length' => 6,
			'fixed' => true,
			'notnull' => false),
		'font_color' => array(
			'type' => 'text',
			'length' => 6,
			'fixed' => true,
			'notnull' => false),
		'tep_active' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 1)
	);
	$ilDB->createTable('tep_type', $fields);
	$ilDB->addPrimaryKey('tep_type', array('id'));	
}

if(!$ilDB->tableExists('tep_op_days'))
{
	$fields = array (
		'obj_type' => array(
			'type' => 'text',
			'length' => 200,
			'notnull' => true,
			'default' => ''),		    				
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'miss_day' => array(
			'type' => 'date',
			'notnull' => true)					
	);
	$ilDB->createTable('tep_op_days', $fields);
}	


//
// create RBAC permissions (incl. org unit?)
//

$new_org_ops = array(
	'tep_is_tutor' => array('TEP Tutor', 2904)
	,'tep_view_other' => array('TEP View Other', 2905)
	,'tep_view_other_rcrsv' => array('TEP View Other (recursive)', 2906)
	,'tep_edit_other' => array('TEP Edit Other', 3505)
	,'tep_edit_other_rcrsv' => array('TEP Edit Other (recursive)', 3506)
);

ilCustomInstaller::addRBACOps('orgu', $new_org_ops);

//
// lng variables
//

$lang_data = array(
	"personal_calendar_title" => array("TEP", "TEP")
	,"page_title" => array("TEP", "TEP")
	,"tab_list" => array("Terminliste", "List Of Dates")
	,"tab_month" => array("Monatsansicht", "Monthly")
	,"tab_halfyear" => array("Halbjahresansicht", "Half-Year")
	,"add_new_entry" => array("Neuen Termin anlegen", "Add new entry")
	,"export" => array("XLS-Export", "XLS-Export")
	,"print" => array("Drucken", "Print")
	,"create_entry" => array("Eintrag anlegen", "Create Entry")
	,"entry_owner" => array("Termin für", "Entry of")
	,"entry_derived" => array("Weitere Teilnehmer", "Other Participants")
	,"entry_title" => array("Termintitel", "Title of Entry")
	,"entry_type" => array("Typ", "Type")
	,"entry_period" => array("Zeitraum", "Period")
	,"entry_location" => array("Ort", "Location")
	,"entry_created" => array("Der Eintrag wurde erstellt.", "The entry has been created.")
	,"entry_updated" => array("Der Eintrag wurde aktualisiert.", "The entry has been updated.")
	,"legend" => array("Legende", "Legend")
	,"search_all" => array("--Alle--", "--All--")
	,"filter_submit" => array("Filtern", "Filter")
	,"filter_no_tutor" => array("Seminare ohne Trainer anzeigen?", "Show Seminars without Trainers?")
	,"column_no_tutor" => array("Seminare ohne Trainer", "Seminars without Trainers")
	,"update_entry" => array("Termin bearbeiten", "Edit Entry")
	,"list_view_title" => array("Terminliste", "List Of Dates")
	,"list_view_info" => array("Es werden Termine der nächsten 4 Wochen angezeigt.", "Only entries in the next 4 weeks are shown.")
	,"entry_delete_sure" => array("Wollen Sie wirklich den Termin löschen?", "Are you sure you want to delete the entry?")
	,"delete_entry" => array("Termin löschen", "Delete Entry")
	,"entry_deleted" => array("Termin wurde gelöscht.", "Entry has been deleted.")
	,"op_tab_list_operation_days" => array("Einsatztage", "Operation Days")
	,"edit_user_operation_days" => array("Einsatztage bearbeiten", "Edit Operation Days")
	,"edit_operation_days" => array("Einsatztage", "Operation Days")
	,"filter_orgu_all" => array("--Alle Tutoren--", "--All Trainers--")
	,"filter_orgu_rcrsv" => array("rekursiv?", "recursive?")
	,"filter_tutor" => array("Tutor", "Trainer")
	,"filter_tutor_empty" => array("Die Auswahl der Organisationseinheiten enthält keine Tutoren.", "The selected organisational units did not yield any trainers.")
);

ilCustomInstaller::addLangData("tep", array("de", "en"), $lang_data, "patch generali - TEP");

?>

<#26>
<?php

$stmt = $ilDB->prepareManip("INSERT INTO tep_type (id, title, bg_color, font_color, tep_active) VALUES (?, ?, ?, ?, ?) "
						   , array("integer", "text", "text", "text", "integer"));
$data = array( array(1, "Training", "f6000d", "000000", "0")
			 , array(2, "Veranstaltung (LD)", "f76809", "000000", "1")
			 , array(3, "Projekt/Sondermaßnahme", "b90007", "000000", "1")
			 , array(4, "Trainingsvorbereitung", "a2ff2c", "000000", "1")
			 , array(5, "Büro", "49ff00", "000000", "1")
			 , array(6, "Bereichs-/Abteilungsmeeting", "1900ff", "ffffff", "1")
			 , array(7, "Besprechung", "4effff", "000000", "1")
			 , array(8, "Besuch Infoveranstaltung", "feff98", "000000", "1")
			 , array(9, "Weiterbildung", "feff00", "000000", "1")
			 , array(10, "Urlaub/Gleittag", "e0e0e0", "000000", "1")
			 , array(11, "Feiertag im Bundesland", "c0c0c0", "000000", "1")
			 );
$ilDB->executeMultiple($stmt, $data);
$ilDB->free($stmt);
?>

<#27>
<?php

if(!$ilDB->tableExists('bill'))
{
	$fields = array(
		'bill_pk'               => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'bill_number'           => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_recipient_name'   => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_recipient_street' => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_recipient_hnr'    => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_recipient_zip'    => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_recipient_city'   => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_recipient_cntry'  => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_date'             => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'bill_title'            => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => true
		),
		'bill_description'      => array(
			'type'    => 'text',
			'length'  => 4000,
			'notnull' => false,
			'default' => null
		),
		'bill_vat'              => array(
			'type'    => 'float',
			'notnull' => true,
			'default' => 0
		),
		'bill_cost_center'      => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_currency'         => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => true
		),
		'bill_usr_id'           => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'bill_year'             => array(
			'type'    => 'integer',
			'length'  => 2,
			'notnull' => true,
			'default' => 0
		),
		'bill_final'            => array(
			'type'    => 'integer',
			'length'  => 1,
			'notnull' => true,
			'default' => 0
		),
		'bill_context_id'       => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => false,
			'default' => null
		),
		"bill_recipient_email" => array(
			"type" 	=> "text",
			"length"	=> 255,
			"notnull"	=> false,
			"default" => null
		)
	);

	$ilDB->createTable('bill', $fields);
	$ilDB->addPrimaryKey('bill', array('bill_pk'));
	$ilDB->createSequence('bill');
	
	$ilDB->query('ALTER TABLE bill ENGINE=INNODB');
	$ilDB->query('ALTER TABLE bill_seq ENGINE=INNODB');
}

?>

<#28>
<?php

// Tracking Infos about cron jobs for mail

$fields = array (
    'crs_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'title' => array(
        "type" => "text",
        "length" => 64,
        "notnull" => true
        ),
    'send_at' => array(
        'type' => 'timestamp',
        'notnull' => false
        )
);

$ilDB->createTable('gev_crs_dl_mail_cron', $fields);
$ilDB->addPrimaryKey('gev_crs_dl_mail_cron', array('crs_id', 'title'));

?>


<#29>
<?php

// Tracking Infos about cron jobs for mail

$fields = array (
    'crs_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'mail_id' => array(
        "type" => "text",
        "length" => 64,
        "notnull" => true
        ),
    'recipient' => array(
        'type' => 'text',
        "length" => 128,
        'notnull' => false
        )
    , "occasion" => array(
        'type' => 'text',
        "length" => 256,
        'notnull' => true
    )
);

$ilDB->createTable('gev_crs_deferred_mails', $fields);
$ilDB->addPrimaryKey('gev_crs_deferred_mails', array('crs_id', 'mail_id', 'recipient'));

?>

<#30>
<?php

// Tracking relation between coupons and bills they were created for.

$fields = array (
	'bill_pk' => array(
		'type'    => 'integer',
		'length'  => 4,
		'notnull' => true,
		'default' => 0
	),
	'coupon_code' => array(
		'type'    => 'text',
		'length'  => 255,
		'notnull' => true
	)
);

$ilDB->createTable('gev_bill_coupon', $fields);
$ilDB->addPrimaryKey('gev_bill_coupon', array('bill_pk'));

?>

<#31>
<?php

$ilDB->manipulate("UPDATE settings SET value = 1800 WHERE keyword = 'reg_hash_life_time'");

?>

<#32>
<?php
if(!$ilDB->tableExists('billitem'))
{
	$fields = array(
		'billitem_pk'          => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'bill_fk'          => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'billitem_title'       => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => true
		),
		'billitem_description' => array(
			'type'    => 'text',
			'length'  => 4000,
			'notnull' => false,
			'default' => null
		),
		'billitem_pta'         => array(
			'type'    => 'float',
			'notnull' => true,
			'default' => 0
		),
		'billitem_vat'         => array(
			'type'    => 'float',
			'notnull' => true,
			'default' => 0
		),
		'billitem_currency'    => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => true
		),
		'billitem_context_id'  => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => false,
			'default' => null
		),
		'billitem_final'       => array(
			'type'    => 'integer',
			'length'  => 1,
			'notnull' => true,
			'default' => 0
		)
	);

	$ilDB->createTable('billitem', $fields);
	$ilDB->addPrimaryKey('billitem', array('billitem_pk'));
	$ilDB->createSequence('billitem');
	
	$ilDB->query('ALTER TABLE bill_seq ENGINE=INNODB');
	$ilDB->query('ALTER TABLE billitem ENGINE=INNODB');
	$ilDB->query('ALTER TABLE billitem_seq ENGINE=INNODB');
}
?>

<#33>
<?php
if(!$ilDB->tableExists('coupon'))
{
	$fields = array(
		'coupon_pk'          => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'coupon_code'        => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => true
		),
		'coupon_value'       => array(
			'type'    => 'float',
			'notnull' => true,
			'default' => 0
		),
		'coupon_last_change' => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'coupon_expires'     => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'coupon_usr_id'      => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'coupon_active'      => array(
			'type'    => 'integer',
			'length'  => 1,
			'notnull' => true,
			'default' => 0
		),
		'coupon_created'     => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		)
	);

	$ilDB->createTable('coupon', $fields);
	$ilDB->addPrimaryKey('coupon', array('coupon_pk'));
	$ilDB->createSequence('coupon');
	
	$ilDB->query('ALTER TABLE coupon ENGINE=INNODB');
	$ilDB->query('ALTER TABLE coupon_seq ENGINE=INNODB');
}
?>

<#34>
<?php

// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

$lang_data = array(
	"numberlabel" => array("Rechnungsnummer", "Bill Number"),
	"vat"         => array("Umsatzsteuer", "Sales Tax"),
	"net"         => array("(netto)", "(after tax)"),
	"bru"         => array("(brutto)", "(pre-tax)"),
	"val"         => array("Rechnungsbetrag", "Amount")
);

ilCustomInstaller::addLangData("billing", array("de", "en"), $lang_data, "patch generali - Billing");
?>

<#35>
<?php
if(!$ilDB->tableExists('hist_usercoursestatus'))
{
	$fields = array(
		'row_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'hist_version' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 1),
		'hist_historic' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'creator_user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'created_ts' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'crs_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'credit_points' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'bill_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'booking_status' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'participation_status' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'okz' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false),
		'org_unit' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false),
		'certificate' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false),
		'begin_date' => array(
			'type' => 'date'),
		'end_date' => array(
			'type' => 'date'),
		'overnights' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false),
		'function' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false)
	);
	$ilDB->createTable('hist_usercoursestatus', $fields);
	$ilDB->addPrimaryKey('hist_usercoursestatus', array('row_id'));
	$ilDB->createSequence('hist_usercoursestatus');
}
?>

<#36>
<?php

if(!$ilDB->tableExists('hist_certfile'))
{
	$fields = array (
		'row_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'certfile' => array(
			'type' => 'clob',
			'notnull' => true)
	);
	$ilDB->createTable('hist_certfile', $fields);
	$ilDB->addPrimaryKey('hist_certfile', array('row_id'));
	$ilDB->createSequence('hist_certfile');
}
?>

<#37>
<?php
if(!$ilDB->tableExists('hist_course'))
{
	$fields = array (
		'row_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'hist_version' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 1),
		'hist_historic' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'creator_user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'created_ts' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'crs_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'custom_id' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'template_title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'type' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'topic_set' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'begin_date' => array(
			'type' => 'date'),
		'end_date' => array(
			'type' => 'date'),
		'hours' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false,
			'default' => 0),
		'is_expert_course' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0),
		'venue' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false),
		'provider' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false),
		'tutor' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false),
		'max_credit_points' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false),
		'fee' => array(
			'type' => 'float',
			'notnull' => false),
		"is_template" =>  array(
			"type" 	=> "text", 
			"length" 	=> 8,
			"notnull" => false)
	);
	$ilDB->createTable('hist_course', $fields);
	$ilDB->addPrimaryKey('hist_course', array('row_id'));
	$ilDB->createSequence('hist_course');
}
?>

<#38>
<?php
if(!$ilDB->tableExists('hist_topicset2topic'))
{
	$fields = array (
		'row_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'topic_set_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,),
		'topic_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,),
	);
	$ilDB->createTable('hist_topicset2topic', $fields);
	$ilDB->addPrimaryKey('hist_topicset2topic', array('row_id'));
	$ilDB->createSequence('hist_topicset2topic');
}
?>

<#39>
<?php
if(!$ilDB->tableExists('hist_topics'))
{
	$fields = array (
		'row_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'topic_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,),
		'topic_title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
	);
	$ilDB->createTable('hist_topics', $fields);
	$ilDB->addPrimaryKey('hist_topics', array('row_id'));
	$ilDB->createSequence('hist_topics');
}
?>

<#40>
<?php
if(!$ilDB->tableExists('hist_user'))
{
	$fields = array (
		'row_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'hist_version' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 1),
		'hist_historic' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'creator_user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'created_ts' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'firstname' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'lastname' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'gender' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'birthday' => array(
			'type' => 'date'),
		'org_unit' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'position_key' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'entry_date' => array(
			'type' => 'date'),
		'exit_date' => array(
			'type' => 'date'),
		'bwv_id' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'okz' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'begin_of_certification' => array(
			'type' => 'date'),
		'deleted' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true)
	);
	$ilDB->createTable('hist_user', $fields);
	$ilDB->addPrimaryKey('hist_user', array('row_id'));
	$ilDB->createSequence('hist_user');
}
?>

<#41>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#42>
<?php
// init helper class
$ilDB->	manipulate("ALTER TABLE `il_plugin` CHANGE `plugin_id` `plugin_id` 
			VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL");

require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_MODULE, "OrgUnit", "orgutypehk", "GEVOrgTypes");

?>

<#43>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "AdvancedMetaData", "amdc", "CourseAMD");

?>

<#44>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "AdvancedMetaData", "amdc", "OrgUnitAMD");

?>

<#45>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "EventHandling", "evhk", "GEVBilling");

?>

<#46>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "EventHandling", "evhk", "GEVCourseCreation");

?>

<#47>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "EventHandling", "evhk", "GEVCourseTemplateCreation");

?>

<#48>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "EventHandling", "evhk", "GEVCourseUpdate");

?>

<#49>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "EventHandling", "evhk", "GEVMailing");

?>

<#50>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "EventHandling", "evhk", "GEVOrgUnitUpdate");

?>

<#51>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "EventHandling", "evhk", "GEVWaitingList");

?>

<#52>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "User", "udfc", "GEVUserData");
?>


<#53>
<?php
// update on #40, missing fields for wbd
$txt_fields_hist_user = array(
	'street',
	'zipcode',
	'city',
	'phone_nr',
	'mobile_phone_nr',
	'email',
);
foreach ($txt_fields_hist_user as $field) {
	if(!$ilDB->tableColumnExists('hist_user', $field)){
		$ilDB->addTableColumn('hist_user', $field, array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
			)
		);	
	}
}
?>

<#54>
<?php
// marker for wbd-reports
if(!$ilDB->tableColumnExists('hist_user', 'last_wbd_report')){
	$ilDB->manipulate("ALTER TABLE `hist_user` ADD `last_wbd_report` DATE NULL DEFAULT NULL AFTER `created_ts`");
}
if(!$ilDB->tableColumnExists('hist_usercoursestatus', 'last_wbd_report')){
	$ilDB->manipulate("ALTER TABLE `hist_usercoursestatus` ADD `last_wbd_report` DATE NULL DEFAULT NULL AFTER `created_ts`");
}

	

?>


<#55>
<?php
// update on #40,#53; missing fields for wbd
$txt_fields_hist_user = array(
	'agent_status', 
	'wbd_type', //USR_TP_TYPE

);
foreach ($txt_fields_hist_user as $field) {
	if(!$ilDB->tableColumnExists('hist_user', $field)){
		$ilDB->addTableColumn('hist_user', $field, array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
			)
		);	
	}
}
?>
<#56>
<?php
// update on #40,#53, #55; missing fields for wbd
$txt_fields_hist_user = array(
	'wbd_email', //USR_UDF_PRIV_EMAIL
);
foreach ($txt_fields_hist_user as $field) {
	if(!$ilDB->tableColumnExists('hist_user', $field)){
		$ilDB->addTableColumn('hist_user', $field, array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
			)
		);	
	}
}
?>

<#57>
<?php
// missing fields for wbd, hist_course
$txt_fields_hist_course = array(
	'wbd_topic' //CRS_AMD_GDV_TOPIC, study-contents
);
foreach ($txt_fields_hist_course as $field) {
	if(!$ilDB->tableColumnExists('hist_course', $field)){
		$ilDB->addTableColumn('hist_course', $field, array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
			)
		);	
	}
}
?>
<#58>
<?php
// missing fields for wbd, hist_usercoursestatus
$txt_fields_hist_course = array(
	'wbd_booking_id' 
);
foreach ($txt_fields_hist_course as $field) {
	if(!$ilDB->tableColumnExists('hist_usercoursestatus', $field)){
		$ilDB->addTableColumn('hist_usercoursestatus', $field, array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
			)
		);	
	}
}
?>
<#59>
<?php

if(!$ilDB->tableColumnExists("bill", "bill_finalized_date")) {
	$ilDB->addTableColumn("bill", "bill_finalized_date", array(
		  "type" => "integer"
		, "length" => 4
		, "notnull" => false
		));
}

$ilDB->manipulate("UPDATE bill SET bill_finalized_date = UNIX_TIMESTAMP() WHERE bill_final = 1");

?>





<#60>
<?php
// TEP categories

$query = "DELETE FROM tep_type WHERE 1";
$ilDB->manipulate($query);

$stmt = $ilDB->prepareManip("INSERT INTO tep_type (id, title, bg_color, font_color, tep_active) VALUES (?, ?, ?, ?, ?) "
						   , array("integer", "text", "text", "text", "integer"));

$data = array(
	  array(1,  "Projekt", 					"f09273", "000000", "1")
	, array(2,  "Ausgleichstag",			"86b37d", "000000", "1")
	, array(3,  "Krankheit",				"86b37d", "000000", "1")
	, array(4,  "Urlaub genehmigt",			"86b37d", "000000", "1")
	, array(5,  "FD Gespräch",				"e6da9d", "000000", "1")
	, array(6,  "FD-MA Teammeeting",		"e6da9d", "000000", "1")
	, array(7,  "RD-Gespräch",				"e6da9d", "000000", "1")
	, array(8,  "OD-FD Meeting",			"e6da9d", "000000", "1")
	, array(9,  "AKL-Gespräch",				"e6da9d", "000000", "1")
	, array(10, "bAV-Arbeitskreis",			"e6da9d", "000000", "1")
	, array(11, "Gewerbe-Arbeitskreis",		"e6da9d", "000000", "1")
	, array(12, "FDL-Arbeitskreis",			"e6da9d", "000000", "1")
	, array(13, "Firmenkunden",				"cccccc", "000000", "1")
	, array(14, "Aquise Pilotprojekt", 		"cccccc", "000000", "1")
	, array(15, "Individuelle Unterstützung SpV/FD",	"cccccc", "000000", "1")
	, array(16, "AD Begleitung",			"cccccc", "000000", "1")
	, array(17, "Urlaub beantragt",			"b8ce8d", "000000", "1")
	, array(18, "Trainer- / DBV Klausur (Zentral)", "bf6364", "000000", "1")
	, array(19, "Trainer Teammeeting",		"bf6364", "000000", "1")
	, array(20, "Arbeitsgespräch", 			"bf6364", "000000", "1")
	, array(21, "Veranstaltung / Tagung (Zentral)",	"f09273", "000000", "1")
	, array(22, "Büro", 					"b8ace6",  "000000", "1")
	, array(23, "Dezentraler Feiertag", 	"b8ce8d", "000000", "1")
	, array(24, "Training", 				"f0e960" , "000000", "1")
	//, array(24, "Gibt es noch nicht", 		"bf6364" , "000000", "1")
);

$ilDB->executeMultiple($stmt, $data);
$ilDB->free($stmt);
?>


<#61>
<?php
// update on #719, new fields 
$txt_fields_hist_user = array(
	'job_number',
	'adp_number',
	'position_key',
	'org_unit_above1',
	'org_unit_above2'
);
foreach ($txt_fields_hist_user as $field) {
	if(!$ilDB->tableColumnExists('hist_user', $field)){
		$ilDB->addTableColumn('hist_user', $field, array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
			)
		);	
	}
}
?>


<#62>
<?php
// change fieldname

$query = "ALTER TABLE hist_user 
	CHANGE agent_status wbd_agent_status 
	VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL 
	DEFAULT NULL;
";

$ilDB->manipulate($query);
?>

<#63>
<?php
// change fieldname

$query = "ALTER TABLE hist_usercoursestatus 
	CHANGE bill_id bill_id 
	VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL 
	DEFAULT NULL;
";

$ilDB->manipulate($query);

/**
* migrate hist_usercoursestatus.bill_id to hold 
*/

$query = "SELECT 
			hist_usercoursestatus.row_id, 
			hist_usercoursestatus.bill_id,
			bill.bill_number 
		FROM hist_usercoursestatus
		INNER JOIN bill ON 
			hist_usercoursestatus.bill_id = bill.bill_pk
		WHERE 
			hist_usercoursestatus.bill_id > 0
			AND 
			hist_usercoursestatus.hist_historic = 0
			AND 
			bill.bill_number IS NOT NULL;
		";


$res = $ilDB->query($query);
while($rec = $ilDB->fetchAssoc($res)) {
	$bill_id = $rec['bill_number'];
	$row_id = $rec['row_id'];
	$sql = "UPDATE hist_usercoursestatus SET bill_id = '$bill_id' WHERE row_id=$row_id";
	$ilDB->manipulate($sql);
}
?>

<#64>
<?php
// missing fields, hist_course
$txt_fields_hist_course = array(
	'edu_program' //CRS_AMD_EDU_PROGRAMM
);
foreach ($txt_fields_hist_course as $field) {
	if(!$ilDB->tableColumnExists('hist_course', $field)){
		$ilDB->addTableColumn('hist_course', $field, array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
			)
		);	
	}
}
?>

<#65>
<?php
if(!$ilDB->tableExists('org_unit_personal'))
{
	$fields = array (
    'orgunit_id'    => array(
    		'type' => 'integer',
    		'length'  => 4,
    		'notnull' => true,
    		'default' => 0),

  'usr_id'    => array(
    		'type' => 'integer',
    		'length'  => 4,
    		'notnull' => true,
    		'default' => 0),

  
  );
  $ilDB->createTable('org_unit_personal', $fields);
  $ilDB->addPrimaryKey('org_unit_personal', array('orgunit_id'));
}
?>

<#66>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#67>
<?php
	if(!$ilDB->tableColumnExists('gev_crs_addset', "suppress_mails")){
		$ilDB->addTableColumn('gev_crs_addset', "suppress_mails", array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
			)
		);	
	}
?>

<#68>
<?php
	if(!$ilDB->tableColumnExists('hist_user', "is_vfs")){
		$ilDB->addTableColumn('hist_user', "is_vfs", array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
			)
		);	
	}

?>

<#69>
<?php

if(!$ilDB->tableExists('copy_mappings'))
{
	$fields = array (
	'target_ref_id'    => array(
			'type' => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0),
	
	'source_ref_id'    => array(
			'type' => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0),
	);

	$ilDB->createTable('copy_mappings', $fields);
	$ilDB->addPrimaryKey('copy_mappings', array('target_ref_id'));
}

?>

<#70>
<?php

if(!$ilDB->tableExists('hist_tep'))
{
	$fields = array (
		'row_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'hist_version' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 1),
		'hist_historic' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'creator_user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'created_ts' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'cal_entry_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'cal_derived_entry_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false),
		'context_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'subtitle' => array(
			'type' => 'text',
			'length' => 255),
		'description' => array(
			'type' => 'text',
			'length' => 255),
		'location' => array(
			'type' => 'text',
			'length' => 255),
		'fullday' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true),
		'begin_date' => array(
			'type' => 'date'),
		'end_date' => array(
			'type' => 'date'),
		'individual_days' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'category' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'deleted' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true)
	);
	$ilDB->createTable('hist_tep', $fields);
	$ilDB->addPrimaryKey('hist_tep', array('row_id'));
	$ilDB->createSequence('hist_tep');
}

?>

<#71>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#72>
<?php
	require_once "Customizing/class.ilCustomInstaller.php";

	$new_org_ops = array(
		 'add_dec_training_self' => array('Create decentral Trainings for self', 2907)
		,'add_dec_training_others' => array('Create decentral Trainings for others', 2908)
		,'add_dec_training_others_rec' => array('Create decentral Trainings for others (recursiv)', 2909)
	);
	ilCustomInstaller::addRBACOps('orgu', $new_org_ops);
?>

<#73>
<?php
	$query = "UPDATE  tep_type SET title = 'Akquise Pilotprojekt' WHERE id=14";
	$ilDB->manipulate($query);
?>

<#74>
<?php
	require_once "Customizing/class.ilCustomInstaller.php";

	$new_crs_ops = array(
		 'write_reduced_settings' => array('Edit reduced settings of training.', 6001)
	);
	ilCustomInstaller::addRBACOps('crs', $new_crs_ops);
?>

<#75>
<?php
	// create instance
	include_once("Services/AccessControl/classes/class.ilObjRoleTemplate.php");
	require_once "Customizing/class.ilCustomInstaller.php";
	
	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitUserToRoot();
	
	$newObj = new ilObjRoleTemplate();
	$newObj->setType("rolt");
	$newObj->setTitle("Pool Trainingsersteller");
	$newObj->setDescription("Rolle für die Ersteller von dezentralen Trainings");
	$newObj->create();
	$newObj->createReference();
	$newObj->putInTree(ROLE_FOLDER_ID);
	$newObj->setPermissions(ROLE_FOLDER_ID);

	$ilDB->manipulate("INSERT INTO rbac_fa (rol_id, parent, assign, protected)"
					 ." VALUES ( ".$ilDB->quote($newObj->getId(), "integer")
					 ."        , ".$ilDB->quote(ROLE_FOLDER_ID, "integer")
					 ."        , 'n', 'y')"
					 );
?>

<#76>
<?php
	require_once("Services/GEV/Utils/classes/class.gevSettings.php");

	$res = $ilDB->query("SELECT record_id FROM adv_md_record"
					   ." WHERE title = 'Verwaltung'");
	$rec = $ilDB->fetchAssoc($res);
	$record_id = $rec["record_id"];
	
	$res = $ilDB->query("SELECT field_id FROM adv_mdf_definition"
					   ." WHERE title = 'Bildungsprogramm'");
	$rec = $ilDB->fetchAssoc($res);
	$field_id = $rec["field_id"];
	
	$ilDB->manipulate("UPDATE adv_mdf_definition "
					 ."   SET record_id = ".$ilDB->quote($record_id, "integer")
					 ." WHERE title = 'Bildungsprogramm'"
					 );
	$ilDB->manipulate("UPDATE settings "
					 ."   SET value = ".$this->db->quote($record_id." ".$field_id, "text")
					 ." WHERE keyword = ".$this->db->quote(gevSettings::CRS_AMD_EDU_PROGRAMM, "text")
					 );

	$pos = array( 1 => "Trainingsnummer"
				, 2 => "Trainingtyp"
				, 3 => "Bildungsprogramm"
				, 4 => "Vorlage"
				, 5 => "Vorlagentitel"
				, 6 => "Referenz-Id der Vorlage"
				, 7 => "Nummernkreis"
				);
	
	foreach ($pos as $key => $value) {
		$ilDB->manipulate("UPDATE adv_mdf_definition"
						 ."   SET position = ".$ilDB->quote($key, "integer")
						 ." WHERE title = ".$ilDB->quote($value, "text")
						 );
	}
?>

<#77>
<?php

	$ilDB->manipulate("UPDATE adv_mdf_definition SET field_values = '".serialize(array(
			  "Fachwissen"
			, "SUHK - Privatkunden"
			, "SUHK - Firmenkunden"
			, "Leben und Rente"
			, "Betriebliche Altersvorsorge"
			, "Kooperationspartner"
			, "Vertrieb"
			, "Akquise / Verkauf"
			, "Beratungs- und Tarifierungstools"
			, "Büromanagement"
			, "Neue Medien"
			, "Unternehmensführung"
			, "Agenturmanagement"
			, "Führung"
			, "Persönlichkeit"
			, "Grundausbildung"
			, "Ausbilder"
			, "Erstausbildung"
			, "Qualifizierungsprogramm"
		))."' WHERE title = 'Trainingskategorie'");
?>

<#78>
<?php

	$ilDB->manipulate("UPDATE cat_mail_templates SET template_type = 'Agentregistration'"
					 ." WHERE template_type = 'Registration'");
	$res = $ilDB->query("SELECT id FROM cat_mail_templates WHERE category_name = 'EVG_Aktivierung'");
	if ($rec = $ilDB->fetchAssoc($res)) {
		$ilDB->manipulate("DELETE FROM cat_mail_variants WHERE mail_types_fi = ".$ilDB->quote($rec["id"], "integer"));
	}
	$ilDB->manipulate("DELETE FROM cat_mail_templates WHERE category_name = 'EVG_Aktivierung'");
	$ilDB->manipulate("UPDATE cat_mail_templates SET category_name = 'Confirmation'"
					 ." WHERE category_name = 'Makler_Aktivierung'");
?>

<#79>
<?php
	require_once "Customizing/class.ilCustomInstaller.php";
	ilCustomInstaller::maybeInitClientIni();
	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitObjDataCache();
	ilCustomInstaller::maybeInitUserToRoot();

	ini_set('max_execution_time', 0);
	set_time_limit(0);

	require_once("Services/GEV/Import/classes/class.gevImportOrgStructure.php");
	$imp = new gevImportOrgStructure();
	$imp->createOrgUnits();

	require_once("Services/GEV/Utils/classes/class.gevSettings.php");
	$gev_settings = gevSettings::getInstance();
	$res = $ilDB->query("SELECT obj_id FROM object_data WHERE import_id = 'uvg' AND type = 'orgu'");
	if ($rec = $ilDB->fetchAssoc($res)) {
		$gev_settings->setDBVPOUBaseUnitId($rec["obj_id"]);
	}
	else {
		die("Custom Update #79: Expected to find org_unit with import_id = 'uvg'");
	}
	
	$res = $ilDB->query("SELECT obj_id FROM object_data WHERE import_id = 'cpool' AND type = 'orgu'");
	if ($rec = $ilDB->fetchAssoc($res)) {
		$gev_settings->setCPoolUnitId($rec["obj_id"]);
	}
	else {
		die("Custom Update #79: Expected to find org_unit with import_id = 'cpool'");
	}
	
	$res = $ilDB->query("SELECT obj_id FROM object_data WHERE import_id = 'dbv_tmplt' AND type = 'orgu'");
	if ($rec = $ilDB->fetchAssoc($res)) {
		$gev_settings->setDBVPOUTemplateUnitId($rec["obj_id"]);
	}
	else {
		die("Custom Update #79: Expected to find org_unit with import_id = 'dbv_tmplt'");
	}
?>

<#80>
<?php
	require_once "Customizing/class.ilCustomInstaller.php";
	ilCustomInstaller::maybeInitClientIni();
	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitObjDataCache();
	ilCustomInstaller::maybeInitUserToRoot();
	
	require_once("Services/GEV/Import/classes/class.gevImportOrgStructure.php");
	$imp = new gevImportOrgStructure();
	$imp->createOrgUnit("na_tmplt", "Vorlage NA-Einheit", "na", "", "", "", "", "", "", "");

	require_once("Services/GEV/Utils/classes/class.gevSettings.php");
	$gev_settings = gevSettings::getInstance();
	$res = $ilDB->query("SELECT obj_id FROM object_data WHERE import_id = 'na' AND type = 'orgu'");
	if ($rec = $ilDB->fetchAssoc($res)) {
		$gev_settings->setNAPOUBaseUnitId($rec["obj_id"]);
	}
	else {
		die("Custom Update #79: Expected to find org_unit with import_id = 'na'");
	}
	
	$res = $ilDB->query("SELECT obj_id FROM object_data WHERE import_id = 'na_tmplt' AND type = 'orgu'");
	if ($rec = $ilDB->fetchAssoc($res)) {
		$gev_settings->setNAPOUTemplateUnitId($rec["obj_id"]);
	}
	else {
		die("Custom Update #79: Expected to find org_unit with import_id = 'na_tmplt'");
	}
?>

<#81>
<?php
	require_once "Customizing/class.ilCustomInstaller.php";
	ilCustomInstaller::maybeInitClientIni();
	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitObjDataCache();
	ilCustomInstaller::maybeInitUserToRoot();

	require_once "Services/GEV/Utils/classes/class.gevOrgUnitUtils.php";
	
	$res = $ilDB->query("SELECT DISTINCT oref.ref_id "
						."  FROM object_data od "
						."  JOIN object_reference oref ON oref.obj_id = od.obj_id "
						." WHERE ".$ilDB->in("import_id", array("evg", "uvg", "na"), false, "text")
						."   AND oref.deleted IS NULL"
						."   AND od.type = 'orgu'"
						);
	
	while($rec = $ilDB->fetchAssoc($res)) {
		gevOrgUnitUtils::grantPermissionsRecursivelyFor($rec["ref_id"], "superior",
				array( "view_employee_bookings"
					 , "view_employee_bookings_rcrsv"
					 , "book_employees"
					 , "book_employees_rcrsv"
					 , "cancel_employee_bookings"
					 , "cancel_employee_bookings_rcrsv"
					 ));
	}
?>

<#82>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#83>
<?php
	if(!$ilDB->tableExists('gev_na_tokens'))
	{
		$fields = array (
			'user_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true),
			'adviser_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true),
			'token' => array(
				'type' => 'text',
				'length' => 32,
				'notnull' => true)
		);
		$ilDB->createTable('gev_na_tokens', $fields);
		$ilDB->addPrimaryKey('gev_na_tokens', array('user_id'));
	}
?>


<#84>
<?php

$ilDB->manipulate("UPDATE tep_type SET title = 'AD-Begleitung' WHERE title = 'AD Begleitung'");
$ilDB->manipulate("UPDATE tep_type SET title = 'FD-Gespräch' WHERE title = 'FD Gespräch'");

?>

<#85>
<?php
	require_once "Customizing/class.ilCustomInstaller.php";
	ilCustomInstaller::maybeInitClientIni();
	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitObjDataCache();
	ilCustomInstaller::maybeInitUserToRoot();

	require_once "Services/GEV/Utils/classes/class.gevOrgUnitUtils.php";
	
	$res = $ilDB->query("SELECT DISTINCT oref.ref_id "
						."  FROM object_data od "
						."  JOIN object_reference oref ON oref.obj_id = od.obj_id "
						." WHERE ".$ilDB->in("import_id", array("gev_base"), false, "text")
						."   AND oref.deleted IS NULL"
						."   AND od.type = 'orgu'"
						);
	
	while($rec = $ilDB->fetchAssoc($res)) {
		gevOrgUnitUtils::grantPermissionsRecursivelyFor($rec["ref_id"], "superior",
				array( "view_employee_bookings"
					 , "view_employee_bookings_rcrsv"
					 , "book_employees"
					 , "book_employees_rcrsv"
					 , "cancel_employee_bookings"
					 , "cancel_employee_bookings_rcrsv"
					 ));
	}
?>

<#86>
<?php
	//set indizes for the history table - wow, such performance!
	$queries =  array(
		 "ALTER TABLE hist_course ADD INDEX hist_historic (hist_historic);"
		,"ALTER TABLE hist_course ADD INDEX crs_id (crs_id);"

		,"ALTER TABLE hist_user ADD INDEX hist_historic (hist_historic);"
		,"ALTER TABLE hist_user ADD INDEX user_id (user_id);"
		
		,"ALTER TABLE hist_usercoursestatus ADD INDEX hist_historic (hist_historic);"
		,"ALTER TABLE hist_usercoursestatus ADD INDEX crs_id (crs_id);"
		,"ALTER TABLE hist_usercoursestatus ADD INDEX usr_id (usr_id);"

	);
	foreach ($queries as $query) {
		try{
			$ilDB->manipulate($query);
		} catch(Exception $e){
			//pass
		}
	}


?>

<#87>
<?php
	require_once "Customizing/class.ilCustomInstaller.php";

	if(!$ilDB->tableExists('crs_matlist'))
	{
		$fields = array (
			'id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0),
			'obj_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0),
			'quant_per_part' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0),
			'quant_per_crs' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0),
			'mat_number' => array(
				'type' => 'text',
				'length' => 80,
				'notnull' => false),
			'description' => array(
				'type' => 'text',
				'length' => 200,
				'notnull' => false),
			'changed_by' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0),
			'changed_on' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0),
		);
		$ilDB->createTable('crs_matlist', $fields);
		$ilDB->addPrimaryKey('crs_matlist', array('id'));
		$ilDB->createSequence('crs_matlist');
	}


	$new_crs_ops = array(
		'view_material' => array('View Material', 2910)
		,'edit_material' => array('Edit Material', 3510)
	);
	ilCustomInstaller::addRBACOps('crs', $new_crs_ops);


	$lang_data = array(
		"tab" => array("Material", "Material")
		,"add" => array("Material hinzufügen", "Add Material")
		,"download" => array("Materialliste herunterladen", "Download Material List")
		,"participants_count" => array("Anzahl/Teilnehmer", "Number/Participants")
		,"course_count" => array("Anzahl/Seminar", "Number/Course")
		,"product_id" => array("Artikelnummer", "Product Id")
		,"title" => array("Bezeichnung", "Title")
		,"updated" => array("Materialliste gespeichert.", "The material list has been updated.")
		,"delete_sure" => array("Wollen Sie wirklich die folgenden Materialien löschen?", "Are you sure you want to delete the following materials?")
		,"deleted" => array("Materialien wurden gelöscht.", "Materials have been deleted.")
		// xls
		,"xls_title" => array("Titel", "Title")
		,"xls_subtitle" => array("Untertitel", "Subtitle")
		,"xls_custom_id" => array("Nummer der Maßnahme", "Id of Training")
		,"xls_amount_participants" => array("Anzahl Teilnehmer", "Number of Participants")
		,"xls_date_info" => array("Datum", "Period")
		,"xls_trainer" => array("Trainer", "Trainer")
		,"xls_venue_info" => array("Veranstaltungsort", "Venue")
		,"xls_contact" => array("Bei Rückfragen", "Contact")
		,"xls_creation_date" => array("Datum der Erstellung", "Date of Creation")	
		,"xls_list_header" => array("Materialliste", "Material List")	
		,"xls_list_general_header" => array("Generelle Angaben zum Training", "General Info about Training")	
		,"xls_list_item_header" => array("Materialien", "Materials")	
	);
	ilCustomInstaller::addLangData("matlist", array("de", "en"), $lang_data, "patch generali - material list");

	ilCustomInstaller::reloadStructure();
?>

<#88>
<?php
	//more fields in history
	if(!$ilDB->tableColumnExists('hist_user', "is_active")){
		$ilDB->addTableColumn('hist_user', "is_active", array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
			)
		);	
	}
	if(!$ilDB->tableColumnExists('hist_course', "is_online")){
		$ilDB->addTableColumn('hist_course', "is_online", array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
			)
		);	
	}
?>

<#89>
<?php
	//deadline fields in course history
	$deadlines = array(
		'dl_invitation',
		'dl_storno',
		'dl_booking',
		'dl_waitinglist'
	);

	foreach ($deadlines as $deadline) {
		if(!$ilDB->tableColumnExists('hist_course', $deadline)){
			$ilDB->addTableColumn('hist_course', $deadline, array(
				'type' => 'integer',
				'length' => 3,
				'notnull' => false,
				)
			);	
		}
	}
?>

<#90>
<?php

	// calendar entry weight
	if(!$ilDB->tableColumnExists('cal_entries', 'entry_weight'))
	{
		$ilDB->addTableColumn('cal_entries', 'entry_weight', 
			array(
				'type' => 'integer', 
				'length' => 1, 
				'notnull' => false, 
				'default' => ''
		));			
	}
	
	// operation day weight
	if(!$ilDB->tableColumnExists('tep_op_days', 'weight'))
	{
		$ilDB->addTableColumn('tep_op_days', 'weight', 
			array(
				'type' => 'integer', 
				'length' => 1, 
				'notnull' => false, 
				'default' => ''
		));			
	}

?>

<#91>
<?php
	if(!$ilDB->tableExists('hist_tep_individ_days'))
	{
		$fields = array (
			'id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0),
			'day' => array(
				'type' => 'date',
				'notnull' => true
				),
			'start_time' => array(
				'type' => 'text',
				'length' => 5,
				'notnull' => false
				),
			'end_time' => array(
				'type' => 'text',
				'length' => 5,
				'notnull' => false
				),
			'weight' => array(
				'type' => 'integer',
				'length' => 1,
				'notnull' => true
				)
		);
		$ilDB->createTable('hist_tep_individ_days', $fields);
		$ilDB->addPrimaryKey('hist_tep_individ_days', array('id', 'day'));
		$ilDB->createSequence('hist_tep_individ_days');
	}

?>

<#92>
<?php
	require_once "Customizing/class.ilCustomInstaller.php";
	ilCustomInstaller::maybeInitClientIni();
	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitObjDataCache();
	ilCustomInstaller::maybeInitUserToRoot();

	require_once "Services/GEV/Utils/classes/class.gevOrgUnitUtils.php";
	
	$res = $ilDB->query("SELECT DISTINCT oref.ref_id "
						."  FROM object_data od "
						."  JOIN object_reference oref ON oref.obj_id = od.obj_id "
						." WHERE ".$ilDB->in("import_id", array("evg"), false, "text")
						."   AND oref.deleted IS NULL"
						."   AND od.type = 'orgu'"
						);
	
	while($rec = $ilDB->fetchAssoc($res)) {
		gevOrgUnitUtils::grantPermissionsRecursivelyFor($rec["ref_id"], "superior",
				array( "cat_administrate_users"
					 , "read_users"
					 ));
	}
?>


<#93>
<?php
	require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
	require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
	require_once("Customizing/class.ilCustomInstaller.php");
	
	ilCustomInstaller::maybeInitClientIni();
	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitObjDataCache();
	ilCustomInstaller::maybeInitUserToRoot();
	
	$res = $ilDB->query( "SELECT od.obj_id, oref.ref_id, od.title "
						."  FROM object_data od"
						."  JOIN object_reference oref ON oref.obj_id = od.obj_id"
						." WHERE oref.deleted IS NULL"
						."   AND type = 'orgu'"
						."   AND title LIKE 'Organisationsdirektion%'"
						);

	while ($rec = $ilDB->fetchAssoc($res)) {
		$tmp = explode(" ", $rec["title"]);
		$res2 = $ilDB->query("SELECT od.obj_id, oref.ref_id, od.title "
							."  FROM object_data od"
							."  JOIN object_reference oref ON oref.obj_id = od.obj_id"
							." WHERE oref.deleted IS NULL"
							."   AND type = 'orgu'"
							."   AND title = 'Filialdirektion ".$tmp[1]."'"
							);
		$rec2 = $ilDB->fetchAssoc($res2);
		if (!$rec2) {
			continue;
		}
		
		$source_ou = gevOrgUnitUtils::getInstance($rec["obj_id"]);
		$target_ou = gevOrgUnitUtils::getInstance($rec2["obj_id"]);
		$source_employees = gevOrgUnitUtils::getEmployeesIn(array($rec["ref_id"]));
		foreach (gevOrgUnitUtils::getAllPeopleIn(array($rec["ref_id"])) as $usr_id) {
			$user = gevUserUtils::getInstance($usr_id);
			if ($user->hasRoleIn(array("FD", "OD/BD", "UA", "OD/FD/BD ID", "DBV EVG"))) {
				continue;
			}
			$role = in_array($usr_id, $source_employees) ? "Mitarbeiter" : "Vorgesetzter";
			$source_ou->deassignUser($usr_id, $role);
			$target_ou->assignUser($usr_id, $role);
		}
	}
?>

<#94>
<?php
	// Enables local user administration. Based on Martin Studers Paper
	// 20140911_LinkLokaleBenutzerverwaltung.docx

	require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
	require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
	require_once("Customizing/class.ilCustomInstaller.php");
	
	ilCustomInstaller::maybeInitClientIni();
	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitObjDataCache();
	ilCustomInstaller::maybeInitUserToRoot();
	
	$global_roles_of_superiors = array( "Administrator"
									  , "Admin-Voll"
									  , "Admin-eingeschraenkt"
									  , "Admin-Ansicht"
									  , "OD/BD"
									  , "FD"
									  , "UA"
									  , "ID FK"
									  , "DBV UVG"
									  );


	$res = $ilDB->query("SELECT DISTINCT oref.ref_id "
						."  FROM object_data od "
						."  JOIN object_reference oref ON oref.obj_id = od.obj_id "
						." WHERE ".$ilDB->in("import_id", array("evg"), false, "text")
						."   AND oref.deleted IS NULL"
						."   AND od.type = 'orgu'"
						);

	if ($rec = $ilDB->fetchAssoc($res)) {
		foreach($global_roles_of_superiors as $role) {
			gevOrgUnitUtils::grantPermissionsRecursivelyFor($rec["ref_id"], $role,
					array( "visible"
						 , "read"
						 ));
		}
	}
	else {
		die("Could not find orgu with import id evg.");
	}

	$res = $ilDB->query("SELECT DISTINCT od.obj_id "
						."  FROM object_data od "
						."  JOIN object_reference oref ON oref.obj_id = od.obj_id "
						." WHERE ".$ilDB->in("import_id", array("gev_base"), false, "text")
						."   AND oref.deleted IS NULL"
						."   AND od.type = 'orgu'"
						);
	
	if ($rec = $ilDB->fetchAssoc($res)) {
		foreach($global_roles_of_superiors as $role) {
			gevOrgUnitUtils::getInstance($rec["obj_id"])
				->grantPermissionsFor($role,
					array( "visible"
						 , "read"
						 ));
		}
	}
	else {
		die("Could not find orgu with import id gev_base.");
	}

	// Administration 
	$ref_id = 9;
	global $rbacreview;
	global $rbacadmin;
	foreach($global_roles_of_superiors as $role_name) {
		$role = gevRoleUtils::getInstance()->getRoleIdByName($role_name);

		if (!$role) {
			die("Could not find role $role_name");
		}
		$cur_ops = $rbacreview->getRoleOperationsOnObject($role, $ref_id);
		$grant_ops = ilRbacReview::_getOperationIdsByName(array("visible", "read"));
		$new_ops = array_unique(array_merge($grant_ops, $cur_ops));
		$rbacadmin->revokePermission($ref_id, $role);
		$rbacadmin->grantPermission($role, $new_ops, $ref_id);
	}

	// Org-Units 
	$ref_id = 56;
	global $rbacreview;
	global $rbacadmin;
	foreach($global_roles_of_superiors as $role_name) {
		$role = gevRoleUtils::getInstance()->getRoleIdByName($role_name);
		if (!$role) {
			die("Could not find role $role_name");
		}
		$cur_ops = $rbacreview->getRoleOperationsOnObject($role, $ref_id);
		$grant_ops = ilRbacReview::_getOperationIdsByName(array("visible", "read"));
		$new_ops = array_unique(array_merge($grant_ops, $cur_ops));
		$rbacadmin->revokePermission($ref_id, $role);
		$rbacadmin->grantPermission($role, $new_ops, $ref_id);
	}


?>

<#95>
<?php
	// Create Personal Org Unit structure for HAs

	require_once "Customizing/class.ilCustomInstaller.php";
	ilCustomInstaller::maybeInitClientIni();
	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitObjDataCache();
	ilCustomInstaller::maybeInitUserToRoot();

	require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
	$ha_ou_id = gevOrgUnitUtils::createOrgUnit("ha", "Hauptagenturen", "gev_base");
	$ha_tmplt_ou_id = gevOrgUnitUtils::createOrgUnit("ha_tmplt", "Vorlage HA-Einheiten", "ha");
	
	require_once("Services/GEV/Utils/classes/class.gevSettings.php");
	$gev_settings = gevSettings::getInstance();
	$gev_settings->setHAPOUBaseUnitId($ha_ou_id);
	$gev_settings->setHAPOUTemplateUnitId($ha_tmplt_ou_id);
?>


<#96>
<?php
	// Enables local user administration for HAs in HA-substructure. 
	// Based on Martin Studers Paper 20140911_LinkLokaleBenutzerverwaltung.docx

	require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
	require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
	require_once("Customizing/class.ilCustomInstaller.php");
	
	ilCustomInstaller::maybeInitClientIni();
	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitObjDataCache();
	ilCustomInstaller::maybeInitUserToRoot();

	$global_roles_of_superiors = array( "HA 84"
									  );
	
	// Administration and Org-Units 
	$ref_ids = array(9, 56);

	// Generali Versicherungen and HA-Substructure
	$res = $ilDB->query("SELECT DISTINCT oref.ref_id "
						."  FROM object_data od "
						."  JOIN object_reference oref ON oref.obj_id = od.obj_id "
						." WHERE ".$ilDB->in("import_id", array("gev_base", "ha"), false, "text")
						."   AND oref.deleted IS NULL"
						."   AND od.type = 'orgu'"
						);

	while($rec = $this->db->fetchAssoc($res)) {
		$ref_ids[] = $rec["ref_id"];
	}


	global $rbacreview;
	global $rbacadmin;
	foreach($ref_ids as $ref_id) {
		foreach($global_roles_of_superiors as $role_name) {
			$role = gevRoleUtils::getInstance()->getRoleIdByName($role_name);
			if (!$role) {
				die("Could not find role $role_name");
			}
			$cur_ops = $rbacreview->getRoleOperationsOnObject($role, $ref_id);
			$grant_ops = ilRbacReview::_getOperationIdsByName(array("visible", "read"));
			$new_ops = array_unique(array_merge($grant_ops, $cur_ops));
			$rbacadmin->revokePermission($ref_id, $role);
			$rbacadmin->grantPermission($role, $new_ops, $ref_id);
		}
	}

	require_once("Services/GEV/Utils/classes/class.gevSettings.php");
	$gev_settings = gevSettings::getInstance();
	$ha_tmplt_ou_id = $gev_settings->getHAPOUTemplateUnitId();
	gevOrgUnitUtils::getInstance($ha_tmplt_ou_id)
		->grantPermissionsFor( "superior"
							 , array("cat_administrate_users", "read_users")
							 );	
?>

<#97>
<?php

	// calendar entry weight
	if(!$ilDB->tableColumnExists('cal_entries', 'orgu_id'))
	{
		$ilDB->addTableColumn('cal_entries', 'orgu_id', 
			array(
				'type' => 'integer', 
				'length' => 4, 
				'notnull' => false, 
				'default' => null
		));			
	}

?>

<#98>
<?php

	if (!$ilDB->tableColumnExists("hist_tep", "orgu_title")) {
		$ilDB->addTableColumn('hist_tep', 'orgu_title', 
			array(
				'type' => 'text', 
				'length' => 255, 
				'notnull' => true, 
				'default' => "-empty-"
		));
	}

?>

<#99>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#100>
<?php
	$res = $ilDB->query("SELECT DISTINCT oref.ref_id "
						."  FROM object_data od "
						."  JOIN object_reference oref ON oref.obj_id = od.obj_id "
						." WHERE import_id = 'exit'"
						."   AND oref.deleted IS NULL"
						."   AND od.type = 'orgu'"
						);
	
	require_once("Services/GEV/Utils/classes/class.gevSettings.php");
	
	if ($rec = $ilDB->fetchAssoc($res)) {
		gevSettings::getInstance()->setOrgUnitExited($rec["ref_id"]);
	}
	else {
		die("Could not find orgu with import id exit.");
	}
?>


<#101>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#102>
<?php

require_once "Customizing/class.ilCustomInstaller.php";
ilCustomInstaller::maybeInitClientIni();
ilCustomInstaller::maybeInitPluginAdmin();
ilCustomInstaller::maybeInitObjDefinition();
ilCustomInstaller::maybeInitAppEventHandler();
ilCustomInstaller::maybeInitTree();
ilCustomInstaller::maybeInitRBAC();
ilCustomInstaller::maybeInitObjDataCache();
ilCustomInstaller::maybeInitUserToRoot();
ilCustomInstaller::maybeInitSettings();

require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

gevCourseUtils::grantPermissionsForAllCoursesBelow(1696, "RTL", array("write"));
gevCourseUtils::grantPermissionsForAllCoursesBelow(1783, "RTL", array("write"));
gevCourseUtils::grantPermissionsForAllCoursesBelow(1621, "RTL", array("write"));
gevCourseUtils::grantPermissionsForAllCoursesBelow(1644, "RTL", array("write"));


?>


<#103>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "User", "udfc", "GEVUserData");
?>

<#104>
<?php
	require_once "Customizing/class.ilCustomInstaller.php";
	ilCustomInstaller::maybeInitClientIni();
	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitObjDataCache();
	ilCustomInstaller::maybeInitUserToRoot();

	require_once("Services/GEV/Utils/classes/class.gevSettings.php");
	$gev_settings = gevSettings::getInstance();
	$res = $ilDB->query("SELECT obj_id FROM object_data WHERE import_id = 'na_ohne' AND type = 'orgu'");
	if ($rec = $ilDB->fetchAssoc($res)) {
		$gev_settings->setNAPOUNoAdviserUnitId($rec["obj_id"]);
	}
	else {
		die("Custom Update #104: Expected to find org_unit with import_id = 'na_ohne'");
	}
?>

<#105>
<?php

	require_once "Customizing/class.ilCustomInstaller.php";
	ilCustomInstaller::maybeInitClientIni();
	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitObjDataCache();
	ilCustomInstaller::maybeInitUserToRoot();

	require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");


	// Set the absolute cancel deadline to 9999 for all trainings in the category
	// "Grundausbildung"
	$cat_ref_id = 1644;

	foreach (gevCourseUtils::getAllCoursesBelow(array($cat_ref_id)) as $info) {
		$utils = gevCourseUtils::getInstance($info["obj_id"]);
		$utils->setAbsoluteCancelDeadline(9999);
	}

?>

<#106>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#107>
<?php
	// Create new user for the "Maklerangenbot"

	require_once "Customizing/class.ilCustomInstaller.php";
	ilCustomInstaller::maybeInitClientIni();
	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitObjDataCache();
	ilCustomInstaller::maybeInitUserToRoot();
	ilCustomInstaller::maybeInitSettings();
	ilCustomInstaller::maybeInitIliasObject();

	$user = new ilObjUser();
	$user->setLogin("makler_angebot");
	$user->setEmail("rklees@cat06.de");
	$user->setPasswd(md5(rand()));
	$user->setLastname("löschen");
	$user->setFirstname("nicht");
	$user->setGender("m");

	// is active, owner is root
	$user->setActive(true, 6);
	$user->setTimeLimitUnlimited(true);
	// user already agreed at registration

	$now = new ilDateTime(time(),IL_CAL_UNIX);
	$user->setAgreeDate($now->get(IL_CAL_DATETIME));
	$user->setIsSelfRegistered(true);

	$user->create();
	$user->saveAsNew();

	require_once("Services/GEV/Utils/classes/class.gevSettings.php");
	gevSettings::getInstance()->set(gevSettings::AGENT_OFFER_USER_ID, $user->getId());
?>

<#108>
<?php

	// Create DB-field for #1041
	if (!$ilDB->tableColumnExists("crs_pstatus_crs", "mail_send_date")) {
		$ilDB->addTableColumn('crs_pstatus_crs', 'mail_send_date', 
			array(
				'type' => 'date', 
				'notnull' => false
			));
	}

?>

<#109>
<?php
	require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
	require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
	require_once("Customizing/class.ilCustomInstaller.php");
	
	ilCustomInstaller::maybeInitClientIni();
	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitObjDataCache();
	ilCustomInstaller::maybeInitUserToRoot();

	$res = $ilDB->query("SELECT DISTINCT oref.ref_id "
						."  FROM object_data od "
						."  JOIN object_reference oref ON oref.obj_id = od.obj_id "
						." WHERE ".$ilDB->in("import_id", array("gev_base"), false, "text")
						."   AND oref.deleted IS NULL"
						."   AND od.type = 'orgu'"
						);

	if ($rec = $ilDB->fetchAssoc($res)) {
		gevOrgUnitUtils::grantPermissionsRecursivelyFor($rec["ref_id"], "superior",
					array( "view_learning_progress_rec"));
	}
	else {
		die("Custom Update #109: Expected to find org_unit with import_id = 'gev_base'");
	}

?>

<#110>
<?php
	// Add global and local Key-Accounter-Roles.
	require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
	require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
	require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
	require_once("Customizing/class.ilCustomInstaller.php");
	
	ilCustomInstaller::maybeInitClientIni();
	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitObjDataCache();
	ilCustomInstaller::maybeInitUserToRoot();
	
	$role_utils = gevRoleUtils::getInstance();
	
	$role_utils->createGlobalRole("Key-Accounter", "Key-Accounter (global)");
	
	$evg = gevOrgUnitUtils::getInstanceByImportId("evg");
	$children = gevOrgUnitUtils::getAllChildren(array($evg->getRefId()));
	foreach ($children as $child) {
		$role = $role_utils->createLocalRole($child["ref_id"], "Key-Accounter", "Key-Accounter (lokal)");
		$ouutils = gevOrgUnitUtils::getInstance($child["obj_id"]);
		$ouutils->grantPermissionsFor($role->getId(),  array( "view_learning_progress"
															, "view_learning_progress_rec"
															)
									 );
	}
?>


<#111>
<?php
	require_once("Services/WBDData/classes/class.wbdErrorLog.php");
	wbdErrorLog::_install();
?>

<#112>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#113>
<?php
	// Add global and local Key-Accounter-Roles.
	require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
	require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
	require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
	require_once("Customizing/class.ilCustomInstaller.php");
	
	ilCustomInstaller::maybeInitClientIni();
	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitObjDataCache();
	ilCustomInstaller::maybeInitUserToRoot();
	
	$role_utils = gevRoleUtils::getInstance();
	$role_utils->createGlobalRole("ExpressUser", "Benutzeraccount per Express-Login angelegt.");
?>

<#114>
<?php
	require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
	require_once("Customizing/class.ilCustomInstaller.php");

	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitObjDataCache();
	ilCustomInstaller::maybeInitUserToRoot();

	$orgu = new ilObjOrgUnit();
	$orgu->setTitle("ohne Zuordnung");
	$orgu->create();
	$orgu->createReference();
	$orgu->update();

	$id = $orgu->getId();
	$ref_id = $orgu->getRefId();

	$orgu->putInTree($orgu->getRootOrgRefId());
	$orgu->initDefaultRoles();



	require_once("Services/GEV/Utils/classes/class.gevSettings.php");
	$setting_utils = gevSettings::getInstance();
	$setting_utils->setOrgUnitUnassignedUser($ref_id);
?>


<#115>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#116>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#117>
<?php
	require_once("Services/VCPool/class.VCPoolInstaller.php");
	
	VCPoolInstaller::allSteps($ilDB);
?>

<#118>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "AdvancedMetaData", "amdc", "CourseAMD");

?>

<#119>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#120>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "AdvancedMetaData", "amdc", "CourseAMD");

?>

<#121>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "User", "udfc", "GEVUserData");
?>

<#122>
<?php
if(!$ilDB->tableColumnExists('hist_user', 'exit_date_wbd')){
	$ilDB->manipulate("ALTER TABLE `hist_user` ADD `exit_date_wbd` DATE NULL DEFAULT '0000-00-00' AFTER `is_active`");
}
?>

<#123>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#124>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#125>
<?php
	if(!$ilDB->tableColumnExists('hist_course', 'dbv_hot_topic')) {
		$ilDB->manipulate("ALTER TABLE `hist_course` ADD `dbv_hot_topic` VARCHAR(50) NULL");
	}
?>

<#126>
<?php
require_once "Customizing/class.ilCustomInstaller.php";
	ilCustomInstaller::initPluginEnv();
	ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "AdvancedMetaData", "amdc", "CourseAMD");
?>

<#127>
<?php
require_once "Customizing/class.ilCustomInstaller.php";
	ilCustomInstaller::initPluginEnv();
	ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "AdvancedMetaData", "amdc", "CourseAMD");
?>

<#128>
<?php
require_once "Customizing/class.ilCustomInstaller.php";
	ilCustomInstaller::initPluginEnv();
	ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "AdvancedMetaData", "amdc", "CourseAMD");
?>

<#129>
<?php
	if(!$ilDB->tableColumnExists('hist_course', 'webex_vc_type' )) {
		$ilDB->manipulate("ALTER TABLE `hist_course` ADD COLUMN webex_vc_type VARCHAR(50) NULL");
	}
?>


<#130> 
<?php
	if(!$ilDB->tableExists('hist_userorgu')) {
		$fields = array(
			'row_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true),
			'hist_version' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 1),
			'hist_historic' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0),
			'creator_user_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true),
			'created_ts' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0),
			'usr_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true),
			'orgu_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true),
			'rol_id' => array(
				'type' => 'integer' ,
				'length' => 4 ,
				'notnull' => true),
			'orgu_title' => array(
				'type' => 'text',
				'length' => 40 ,
				'notnull' => false),
			'org_unit_above1' => array(
				'type' => 'text',
				'length' => 40 ,
				'notnull' => false),
			'org_unit_above2' => array(
				'type' => 'text',
				'length' => 40 ,
				'notnull' => false),
			'rol_title' => array(
				'type' => 'text',
				'length' => 40 ,
				'notnull' => false),
			'action' => array(
				'type' => 'integer',
				'length' => 1 ,
				'notnull' => true)			
		);
		$ilDB->createTable('hist_userorgu', $fields);
		$ilDB->addPrimaryKey('hist_userorgu', array('row_id'));
		$ilDB->createSequence('hist_userorgu');
	}
?>

<#131> 
<?php
	if(!$ilDB->tableExists('hist_userrole')) {
		$fields = array(
			'row_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true),
			'hist_version' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 1),
			'hist_historic' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0),
			'creator_user_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true),
			'created_ts' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0),
			'usr_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true),
			'rol_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true),
			'rol_title' => array(
				'type' => 'text',
				'length' => 40 ,
				'notnull' => false),
			'action' => array(
				'type' => 'integer',
				'length' => 1 ,
				'notnull' => true)
		);
		$ilDB->createTable('hist_userrole', $fields);
		$ilDB->addPrimaryKey('hist_userrole', array('row_id'));
		$ilDB->createSequence('hist_userrole');
	}	
?>

<#132>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#133>
<?php
if( !$ilDB->tableExists('dct_building_block') )
{
	$ilDB->createTable('dct_building_block', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'title' => array(
			'type' => 'text',
			'length' => 50,
			'notnull' => true
		),
		'content' => array(
			'type' => 'text',
			'length' => 50,
			'notnull' => true
		),
		'learning_dest' => array(
			'type' => 'text',
			'length' => 50,
			'notnull' => true
		),
		'is_wp_relevant' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
		),
		'is_active' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
		),
		'is_deleted' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
		),
		'last_change_user' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		),
		'last_change_date' => array(
			'type' => 'timestamp',
			'notnull' => false
		)
	));
		
	$ilDB->addPrimaryKey('dct_building_block', array('obj_id'));
}
?>

<#134>
<?php
if( !$ilDB->tableExists('dct_crs_building_block') )
{
	$ilDB->createTable('dct_crs_building_block', array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'crs_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		),
		'bb_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'start_date' => array(
			'type' => 'timestamp',
			'notnull' => true
		),
		'end_date' => array(
			'type' => 'timestamp',
			'notnull' => true
		),
		'method' => array(
			'type' => 'text',
			'length' => 100,
			'notnull' => true
		),
		'media' => array(
			'type' => 'text',
			'length' => 100,
			'notnull' => true
		),
		'last_change_user' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		)
	));

	$ilDB->addPrimaryKey('dct_crs_building_block',array('id'));

}
?>

<#135>
<?php
require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreationRequestDB.php");
gevDecentralTrainingCreationRequestDB::install_step1($ilDB);
?>

<#136>
<?php
require_once "Customizing/class.ilCustomInstaller.php";
	ilCustomInstaller::initPluginEnv();
	ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "AdvancedMetaData", "amdc", "CourseAMD");
?>

<#137>
<?php
require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreationRequestDB.php");
gevDecentralTrainingCreationRequestDB::install_step2($ilDB);
?>

<#138>
<?php
if(!$ilDB->tableColumnExists('dct_crs_building_block', 'crs_request_id')) {
	$ilDB->addTableColumn('dct_crs_building_block', 'crs_request_id', array(
			"type" => "integer",
			"length" => 4,
			"notnull" => true
		));
}

if($ilDB->tableColumnExists('dct_crs_building_block', 'media')) {
	$ilDB->modifyTableColumn('dct_crs_building_block','media', array(
			'type' => 'text',
			'length' => 4000,
			'notnull' => true
	));
}

if($ilDB->tableColumnExists('dct_crs_building_block', 'method')) {
	$ilDB->modifyTableColumn('dct_crs_building_block','method', array(
			'type' => 'text',
			'length' => 4000,
			'notnull' => true
	));
}

if($ilDB->tableColumnExists('dct_building_block', 'title')) {
	$ilDB->modifyTableColumn('dct_building_block','title', array(
			'type' => 'text',
			'length' => 100,
			'notnull' => true,
			'default' => ""
	));
}

if($ilDB->tableColumnExists('dct_building_block', 'content')) {
	$ilDB->modifyTableColumn('dct_building_block','content', array(
			'type' => 'text',
			'length' => 100,
			'notnull' => true,
			'default' => ""
	));
}

if($ilDB->tableColumnExists('dct_building_block', 'learning_dest')) {
	$ilDB->modifyTableColumn('dct_building_block','learning_dest', array(
			'type' => 'text',
			'length' => 100,
			'notnull' => true,
			'default' => ""
	));
}
?>

<#139>
<?php
	$ilDB->createSequence("dct_crs_building_block");
	$ilDB->createSequence("dct_building_block");
?>

<#140>
<?php
require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreationRequestDB.php");
gevDecentralTrainingCreationRequestDB::install_step3($ilDB);
?>

<#141>
<?php
if(!$ilDB->tableColumnExists('dct_crs_building_block', 'last_change_date')) {
	$ilDB->addTableColumn('dct_crs_building_block', 'last_change_date', array(
			"type" => "timestamp",
			"notnull" => true
		));
}
?>

<#142>
<?php
$ilDB->modifyTableColumn('dct_crs_building_block', "crs_request_id", array(
		"type" => "integer",
		"length" => 4,
		"notnull" => false
));
?>

<#143>
<?php
$ilDB->modifyTableColumn('dct_crs_building_block', 'crs_id', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false
));
?>

<#144>
<?php
require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreationRequestDB.php");
gevDecentralTrainingCreationRequestDB::install_step4($ilDB);
gevDecentralTrainingCreationRequestDB::install_step5($ilDB);
?>

<#145>
<?php
if(!$ilDB->tableColumnExists('hist_usercoursestatus', 'gev_id')) {
	$ilDB->addTableColumn('hist_usercoursestatus', 'gev_id', array(
			"type" => "integer",
			"length" => 4,
			"notnull" => false
		));
}
?>

<#146>
<?php
	$ilDB->modifyTableColumn('hist_userorgu', "orgu_title", array(
			"type" => "text",
			"length" => 100,
			"notnull" => false
	));
?>

<#147>
<?php
	$ilDB->modifyTableColumn('hist_userorgu', "org_unit_above1", array(
			"type" => "text",
			"length" => 100,
			"notnull" => false
	));
	$ilDB->modifyTableColumn('hist_userorgu', "org_unit_above2", array(
			"type" => "text",
			"length" => 100,
			"notnull" => false
	));
?>

<#148> 
<?php

	$ilDB->renameTableColumn('hist_course', 'webex_vc_type', 'virtual_classroom_type');

?>

<#149>
<?php
	require_once("Services/GEV/Utils/classes/class.gevSettings.php");
	
	$ilDB->manipulate("UPDATE settings SET keyword = ".$ilDB->quote(gevSettings::CRS_AMD_VC_LINK,"text")
		." WHERE keyword = ".$ilDB->quote(gevSettings::CRS_AMD_WEBEX_LINK,"text"));

	$ilDB->manipulate("UPDATE settings SET keyword = ".$ilDB->quote(gevSettings::CRS_AMD_VC_PASSWORD,"text")
		." WHERE keyword = ".$ilDB->quote(gevSettings::CRS_AMD_WEBEX_PASSWORD,"text"));

	$ilDB->manipulate("UPDATE settings SET keyword = ".$ilDB->quote(gevSettings::CRS_AMD_VC_PASSWORD_TUTOR,"text")
		." WHERE keyword = ".$ilDB->quote(gevSettings::CRS_AMD_WEBEX_PASSWORD_TUTOR,"text"));

	$ilDB->manipulate("UPDATE settings SET keyword = ".$ilDB->quote(gevSettings::CRS_AMD_VC_CLASS_TYPE,"text")
		." WHERE keyword = ".$ilDB->quote(gevSettings::CRS_AMD_WEBEX_VC_CLASS_TYPE,"text"));

	$ilDB->manipulate("UPDATE settings SET keyword = ".$ilDB->quote(gevSettings::CRS_AMD_VC_LOGIN_TUTOR,"text")
		." WHERE keyword = ".$ilDB->quote(gevSettings::CRS_AMD_WEBEX_LOGIN_TUTOR,"text"));

	require_once "Customizing/class.ilCustomInstaller.php";
	ilCustomInstaller::initPluginEnv();
	ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "AdvancedMetaData", "amdc", "CourseAMD");

?>

<#150>
<?php
	
	$ilDB->dropTableColumn('hist_usercoursestatus', 'org_unit');

?>

<#151>
<?php

	if(!$ilDB->tableColumnExists('mail_log', "mail_id")){
		$ilDB->addTableColumn('mail_log', "mail_id", array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
			)
		);	
	}
?>

<#152>
<?php

	if(!$ilDB->tableColumnExists('mail_log', "recipient_id")){
		$ilDB->addTableColumn('mail_log', "recipient_id", array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
			)
		);	
	}
?>

<#153>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#154>
<?php
if(!$ilDB->tableColumnExists('dct_building_block', 'gdv_topic')) {
	$ilDB->addTableColumn('dct_building_block','gdv_topic', array(
		'type' => 'text',
		'length' => 100,
		'notnull' => true,
	));
}

if(!$ilDB->tableColumnExists('dct_building_block', 'training_categories')) {
	$ilDB->addTableColumn('dct_building_block','training_categories', array(
		'type' => 'text',
		'length' => 4000,
		'notnull' => true,
	));
}

if($ilDB->tableColumnExists('dct_crs_building_block', 'method')) {
	$ilDB->dropTableColumn('dct_crs_building_block','method');
}

if($ilDB->tableColumnExists('dct_crs_building_block', 'media')) {
	$ilDB->dropTableColumn('dct_crs_building_block','media');
}
?>

<#155>
<?php
if(!$ilDB->tableColumnExists('dct_building_block', 'topic')) {
	$ilDB->addTableColumn('dct_building_block','topic', array(
		'type' => 'text',
		'length' => 100,
		'notnull' => true,
	));
}

if(!$ilDB->tableColumnExists('dct_building_block', 'dbv_topic')) {
	$ilDB->addTableColumn('dct_building_block','dbv_topic', array(
		'type' => 'text',
		'length' => 100,
		'notnull' => true,
	));
}
?>

<#156>
<?php
if($ilDB->tableColumnExists('dct_crs_building_block', 'start_date')) {
	$ilDB->renameTableColumn('dct_crs_building_block', 'start_date', 'start_time');

	$ilDB->modifyTableColumn('dct_crs_building_block','start_time', array(
		'type' => 'time',
		'notnull' => true,
	));
}

if($ilDB->tableColumnExists('dct_crs_building_block', 'end_date')) {
	$ilDB->renameTableColumn('dct_crs_building_block', 'end_date', 'end_time');

	$ilDB->modifyTableColumn('dct_crs_building_block','end_time', array(
		'type' => 'time',
		'notnull' => true,
	));
}
?>

<#157>
<?php
if(!$ilDB->tableColumnExists('dct_crs_building_block', 'credit_points')) {
	$ilDB->addTableColumn('dct_crs_building_block','credit_points', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false,
	));
}
?>

<#158>
<?php
if(!$ilDB->tableColumnExists('dct_building_block', 'move_to_course')) {
	$ilDB->addTableColumn('dct_building_block','move_to_course', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false,
		'default' => 1
	));
}
?>

<#159>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#160>
<?php
if($ilDB->tableColumnExists('dct_building_block', 'content')) {

	$ilDB->modifyTableColumn('dct_building_block','content', array(
		'type' => 'text',
		'length' => 200,
		'notnull' => true,
		'default' => ""
	));
}

if($ilDB->tableColumnExists('dct_building_block', 'learning_dest')) {

	$ilDB->modifyTableColumn('dct_building_block','learning_dest', array(
		'type' => 'text',
		'length' => 200,
		'notnull' => true,
		'default' => ""
	));
}
?>

<#161>
<?php
$new_crs_ops = array(
	'view_schedule_pdf' => array('View Schedule PDF', 6002)
);
require_once "Customizing/class.ilCustomInstaller.php";
ilCustomInstaller::addRBACOps('crs', $new_crs_ops);
?>

<#162>
<?php
if($ilDB->tableColumnExists('dct_building_block', 'gdv_topic')) {
	$ilDB->modifyTableColumn('dct_building_block','gdv_topic', array(
		'type' => 'text',
		'length' => 100,
		'notnull' => false,
	));
}
?>

<#163>
<?php
if($ilDB->tableColumnExists('dct_building_block', 'content')) {

	$ilDB->modifyTableColumn('dct_building_block','content', array(
		'type' => 'text',
		'length' => 500,
		'notnull' => true,
		'default' => ""
	));
}

if($ilDB->tableColumnExists('dct_building_block', 'learning_dest')) {

	$ilDB->modifyTableColumn('dct_building_block','learning_dest', array(
		'type' => 'text',
		'length' => 500,
		'notnull' => true,
		'default' => ""
	));
}

if($ilDB->tableColumnExists('dct_crs_building_block', 'credit_points')) {

	$ilDB->modifyTableColumn('dct_crs_building_block','credit_points', array(
		'type' => 'float'
	));
}
?>

<#164>
<?php
if(!$ilDB->tableColumnExists('dct_crs_building_block', 'practice_session')) {

	$ilDB->addTableColumn('dct_crs_building_block','practice_session', array(
		'type' => 'float',
		'notnull' => false
	));
}
?>

<#165>
<?php
if($ilDB->tableColumnExists('dct_building_block', 'content')) {

	$ilDB->modifyTableColumn('dct_building_block','content', array(
		'type' => 'text',
		'length' => 500,
		'notnull' => false
	));
}

if($ilDB->tableColumnExists('dct_building_block', 'learning_dest')) {
	$ilDB->modifyTableColumn('dct_building_block','learning_dest', array(
		'type' => 'text',
		'length' => 500,
		'notnull' => false
	));
}

if($ilDB->tableColumnExists('dct_building_block', 'dbv_topic')) {
	$ilDB->modifyTableColumn('dct_building_block','dbv_topic', array(
		'type' => 'text',
		'length' => 100,
		'notnull' => false
	));
}

if($ilDB->tableColumnExists('dct_building_block', 'training_categories')) {
	$ilDB->modifyTableColumn('dct_building_block','training_categories', array(
		'type' => 'text',
		'length' => 4000,
		'notnull' => false
	));
}
?>

<#166>
<?php
$new_crs_ops = array(
	'change_trainer' => array('Change Trainer', 6003)
);
require_once "Customizing/class.ilCustomInstaller.php";
ilCustomInstaller::addRBACOps('crs', $new_crs_ops);
?>

<#167>
<?php
$new_crs_ops = array(
	'load_signature_list' => array('Load Signature List', 6004)
	,'load_member_list' => array('Load Member List', 6005)
);
require_once "Customizing/class.ilCustomInstaller.php";
ilCustomInstaller::addRBACOps('crs', $new_crs_ops);
?>

<#168>
<?php
$new_crs_ops = array(
	'load_csn_list' => array('Load CSN List', 6006)
);
require_once "Customizing/class.ilCustomInstaller.php";
ilCustomInstaller::addRBACOps('crs', $new_crs_ops);
?>

<#169>
<?php
$new_crs_ops = array(
	'view_maillog' => array('View Maillog', 6007)
);
require_once "Customizing/class.ilCustomInstaller.php";
ilCustomInstaller::addRBACOps('crs', $new_crs_ops);
?>

<#170>
<?php
require_once "Customizing/class.ilCustomInstaller.php";
	ilCustomInstaller::initPluginEnv();
	ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "AdvancedMetaData", "amdc", "CourseAMD");
?>


<#171>
<?php

require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::maybeInitClientIni();
ilCustomInstaller::maybeInitPluginAdmin();
ilCustomInstaller::maybeInitObjDefinition();
ilCustomInstaller::maybeInitAppEventHandler();
ilCustomInstaller::maybeInitTree();
ilCustomInstaller::maybeInitRBAC();
ilCustomInstaller::maybeInitObjDataCache();
ilCustomInstaller::maybeInitUserToRoot();
ilCustomInstaller::maybeInitSettings();

require_once("Services/Object/classes/class.ilObjectFactory.php");

global $ilias;
$ilias->db = $ilDB;

require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

$central_training_category_ref_ids = array(1696, 1783, 1621, 1644, 1686, 47318 , 43277, 1699, 34937);

foreach ($central_training_category_ref_ids as $ref_id) {
	gevCourseUtils::grantPermissionsForAllCoursesBelow($ref_id, "Administrator", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));
	gevCourseUtils::grantPermissionsForAllCoursesBelow($ref_id, "Admin-Voll", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));
	gevCourseUtils::grantPermissionsForAllCoursesBelow($ref_id, "Admin-eingeschraenkt", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));
	gevCourseUtils::grantPermissionsForAllCoursesBelow($ref_id, "admin", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));
	gevCourseUtils::grantPermissionsForAllCoursesBelow($ref_id, "trainer", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));
}

?>

<#172>
<?php

require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::maybeInitClientIni();
ilCustomInstaller::maybeInitPluginAdmin();
ilCustomInstaller::maybeInitObjDefinition();
ilCustomInstaller::maybeInitAppEventHandler();
ilCustomInstaller::maybeInitTree();
ilCustomInstaller::maybeInitRBAC();
ilCustomInstaller::maybeInitObjDataCache();
ilCustomInstaller::maybeInitUserToRoot();
ilCustomInstaller::maybeInitSettings();

require_once("Services/Object/classes/class.ilObjectFactory.php");

global $ilias;
$ilias->db = $ilDB;

require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

$fixed_dec_training_category_ref_id = 49841;

gevCourseUtils::grantPermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "Administrator", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "Admin-Voll", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "DBV UVG", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "DBV EVG", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "RTL", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "flex-dez-Training", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "Admin-dez-ID", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "admin", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "trainer", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "Pool Trainingsersteller", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));


$flex_dec_training_category_ref_id = 49840;

gevCourseUtils::grantPermissionsForAllCoursesBelow($flex_dec_training_category_ref_id, "Administrator", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($flex_dec_training_category_ref_id, "Admin-Voll", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($flex_dec_training_category_ref_id, "DBV UVG", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($flex_dec_training_category_ref_id, "flex-dez-Training", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($flex_dec_training_category_ref_id, "admin", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($flex_dec_training_category_ref_id, "trainer", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($flex_dec_training_category_ref_id, "Pool Trainingsersteller", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));

?>

<#173>
<?php

require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::maybeInitClientIni();
ilCustomInstaller::maybeInitPluginAdmin();
ilCustomInstaller::maybeInitObjDefinition();
ilCustomInstaller::maybeInitAppEventHandler();
ilCustomInstaller::maybeInitTree();
ilCustomInstaller::maybeInitRBAC();
ilCustomInstaller::maybeInitObjDataCache();
ilCustomInstaller::maybeInitUserToRoot();
ilCustomInstaller::maybeInitSettings();

require_once("Services/Object/classes/class.ilObjectFactory.php");

global $ilias;
$ilias->db = $ilDB;

require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

$fixed_dec_training_category_ref_id = 49841;

gevCourseUtils::revokePermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "DBV UVG", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));
gevCourseUtils::revokePermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "DBV EVG", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));

$flex_dec_training_category_ref_id = 49840;

gevCourseUtils::revokePermissionsForAllCoursesBelow($flex_dec_training_category_ref_id, "DBV UVG", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf"));

?>

<#174>
<?php
$new_crs_ops = array(
	'cancel_training' => array('Cancel Training', 6008)
);
require_once "Customizing/class.ilCustomInstaller.php";
ilCustomInstaller::addRBACOps('crs', $new_crs_ops);

?>

<#175>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#176>
<?php
if($ilDB->tableColumnExists('dct_building_block', 'learning_dest')) {
	$ilDB->renameTableColumn('dct_building_block','learning_dest','target');
}
?>

<#177>
<?php
require_once "Customizing/class.ilCustomInstaller.php";
	ilCustomInstaller::initPluginEnv();
	ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "AdvancedMetaData", "amdc", "CourseAMD");
?>

<#178>
<?php
	require_once("Services/Administration/classes/class.ilSetting.php");
	$set = new ilSetting();
	$set->set("enable_trash",0);
?>

<#179>
<?php
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::maybeInitClientIni();
ilCustomInstaller::maybeInitPluginAdmin();
ilCustomInstaller::maybeInitObjDefinition();
ilCustomInstaller::maybeInitAppEventHandler();
ilCustomInstaller::maybeInitTree();
ilCustomInstaller::maybeInitRBAC();
ilCustomInstaller::maybeInitObjDataCache();
ilCustomInstaller::maybeInitUserToRoot();
ilCustomInstaller::maybeInitSettings();


global $ilias;
$ilias->db = $ilDB;
global $ilClientIniFile;
$ilias->ini = $ilClientIniFile;

require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
$gev_set = gevSettings::getInstance();
$private_email_field_id = $gev_set->getUDFFieldId(gevSettings::USR_UDF_PRIV_EMAIL);

$res = $ilDB->query(
<<<SQL
	SELECT usr.usr_id, udf.value
	FROM usr_data usr
	JOIN udf_text udf ON usr.usr_id = udf.usr_id AND udf.field_id = $private_email_field_id
	WHERE
		NOT udf.value IS NULL
SQL
);

while ($rec = $ilDB->fetchAssoc($res)) {
	$usr_id = $rec["usr_id"];
	$utils = gevUserUtils::getInstance($usr_id);
	$user = $utils->getUser();
	$user->setEmail($rec["value"]);
	$user->update();
}
?>

<#180>
<?php
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::maybeInitClientIni();
ilCustomInstaller::maybeInitPluginAdmin();
ilCustomInstaller::maybeInitObjDefinition();
ilCustomInstaller::maybeInitAppEventHandler();
ilCustomInstaller::maybeInitTree();
ilCustomInstaller::maybeInitRBAC();
ilCustomInstaller::maybeInitObjDataCache();
ilCustomInstaller::maybeInitUserToRoot();
ilCustomInstaller::maybeInitSettings();

require_once("Services/GEV/Utils/classes/class.gevUDFUtils.php");
gevUDFUtils::removeUDFField(gevSettings::USR_UDF_PRIV_EMAIL);
?>

<#181>
<?php
require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreationRequestDB.php");
gevDecentralTrainingCreationRequestDB::install_step6($ilDB);
?>

<#182>
<?php
if( !$ilDB->tableExists('crs_custom_attachments') )
{
	$ilDB->createTable('crs_custom_attachments', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'file_name' => array(
			'type' => 'text',
			'length' => 250,
			'notnull' => true,
			'default' => "-"
		)	
	));
		
	$ilDB->addPrimaryKey('crs_custom_attachments', array('obj_id', 'file_name'));
}
?>

<#183>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#184>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "User", "udfc", "GEVUserData");
?>

<#185>
<?php
if(!$ilDB->tableColumnExists('hist_user', 'next_wbd_action')) {
	$ilDB->addTableColumn('hist_user', 'next_wbd_action', array(
		'type' => 'text',
		'length' => 255,
		'notnull' => false
		)
	);
}
?>

<#186>
<?php

		$ilDB->addTableColumn('hist_course', 'dct_type', array(
			'type' => 'text',
			'length' => 30,
			'notnull' => false
			)
		);	

?>

<#187>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "User", "udfc", "GEVUserData");
?>

<#188>
<?php
	if(!$ilDB->tableColumnExists('hist_course', 'template_obj_id')) {
		$ilDB->addTableColumn('hist_course', 'template_obj_id', array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
			)
		);
	}
?>

<#189>
<?php
if($ilDB->tableExists('hist_userrole')) {
	$s = "ALTER TABLE hist_userrole ADD INDEX rol_ind (rol_id);";
	$ilDB->manipulate($s);

	$s = "ALTER TABLE hist_userrole ADD INDEX usr_ind (usr_id);";
	$ilDB->manipulate($s);
}
?>

<#190>
<?php

require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::maybeInitClientIni();
ilCustomInstaller::maybeInitPluginAdmin();
ilCustomInstaller::maybeInitObjDefinition();
ilCustomInstaller::maybeInitAppEventHandler();
ilCustomInstaller::maybeInitTree();
ilCustomInstaller::maybeInitRBAC();
ilCustomInstaller::maybeInitObjDataCache();
ilCustomInstaller::maybeInitUserToRoot();
ilCustomInstaller::maybeInitSettings();

require_once("Services/Object/classes/class.ilObjectFactory.php");

global $ilias;
$ilias->db = $ilDB;

require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

$fixed_dec_training_category_ref_id = 49841;

gevCourseUtils::grantPermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "Administrator", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf", "cancel_training"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "Admin-Voll", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf", "cancel_training"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "Admin-eingeschraenkt", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf", "cancel_training"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "RTL", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf", "cancel_training"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "flex-dez-Training", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf", "cancel_training"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "Admin-dez-ID", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf", "cancel_training"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "admin", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf", "cancel_training"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "trainer", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf", "cancel_training"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "Pool Trainingsersteller", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf", "cancel_training", "write_reduced_settings"));
gevCourseUtils::revokePermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "Pool Trainingsersteller", array("write"));


$flex_dec_training_category_ref_id = 49840;

gevCourseUtils::grantPermissionsForAllCoursesBelow($flex_dec_training_category_ref_id, "Administrator", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf", "cancel_training"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($flex_dec_training_category_ref_id, "Admin-Voll", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf", "cancel_training"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($flex_dec_training_category_ref_id, "flex-dez-Training", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf", "cancel_training"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($flex_dec_training_category_ref_id, "admin", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf", "cancel_training"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($flex_dec_training_category_ref_id, "trainer", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf", "cancel_training"));
gevCourseUtils::grantPermissionsForAllCoursesBelow($flex_dec_training_category_ref_id, "Pool Trainingsersteller", array("change_trainer","load_signature_list","load_member_list","load_csn_list","view_maillog","view_schedule_pdf", "cancel_training", "write_reduced_settings"));
gevCourseUtils::revokePermissionsForAllCoursesBelow($flex_dec_training_category_ref_id, "Pool Trainingsersteller", array("write"));

?>

<#191>
<?php

require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::maybeInitClientIni();
ilCustomInstaller::maybeInitPluginAdmin();
ilCustomInstaller::maybeInitObjDefinition();
ilCustomInstaller::maybeInitAppEventHandler();
ilCustomInstaller::maybeInitTree();
ilCustomInstaller::maybeInitRBAC();
ilCustomInstaller::maybeInitObjDataCache();
ilCustomInstaller::maybeInitUserToRoot();
ilCustomInstaller::maybeInitSettings();

require_once("Services/Object/classes/class.ilObjectFactory.php");

global $ilias;
$ilias->db = $ilDB;

require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

$fixed_dec_training_category_ref_id = 49841;

gevCourseUtils::grantPermissionsForAllCoursesBelow($fixed_dec_training_category_ref_id, "Pool Trainingsersteller", array("visible", "read", "view_bookings", "book_users", "cancel_bookings", "view_participation_status", "set_participation_status", "review_participation_status"));

$flex_dec_training_category_ref_id = 49840;

gevCourseUtils::grantPermissionsForAllCoursesBelow($flex_dec_training_category_ref_id, "Pool Trainingsersteller", array("visible", "read", "view_bookings", "book_users", "cancel_bookings", "view_participation_status", "set_participation_status", "review_participation_status"));

?>

<#192>
<?php
// TEP categories
$s = "INSERT INTO tep_type (id, title, bg_color, font_color, tep_active) VALUES (25, 'Weiterbildungstage', 'bf6364', '000000', 1)";
$ilDB->manipulate($s);
?>

<#193>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#194>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#195>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "EventHandling", "evhk", "GEVCourseDelete");
?>

<#196>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "User", "udfc", "GEVUserData");
?>

<#197>
<?php
if( !$ilDB->tableExists('wbd_errors_categories') )
{
	$ilDB->createTable('wbd_errors_categories', array(
													'id' => array(
														'type' => 'integer',
														'length' => 4,
														'notnull' => true,
														'default' => 0
													),
													'reason_string' => array(
														'type' => 'text',
														'length' => 200,
														'notnull' => false
													),
													'internal' => array(
														'type' => 'integer',
														'length' => 1,
														'notnull' => false
													),
													'failure' => array(
														'type' => 'text',
														'length' => 1000,
														'notnull' => false
													)
												)
					);
}
?>

<#198>
<?php
if (!$ilDB->tableColumnExists('wbd_errors_categories', 'error_group'))
	{		
		$ilDB->addTableColumn('wbd_errors_categories', 'error_group', array(
			"type" => "text",
			"length" => 50,
			"notnull" => false
		));
	}
?>

<#199>
<?php
	require_once "Customizing/class.ilCustomInstaller.php";
	require_once('Modules/OrgUnit/classes/Types/class.ilOrgUnitType.php');
	require_once('Services/GEV/Utils/classes/class.gevSettings.php');

	ilCustomInstaller::maybeInitClientIni();
	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitObjDataCache();
	ilCustomInstaller::maybeInitUserToRoot();
	ilCustomInstaller::maybeInitSettings();

	$type = new ilOrgUnitType();
	$type->setDefaultLang("de");
	$type->setTitle("BD", "de");
	$type->setDescription("Identifiziert eine Organisationseinheiten als BD", "de");
	$type->setTitle("BD", "en");
	$type->setDescription("Identifies an Organisational Unit as BD", "en");
	$type->save();

	$settings = gevSettings::getInstance();
	$settings->setTypeIDOrgUnitTypeDB($type->getId());
?>

<#200>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#201>
<?php
if (!$ilDB->tableColumnExists('hist_userorgu', 't_in'))
	{		
		$ilDB->addTableColumn('hist_userorgu', 't_in', array(
			"type" => "integer",
			"length" => 4,
			"notnull" => false
		));
	}
?>

<#202>
<?php
if ($ilDB->tableColumnExists('tep_type', 'tep_active'))	{		
	$s_query = "UPDATE tep_type SET tep_active = 0 WHERE title = 'Training'";
	$ilDB->manipulate($s_query);
}
?>

<#203>
<?php
	require_once "Customizing/class.ilCustomInstaller.php";
	ilCustomInstaller::initPluginEnv();
	ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "AdvancedMetaData", "amdc", "CourseAMD");
?>

<#204>
<?php
	if (!$ilDB->tableColumnExists('hist_course', 'is_cancelled')) {		
		$ilDB->addTableColumn('hist_course', 'is_cancelled', array(
			"type" => "text",
			"length" => 8,
			"notnull" => false
		));
	}
?>

<#205>
<?php
	if (!$ilDB->tableColumnExists('hist_course', 'waitinglist_active')) {		
		$ilDB->addTableColumn('hist_course', 'waitinglist_active', array(
			"type" => "text",
			"length" => 8,
			"notnull" => false
		));
	}
	if (!$ilDB->tableColumnExists('hist_course', 'max_participants')) {		
		$ilDB->addTableColumn('hist_course', 'max_participants', array(
			"type" => "integer",
			"length" => 4,
			"notnull" => false
		));
	}
	if (!$ilDB->tableColumnExists('hist_course', 'min_participants')) {		
		$ilDB->addTableColumn('hist_course', 'min_participants', array(
			"type" => "integer",
			"length" => 4,
			"notnull" => false
		));
	}
	if (!$ilDB->tableColumnExists('hist_course', 'size_waitinglist')) {		
		$ilDB->addTableColumn('hist_course', 'size_waitinglist', array(
			"type" => "integer",
			"length" => 4,
			"notnull" => false
		));
	}
?>
<#206>
<?php
	if (!$ilDB->tableColumnExists('hist_course', 'accomodation')) {		
		$ilDB->addTableColumn('hist_course', 'accomodation', array(
			"type" => "text",
			"length" => 255,
			"notnull" => false
		));
	}
?>

<#207>
<?php
	if (!$ilDB->tableColumnExists('dct_building_block', 'pool_id'))
	{		
		$ilDB->addTableColumn('dct_building_block', 'pool_id', array(
			"type" => "integer",
			"length" => 4,
			"notnull" => false
		));
	}
?>

<#208>
<?php
	require_once "Customizing/class.ilCustomInstaller.php";
	ilCustomInstaller::initPluginEnv();
	ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "AdvancedMetaData", "amdc", "CourseAMD");
?>

<#209>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "Repository", "robj", "BuildingBlockPool");

?>

<#210>
<?php
$ilCtrlStructureReader->getStructure();

require_once "Customizing/class.ilCustomInstaller.php";
ilCustomInstaller::maybeInitClientIni();
ilCustomInstaller::maybeInitPluginAdmin();
ilCustomInstaller::maybeInitObjDefinition();
ilCustomInstaller::maybeInitAppEventHandler();
ilCustomInstaller::maybeInitTree();
ilCustomInstaller::maybeInitRBAC();
ilCustomInstaller::maybeInitObjDataCache();
ilCustomInstaller::maybeInitUserToRoot();
ilCustomInstaller::maybeInitSettings();

require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
$relevant_ref_ids = array(1696,1783,1621,1644,1686,47318,43277,1699,34937);
foreach ($relevant_ref_ids as $ref_id) {
	gevCourseUtils::grantPermissionsForAllCoursesBelow($ref_id, "Admin-Ansicht", array("load_member_list"));
}
?>

<#211>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#212>
<?php
	require_once "Customizing/class.ilCustomInstaller.php";
	ilCustomInstaller::maybeInitClientIni();
	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitObjDataCache();
	ilCustomInstaller::maybeInitUserToRoot();
	ilCustomInstaller::maybeInitSettings();

	require_once("Services/User/classes/class.ilObjUser.php");
	
	if (!$ilDB->tableColumnExists('hist_user', 'login'))
	{		
		$ilDB->addTableColumn('hist_user', 'login', array(
			"type" => "text",
			"length" => 80,
			"notnull" => false,
			"default" => "-empty-"
		));
	}
?>