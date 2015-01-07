<?php

exit();

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

	if(!$ilDB->tableExists('crs_matlist'))
	{
		echo "<br />DB table.";
			
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


	//
	// create RBAC permissions (incl. org unit?)
	//
	
	echo "<br />RBAC operations.";

	$new_crs_ops = array(
		'view_material' => array('View Material', 2910)
		,'edit_material' => array('Edit Material', 3510)
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

echo "<br />lng update.";
ilCustomInstaller::addLangData("matlist", array("de", "en"), $lang_data, "patch generali - material list");


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