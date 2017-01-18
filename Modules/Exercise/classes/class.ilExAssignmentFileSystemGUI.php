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

	public function listFiles($a_class_table_gui = "")
	{
		parent::listFiles('ilExAssignmentFileSystemTableGUI');
	}

	/**
	 * Insert into database the file order and update the file.
	 *
	 * @param string view to redirect
	 */
	public function uploadFile()
	{
		ilExAssignment::insertOrder();
		parent::uploadFile();

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
			$ilCtrl->redirect($this, "listFiles");
		}
	}

	/**
	 * delete object file
	 * we can pass one parameter to deleteFile in fileSystemGUI, that contains the name of the class to redirect.
	 * @param string view to redirect
	 */
	function deleteFile()
	{
		if($_GET["ass_id"])
		{
			ilExAssignment::deleteOrder($_GET['ass_id'], $_POST["file"]);

			//TODO this redirection must be improved and this param deleted
			parent::deleteFile();
		}
	}

	/**
	 * Rename File name
	 */
	function renameFile()
	{
		if($_GET["ass_id"])
		{
			$new_name = str_replace("..", "", ilUtil::stripSlashes($_POST["new_name"]));
			$old_name = str_replace("/", "", $_GET["old_name"]);

			if($new_name != $old_name)
			{
				ilExAssignment::renameInstructionFile($old_name, $new_name);
			}
		}
		parent::renameFile();
	}

}