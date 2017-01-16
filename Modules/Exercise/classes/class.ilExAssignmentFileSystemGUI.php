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
		ilExAssignment::insertOrder();
		parent::uploadFile($a_ctr_redirect);

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
		if($_GET["ass_id"])
		{
			ilExAssignment::deleteOrder($_GET['ass_id'], $_POST["file"]);

			//TODO this redirection must be improved and this param deleted
			parent::deleteFile($a_redirect_view);
		}
	}
}