<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once('./Services/FileSystem/classes/class.ilFileSystemGUI.php');

/**
 * File System Explorer GUI class
 *
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 */
class ilExAssignmentFileSystemGUI extends ilFileSystemGUI
{

	function __construct($a_main_directory)
	{
		parent::__construct($a_main_directory);

	}

	/**
	 * main view
	 */
	public function filesListView()
	{
		$this->getListFiles("ilExAssignmentFileSystemTableGUI");
	}

	/**
	 * Getting the list of the files that belongs to an assignment
	 *
	 * @param string $a_class_table_gui
	 */
	public function getListFiles($a_class_table_gui = "")
	{
		parent::listFiles($a_class_table_gui);
	}

	/**
	 * Insert into database the file order and update the file.
	 *
	 * @param string view to redirect
	 */
	public function uploadFile($a_ctr_redirect = "filesListView")
	{
		$this->insertOrder();
		parent::uploadFile($a_ctr_redirect);

	}

	/**
	 * Store the file order in the database
	 */
	public function insertOrder()
	{
		$order = 0;
		$order_val = 0;
		//if we have ass_id it means that we are saving instruction files for an exc. assignment
		if($_GET["ass_id"])
		{
			global $ilDB;

			//get max order number
			$result = $ilDB->queryF("SELECT max(order_nr) as max_order FROM exc_ass_file_order WHERE assignment_id = %s",
				array('integer'),
				array($ilDB->quote($_GET["ass_id"], 'integer'))
			);

			while($row = $ilDB->fetchAssoc($result))
			{
				$order_val = (int)$row['max_order'];
			}

			$order = $order_val + 10;

			$id = $ilDB->nextID('exc_ass_file_order');
			$ilDB->manipulate("INSERT INTO exc_ass_file_order " .
				"(id, assignment_id, filename, order_nr) VALUES (" .
				$ilDB->quote($id, "integer") . "," .
				$ilDB->quote($_GET["ass_id"], "integer") . "," .
				$ilDB->quote($_FILES["new_file"]['name'], "text") . "," .
				$ilDB->quote($order, "integer") .
				")");
		}
	}

	/**
	 * Save all the orders.
	 */
	public function saveFilesOrder()
	{
		global $ilCtrl;

		if($_GET["ass_id"])
		{
			ilExAssignment::saveInstructionFilesOrderOfAssignment($_GET['ass_id'], $_POST["order"]);
			$ilCtrl->redirect($this, "filesListView");
		}
	}


	/**
	 * delete object file
	 * we can pass one parameter to deleteFile in fileSystemGUI, that contains the name of the class to redirect.
	 * @param string view to redirect
	 */
	function deleteFile($a_redirect_view = "filesListView")
	{
		die("working here");
		/*  I'M WORKING HERE
		global $ilDB;

		if($_POST['file'] && $_GET['ass_id'])
		{
			foreach ($_POST['file'] as $filename)
			{
				$ilDB->manipulate("DELETE FROM exc_ass_file_order " .
					"WHERE filename = " . $ilDB->quote($filename, 'string') .
					" AND assignment_id = " . $ilDB->quote($_GET['ass_id'], 'integer')
				);
			}
		}
			parent::deleteFile($a_redirect_view);
		*/
	}
}