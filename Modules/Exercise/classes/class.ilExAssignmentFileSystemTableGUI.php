<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once('./Services/FileSystem/classes/class.ilFileSystemTableGUI.php');

/**
 * File System Explorer GUI class
 *
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 */
class ilExAssignmentFileSystemTableGUI extends ilFileSystemTableGUI
{
	//this property will define if the table needs order column.
	protected $add_order_column = true;
	protected $child_class_name = 'ilExAssignmentFileSystemTableGUI';

	function __construct($a_parent_obj, $a_parent_cmd, $a_cur_dir,
						 $a_cur_subdir, $a_label_enable = false,
						 $a_file_labels, $a_label_header = "", $a_commands = array(),
						 $a_post_dir_path = false, $a_table_id = "")
	{
		global $lng;

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_cur_dir,
			$a_cur_subdir, $a_label_enable,
			$a_file_labels, $a_label_header, $a_commands,
			$a_post_dir_path, $a_table_id);

		//default template with order block
		//$this->setRowTemplate("tpl.exc_ass_instruction_file_row.html", "Modules/Exercise");
		$this->addCommandButton("saveFilesOrder", $lng->txt("exc_save_order"));
		$this->setDefaultOrderField("order_val");
		$this->setDefaultOrderDirection("asc");
	}

	/**
	 * Add Order Values (extension of ilFilesystemgui getEntries)
	 * @param array $a_entries
	 * @return array items
	 */
	function instructionFileAddOrder($a_entries = array())
	{
		return ilExAssignment::instructionFileAddOrder($a_entries, $_GET['ass_id']);
	}
}