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

	if(!$ilDB->tableExists('crs_acco'))
	{
		echo "<br />DB table.";
		
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
	
	echo "<br />RBAC operations.";

	$new_crs_ops = array(
		'view_own_accomodations' => array('View Own Accomodations', 2906)
		,'set_own_accomodations' => array('Set Own Accomodations', 2907)
		,'view_others_accomodations' => array('View Others Accomodations', 3507)
		,'set_others_accomodations' => array('Set Others Accomodations', 3508)		
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
	"tab_list_accomodations" => array("Übernachtungen", "Accomodations")	
	,"accomodations" => array("Übernachtungen", "Accomodations")	
	,"edit_user_accomodations" => array("Teilnehmer bearbeiten", "Edit participant")	
	,"period_input_from" => array("von", "from")	
	,"period_input_to" => array("bis", "to")	
	,"period_input_from_first" => array("Vorabend", "Previous Evening")	
	,"period_input_to_last" => array("nächster Morgen", "Next Morning")	
);

echo "<br />lng update.";
ilCustomInstaller::addLangData("acco", array("de", "en"), $lang_data, "patch generali - accomodations");


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