<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for file system
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesFileSystemStorage
*/
class ilFileSystemTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_cur_dir, 
		$a_cur_subdir, $a_label_enable = false,
		$a_file_labels, $a_label_header = "", $a_commands = array(),
		$a_post_dir_path = false, $a_table_id = "")
	{
		global $ilCtrl, $lng, $ilAccess, $lng;

		$this->setId($a_table_id);
		$this->cur_dir = $a_cur_dir;
		$this->cur_subdir = $a_cur_subdir;
		$this->label_enable = $a_label_enable;
		$this->label_header = $a_label_header;
		$this->file_labels = $a_file_labels;
		$this->post_dir_path = $a_post_dir_path;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setTitle($lng->txt("cont_files")." ".$this->cur_subdir);
		$this->setSelectAllCheckbox("file[]");
		
		$this->addColumn("", "", "1", true);
		$this->addColumn("", "", "1");
		if ($this->label_enable)
		{
			$this->addColumn($lng->txt("cont_dir_file"), "name", "50%");
			$this->addColumn($lng->txt("cont_size"), "size", "20%");
			$this->addColumn($this->label_header, "", "30%");
		}
		else
		{
			$this->addColumn($lng->txt("cont_dir_file"), "name", "60%");
			$this->addColumn($lng->txt("cont_size"), "size", "40%");
		}
		
		$this->setDefaultOrderField("name");
		$this->setDefaultOrderDirection("asc");
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.directory_row.html",
			"Services/FileSystem");
		$this->setEnableTitle(true);

		//$this->addMultiCommand("downloadFile", $lng->txt("download"));
		//$this->addMultiCommand("confirmDeleteFile", $lng->txt("delete"));
		//$this->addMultiCommand("unzipFile", $lng->txt("unzip"));
		//$this->addMultiCommand("renameFileForm", $lng->txt("rename"));
		for ($i=0; $i < count($a_commands); $i++)
		{
			if ($a_commands["int"])
			{
				$this->addMultiCommand($a_commands[$i]["method"],
					$a_commands[$i]["name"]);
			}
			else
			{
				$this->addMultiCommand("extCommand_".$i, $a_commands[$i]["name"]);
			}
		}
	}
	
	function numericOrdering($a_field)
	{
		if ($a_field == "size")
		{
			return true;
		}
		return false;
	}

	/**
	* Get data just before output
	*/
	function prepareOutput()
	{
		$this->determineOffsetAndOrder(true);
		$this->getEntries();
	}
	
	
	/**
	* Get entries
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
//var_dump($entries);
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
			$items[] = array("file" => $cfile, "entry" => $e["entry"],
				"type" => $e["type"], "label" => $label, "size" => $e["size"],
				"name" => $pref.$e["entry"]);
		}

		$this->setData($items);
	}
	
	
	/**
	* Fill table row
	*/
	protected function fillRow($a_set)
	{
		global $ilCtrl;

		if ($a_set["entry"] != "..")
		{
			if ($this->post_dir_path)
			{
				$this->tpl->setVariable("CHECKBOX_ID", $a_set["file"]);
			}
			else
			{
				$this->tpl->setVariable("CHECKBOX_ID", $a_set["entry"]);
			}
		}

		// label
		if ($this->label_enable)
		{
			$this->tpl->setCurrentBlock("Label");
			$this->tpl->setVariable("TXT_LABEL", $a_set["label"]);
			$this->tpl->parseCurrentBlock();
		}

		//$this->tpl->setVariable("ICON", $obj["title"]);
		if($a_set["type"] == "dir")
		{
			$this->tpl->setCurrentBlock("FileLink");
			$ilCtrl->setParameter($this->parent_obj, "cdir", $this->cur_subdir);
			$ilCtrl->setParameter($this->parent_obj, "newdir", $a_set["entry"]);
			$ilCtrl->setParameter($this->parent_obj, "resetoffset", 1);
			$this->tpl->setVariable("LINK_FILENAME",
				$ilCtrl->getLinkTarget($this->parent_obj, "listFiles"));
			$this->tpl->setVariable("TXT_FILENAME", $a_set["entry"]);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setVariable("ICON", "<img src=\"".
				ilUtil::getImagePath("icon_cat.svg")."\">");
			$ilCtrl->setParameter($this->parent_obj, "resetoffset", "");
		}
		else
		{
			$this->tpl->setCurrentBlock("File");
			$this->tpl->setVariable("TXT_FILENAME2", $a_set["entry"]);
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setVariable("TXT_SIZE", $a_set["size"]);

		$ilCtrl->setParameter($this->parent_obj, "cdir", $_GET["cdir"]);
		$ilCtrl->setParameter($this->parent_obj, "newdir", $_GET["newdir"]);
	}

}
?>
