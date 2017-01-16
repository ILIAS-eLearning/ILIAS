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
		$this->setDefaultOrderDirection("desc");
	}

	/**
	 * Add Order Values (new version of getEntries)
	 * @param array $a_entries
	 * @return array items
	 */
	function addOrderValues($a_entries = array())
	{
		global $ilDB;

		$ilDB->setLimit(1,0);
		$items = array();

		foreach ($a_entries as $e)
		{
			$result_order_val = $ilDB->query("
			SELECT id, order_nr
			FROM exc_ass_file_order
			WHERE assignment_id = {$ilDB->quote($_GET['ass_id'], 'integer')}
			AND filename = '".$e['entry']."'
		");

			while($row = $ilDB->fetchAssoc($result_order_val))
			{
				$order_val = (int)$row['order_nr'];
				$order_id = (int)$row['id'];
			}

			$items[$order_val] = array("file" => $e["file"], "entry" => $e["entry"],
				"type" => $e["type"], "label" => $e["label"], "size" => $e["size"],
				"name" => $e["name"], "order_id"=>$order_id, "order_val"=>$order_val);
		}

		return $items;

	}

	/**
	 * Get entries
	 * we can pass one parameter to getEntries in fileSystemTableGUI, and then
	 * return an array or send data depending on it. If param, return data and then processes it here with
	 * the data from the database.
	 *
	 */
	/*
	function getEntries()
	{
		if (is_dir($this->cur_dir))
		{
			$entries = ilUtil::getDir($this->cur_dir);
		}
		else
		{
			$entries = array(array("type" => "dir", "entry" => ".."));
		}

		$items = array();

		foreach ($entries as $e)
		{
			if(($e["entry"] == ".") || ($e["entry"] == ".." && empty($this->cur_subdir)))
			{
				continue;
			}
			$cfile = (!empty($this->cur_subdir))
				? $this->cur_subdir."/".$e["entry"]
				: $e["entry"];

			if ($this->label_enable)
			{
				$label = (is_array($this->file_labels[$cfile]))
					? implode($this->file_labels[$cfile], ", ")
					: "";
			}

						$pref = ($e["type"] == "dir")
							? ( $this->getOrderDirection() != "desc" ? "1_" : "9_")
							: "5_";

			global $ilDB;

			$ilDB->setLimit(1,0);
			$result_order_val = $ilDB->query("
				SELECT id, order_nr
				FROM exc_ass_file_order
				WHERE assignment_id = {$ilDB->quote($_GET['ass_id'], 'integer')}
				AND filename = '".$e['entry']."'
			");

			while($row = $ilDB->fetchAssoc($result_order_val))
			{
				$order_val = (int)$row['order_nr'];
				$order_id = (int)$row['id'];
			}

			$items[$order_val] = array("file" => $cfile, "entry" => $e["entry"],
				"type" => $e["type"], "label" => $label, "size" => $e["size"],
				"name" => $pref.$e["entry"], "order_id"=>$order_id, "order_val"=>$order_val);
		}

		$this->setData($items);
	}
	*/
}