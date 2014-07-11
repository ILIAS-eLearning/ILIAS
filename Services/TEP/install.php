<?php

// exit();

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

	if(!$ilDB->tableExists('cal_derived_entry'))
	{
		echo "<br />DB table.";
		
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
	
	echo "<br />RBAC operations.";

	$new_org_ops = array(
		'tep_is_tutor' => array('TEP Tutor', 2904)
		,'tep_view_other' => array('TEP View Other', 2905)
		,'tep_view_other_rcrsv' => array('TEP View Other (recursive)', 2906)
		,'tep_edit_other' => array('TEP Edit Other', 3505)
		,'tep_edit_other_rcrsv' => array('TEP Edit Other (recursive)', 3506)
	);
	ilCustomInstaller::addRBACOps('orgu', $new_org_ops);
}
else
{
	echo "Updating.";
}


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

echo "<br />lng update.";
ilCustomInstaller::addLangData("tep", array("de", "en"), $lang_data, "patch generali - TEP");


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