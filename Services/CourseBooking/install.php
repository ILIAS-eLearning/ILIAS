<?php

//exit();

$update = true;

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

	if(!$ilDB->tableExists('crs_book'))
	{
		echo "<br />DB table.";
		
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


	//
	// create RBAC permissions (incl. org unit?)
	//
	
	echo "<br />RBAC operations.";

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
}
else
{
	echo "Updating.";
}


//
// lng variables
//

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

echo "<br />lng update.";
ilCustomInstaller::addLangData("crsbook", array("de", "en"), $lang_data, "patch generali - course booking");


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