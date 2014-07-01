<?php

//exit();

$update = false;

// init helper class
require_once "../../Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initILIAS();
ilCustomInstaller::checkIsAdmin();

if(!$update)
{
	echo "Installing.";
	
	//
	// create database tables
	// 

	if(!$ilDB->tableExists('crs_pstatus_crs'))
	{
		echo "<br />DB table.";
		
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
	
	echo "<br />RBAC operations.";

	$new_crs_ops = array(
		'view_participation_status' => array('View Participation Status', 2902)
		,'set_participation_status' => array('Set Participation Status', 3502)
		,'review_participation_status' => array('Review Participation Status', 3503)
	);
	ilCustomInstaller::addRBACOps('crs', $new_crs_ops);
}
else
{
	echo "Updating.";
}


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

echo "<br />lng update.";
ilCustomInstaller::addLangData("ptst", array("de", "en"), $lang_data, "patch generali - participation status");


//
// reload ilCtrl
//

if(!$update)
{
	echo "<br />ilCtrl reload.";
	ilCustomInstaller::reloadStructure();
}

echo "<br />Done.";

?>