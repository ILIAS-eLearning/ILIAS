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
	function __construct($a_parent_obj, $a_parent_cmd, $a_cur_dir,
						 $a_cur_subdir, $a_label_enable = false,
						 $a_file_labels, $a_label_header = "", $a_commands = array(),
						 $a_post_dir_path = false, $a_table_id = "")
	{
		global $lng;

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_cur_dir,
			$a_cur_subdir, $a_label_enable,
			$a_file_labels, $a_label_header, $a_commands,
			$a_post_dir_path, $a_table_id, $a_order = true);

		$this->setRowTemplate("tpl.exc_ass_instruction_file_row.html", "Modules/Exercise");

		$this->addCommandButton("saveFilesOrder", $lng->txt("exc_save_order"));
		$this->setDefaultOrderField("order_val");
		$this->setDefaultOrderDirection("desc");
	}

	/**
	 * Get entries
	 * we can pass one parameter to getEntries in fileSystemTableGUI, and then
	 * return an array or send data depending on it. If param, return data and then processes it here with
	 * the data from the database.
	 *
	 */
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

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $ilCtrl;

		$hash = $this->post_dir_path
			? md5($a_set["file"])
			: md5($a_set["entry"]);

		if ($this->has_multi) {
			$this->tpl->setVariable("CHECKBOX_ID", $hash);
		}

		$this->tpl->setVariable("ID", $a_set['order_id']);
		$this->tpl->setVariable("ORDER_VAL", $a_set["order_val"]);

		// label
		if ($this->label_enable) {
			$this->tpl->setCurrentBlock("Label");
			$this->tpl->setVariable("TXT_LABEL", $a_set["label"]);
			$this->tpl->parseCurrentBlock();
		}

		$ilCtrl->setParameter($this->parent_obj, "cdir", $this->cur_subdir);

		if ($a_set["type"] == "dir") {
			$this->tpl->setCurrentBlock("FileLink");
			$ilCtrl->setParameter($this->parent_obj, "newdir", $a_set["entry"]);
			$ilCtrl->setParameter($this->parent_obj, "resetoffset", 1);
			$this->tpl->setVariable("LINK_FILENAME",
				$ilCtrl->getLinkTarget($this->parent_obj, "listFiles"));
			$ilCtrl->setParameter($this->parent_obj, "newdir", "");
			$this->tpl->setVariable("TXT_FILENAME", $a_set["entry"]);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setVariable("ICON", "<img src=\"" .
				ilUtil::getImagePath("icon_cat.svg") . "\">");
			$ilCtrl->setParameter($this->parent_obj, "resetoffset", "");
		} else {
			$this->tpl->setCurrentBlock("File");
			$this->tpl->setVariable("TXT_FILENAME2", $a_set["entry"]);
			$this->tpl->parseCurrentBlock();
		}

		if ($a_set["type"] != "dir") {
			$this->tpl->setVariable("TXT_SIZE", ilUtil::formatSize($a_set["size"]));
		}

		// single item commands
		if (sizeof($this->row_commands) &&
			!($a_set["type"] == "dir" && $a_set["entry"] == "..")
		) {
			$advsel = new ilAdvancedSelectionListGUI();
			foreach ($this->row_commands as $rcom) {
				if ($rcom["allow_dir"] || $a_set["type"] != "dir") {
					$ilCtrl->setParameter($this->parent_obj, "fhsh", $hash);
					$url = $ilCtrl->getLinkTarget($this->parent_obj, $rcom["cmd"]);
					$ilCtrl->setParameter($this->parent_obj, "fhsh", "");

					$advsel->addItem($rcom["caption"], "", $url);
				}
			}
			$this->tpl->setVariable("ACTIONS", $advsel->getHTML());
		}
	}
}