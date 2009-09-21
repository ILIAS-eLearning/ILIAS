<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* File System Explorer GUI class
*
* -> This class should go to Services/FileSystemStorage
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilFileSystemGUI
{
	var $ctrl;

	function ilFileSystemGUI($a_main_directory)
	{
		global $lng, $ilCtrl, $tpl, $ilias;

		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->main_dir = $a_main_directory;
		$this->commands = array();
		$this->file_labels = array();
		$this->label_enable = false;
		$this->ctrl->saveParameter($this, "cdir");
		$lng->loadLanguageModule("content");
//echo "<br>main_dir:".$this->main_dir.":";
	}

	/**
	* Set table id
	*
	* @param	string	table id
	*/
	function setTableId($a_val)
	{
		$this->table_id = $a_val;
	}
	
	/**
	* Get table id
	*
	* @return	string	table id
	*/
	function getTableId()
	{
		return $this->table_id;
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{

			default:
				if (substr($cmd, 0, 11) == "extCommand_")
				{
					$ret =& $this->extCommand(substr($cmd, 11, strlen($cmd) - 11));
				}
				else
				{
					$ret =& $this->$cmd();
				}
				break;
		}

		return $ret;
	}


	/**
	* add command
	*/
	function addCommand(&$a_obj, $a_func, $a_name)
	{
		$i = count($this->commands);

		$this->commands[$i]["object"] =& $a_obj;
		$this->commands[$i]["method"] = $a_func;
		$this->commands[$i]["name"] = $a_name;

		//$this->commands[] = $arr;
	}


	/**
	* label a file
	*/
	function labelFile($a_file, $a_label)
	{
		$this->file_labels[$a_file][] = $a_label;
	}

	/**
	* activate file labels
	*/
	function activateLabels($a_act, $a_label_header)
	{
		$this->label_enable = $a_act;
		$this->label_header = $a_label_header;
	}

	/**
	* call external command
	*/
	function &extCommand($a_nr)
	{
		if (!isset($_POST["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		if (count($_POST["file"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
		}

		if ($_POST["file"][0] == ".." )
		{
			$this->ilias->raiseError($this->lng->txt("select_a_file"),$this->ilias->error_obj->MESSAGE);
		}

		$cur_subdir = str_replace(".", "", ilUtil::stripSlashes($_GET["cdir"]));
		$file = (!empty($cur_subdir))
			? $this->main_dir."/".$cur_subdir."/".ilUtil::stripSlashes($_POST["file"][0])
			: $this->main_dir."/".ilUtil::stripSlashes($_POST["file"][0]);

		// check wether selected item is a directory
		if (@is_dir($file))
		{
			$this->ilias->raiseError($this->lng->txt("select_a_file"),$this->ilias->error_obj->MESSAGE);
		}

		$file = (!empty($cur_subdir))
			? $cur_subdir."/".ilUtil::stripSlashes($_POST["file"][0]) 
			: ilUtil::stripSlashes($_POST["file"][0]);

		$obj =& $this->commands[$a_nr]["object"];
		$method = $this->commands[$a_nr]["method"];


		return $obj->$method($file);
	}


	/**
	* list files
	*/
	function listFiles()
	{
		global $ilToolbar, $lng, $ilCtrl;
		
		// create table
		require_once("./Services/Table/classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI();

		// determine directory
		// FIXME: I have to call stripSlashes here twice, because I could not
		//        determine where the second layer of slashes is added to the
		//        URL Parameter
		$cur_subdir = ilUtil::stripSlashes(ilUtil::stripSlashes($_GET["cdir"]));
		$new_subdir = ilUtil::stripSlashes(ilUtil::stripSlashes($_GET["newdir"]));

		if($new_subdir == "..")
		{
			$cur_subdir = substr($cur_subdir, 0, strrpos($cur_subdir, "/"));
		}
		else
		{
			if (!empty($new_subdir))
			{
				if (!empty($cur_subdir))
				{
					$cur_subdir = $cur_subdir."/".$new_subdir;
				}
				else
				{
					$cur_subdir = $new_subdir;
				}
			}
		}

		$cur_subdir = str_replace("..", "", $cur_subdir);

		$cur_dir = (!empty($cur_subdir))
			? $this->main_dir."/".$cur_subdir
			: $this->main_dir;

		$this->ctrl->setParameter($this, "cdir", $cur_subdir);
		
		// toolbar for adding files/directories
		$ilToolbar->setFormAction($ilCtrl->getFormAction($this), true);
		include_once("./Services/Form/classes/class.ilTextInputGUI.php");
		$ti = new ilTextInputGUI($this->lng->txt("cont_new_dir"), "new_dir");
		$ti->setMaxLength(80);
		$ti->setSize(10);
		$ilToolbar->addInputItem($ti, true);
		$ilToolbar->addFormButton($lng->txt("create"), "createDirectory");
		
		$ilToolbar->addSeparator();
		
		include_once("./Services/Form/classes/class.ilFileInputGUI.php");
		$fi = new ilFileInputGUI($this->lng->txt("cont_new_file"), "new_file");
		$fi->setSize(10);
		$ilToolbar->addInputItem($fi, true);
		$ilToolbar->addFormButton($lng->txt("upload"), "uploadFile");
		
		include_once 'Services/FileSystemStorage/classes/class.ilUploadFiles.php';
		if (ilUploadFiles::_getUploadDirectory())
		{
			$ilToolbar->addSeparator();
			$files = ilUploadFiles::_getUploadFiles();
			$options[""] = $lng->txt("cont_select_from_upload_dir"); 
			foreach($files as $file)
			{
				$file = htmlspecialchars($file, ENT_QUOTES, "utf-8");
				$options[$file] = $file;
			}
			include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
			$si = new ilSelectInputGUI($this->lng->txt("cont_uploaded_file"), "uploaded_file");
			$si->setOptions($options);
			$ilToolbar->addInputItem($si, true);
			$ilToolbar->addFormButton($lng->txt("copy"), "uploadFile");
		}
			
		// load files templates
		//$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.directory.html", false);
		include_once("./Services/FileSystemStorage/classes/class.ilFileSystemTableGUI.php");
		$fs_table = new ilFileSystemTableGUI($this, "listFiles", $cur_dir, $cur_subdir,
			$this->label_enable, $this->file_labels, $this->label_header, $this->commands);
		$fs_table->setId($this->getTableId());
		if ($_GET["resetoffset"] == 1)
		{
			$fs_table->resetOffset();
		}
		$this->tpl->setContent($fs_table->getHTML());
return;
		//
		$this->tpl->addBlockfile("ADM_CONTENT", "files", "tpl.table.html");

		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.directory_row.html", false);

		$num = 0;

		$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$tbl->setTitle($this->lng->txt("cont_files")." ".$cur_subdir);
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));

		/*
		$cols = array("", "", "dir_file", "size");
		$header_params = array("ref_id" => $_GET["ref_id"], "obj_id" => $_GET["obj_id"],
			"cmd" => "listFiles", "hier_id" => $_GET["hier_id"]);
		$tbl->setHeaderVars($cols, $header_params);*/
		//$tbl->setColumnWidth(array("1%", "1%", "60%", "40%"));

		// control
		/*
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);*/		// ???
		//$tbl->setMaxCount(30);		// ???

		if (!$this->label_enable)
		{
			$tbl->setHeaderNames(array("", "", $this->lng->txt("cont_dir_file"),
				$this->lng->txt("cont_size")));
			$this->tpl->setVariable("COLUMN_COUNTS", 4);
			$tbl->setColumnWidth(array("1%", "1%", "60%", "40%"));
			$cols = array("", "", "dir_file", "size");
			$header_params = $this->ctrl->getParameterArray($this, "listFiles");
			/*
			$header_params = array("ref_id" => $_GET["ref_id"], "obj_id" => $_GET["obj_id"],
				"cmd" => "listFiles", "cmdClass" => get_class($this),
				"hier_id" => $_GET["hier_id"]);*/
			$tbl->setHeaderVars($cols, $header_params);
		}
		else
		{
			$tbl->setHeaderNames(array("", "", $this->lng->txt("cont_dir_file"),
				$this->lng->txt("cont_size"), $this->label_header));
			$this->tpl->setVariable("COLUMN_COUNTS", 5);
			$tbl->setColumnWidth(array("1%", "1%", "50%", "25%", "25%"));
			$cols = array("", "", "dir_file", "size", "label");
			$header_params = $this->ctrl->getParameterArray($this, "listFiles");
			/*
			$header_params = array("ref_id" => $_GET["ref_id"], "obj_id" => $_GET["obj_id"],
				"cmd" => "listFiles", "cmdClass" => get_class($this), "hier_id" => $_GET["hier_id"]);*/
			$tbl->setHeaderVars($cols, $header_params);
		}

		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);		// ???

		// delete button
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "deleteFile");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("delete"));
		$this->tpl->parseCurrentBlock();

		// unzip button
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "unzipFile");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("unzip"));
		$this->tpl->parseCurrentBlock();

		// download button
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "downloadFile");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("download"));
		$this->tpl->parseCurrentBlock();

		// rename button
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "renameFileForm");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("rename"));
		$this->tpl->parseCurrentBlock();

		// additional commands
		for ($i=0; $i < count($this->commands); $i++)
		{
			$this->tpl->setCurrentBlock("tbl_action_btn");
			$this->tpl->setVariable("BTN_NAME", "extCommand_".$i);
			$this->tpl->setVariable("BTN_VALUE", $this->commands[$i]["name"]);
			$this->tpl->parseCurrentBlock();
		}


		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("footer");

		if (is_dir($cur_dir))
		{
			$entries = ilUtil::getDir($cur_dir);
		}
		else
		{
			$entries = array(array("type" => "dir", "entry" => ".."));
		}

		//$objs = ilUtil::sortArray($objs, $_GET["sort_by"], $_GET["sort_order"]);
		$tbl->setMaxCount(count($entries));
		$entries = array_slice($entries, $_GET["offset"], $_GET["limit"]);

		$tbl->render();
		if(count($entries) > 0)
		{
			$i=0;
			foreach($entries as $entry)
			{
				if(($entry["entry"] == ".") || ($entry["entry"] == ".." && empty($cur_subdir)))
				{
					continue;
				}

				$cfile = (!empty($cur_subdir))
					? $cur_subdir."/".$entry["entry"]
					: $entry["entry"];

				// label
				if ($this->label_enable)
				{
					$this->tpl->setCurrentBlock("Label");
					if ($this->file_labels[$cfile] != "")
					{
						$this->tpl->setVariable("TXT_LABEL", $this->file_labels[$cfile]);
					}
					else
					{
						$this->tpl->setVariable("TXT_LABEL", "&nbsp;");
					}
					$this->tpl->parseCurrentBlock();
				}

				//$this->tpl->setVariable("ICON", $obj["title"]);
				if($entry["type"] == "dir")
				{
					$this->tpl->setCurrentBlock("FileLink");
					$this->ctrl->setParameter($this, "cdir", $cur_subdir);
					$this->ctrl->setParameter($this, "newdir", $entry["entry"]);
					$this->tpl->setVariable("LINK_FILENAME", $this->ctrl->getLinkTarget($this, "listFiles"));
					$this->tpl->setVariable("TXT_FILENAME", $entry["entry"]);
					$this->tpl->parseCurrentBlock();

					$this->tpl->setVariable("ICON", "<img src=\"".
						ilUtil::getImagePath("icon_cat.gif")."\">");
				}
				else
				{
					$this->tpl->setCurrentBlock("File");
					$this->tpl->setVariable("TXT_FILENAME2", $entry["entry"]);
					$this->tpl->parseCurrentBlock();
				}

				$this->tpl->setCurrentBlock("tbl_content");
				$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
				$this->tpl->setVariable("CSS_ROW", $css_row);

				$this->tpl->setVariable("TXT_SIZE", $entry["size"]);
				$this->tpl->setVariable("CHECKBOX_ID", $entry["entry"]);
				$compare = (!empty($cur_subdir))
					? $cur_subdir."/".$entry["entry"]
					: $entry["entry"];
				$purpose = array();

				$this->tpl->parseCurrentBlock();
			}
		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", 4);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->parseCurrentBlock();
	}

	/**
	* list files
	*/
	function renameFileForm()
	{
		if (!isset($_POST["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		if (count($_POST["file"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
		}

		if (ilUtil::stripSlashes($_POST["file"][0]) == ".." )
		{
			$this->ilias->raiseError($this->lng->txt("select_a_file"),$this->ilias->error_obj->MESSAGE);
		}

		$cur_subdir = str_replace(".", "", ilUtil::stripSlashes($_GET["cdir"]));
		$file = (!empty($cur_subdir))
			? $this->main_dir."/".$cur_subdir."/".ilUtil::stripSlashes($_POST["file"][0])
			: $this->main_dir."/".ilUtil::stripSlashes($_POST["file"][0]);

		// load files templates
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.file_rename.html", false);

		$this->ctrl->setParameter($this, "old_name", ilUtil::stripSlashes($_POST["file"][0]));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "renameFile"));
		if (@is_dir($file))
		{
			$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("cont_rename_dir"));
		}
		else
		{
			$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("rename_file"));
		}
		$this->tpl->setVariable("TXT_NAME", $this->lng->txt("name"));
		$this->tpl->setVariable("VAL_NAME", ilUtil::stripSlashes($_POST["file"][0]));
		$this->tpl->setVariable("CMD_CANCEL", "cancelRename");
		$this->tpl->setVariable("CMD_SUBMIT", "renameFile");
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("rename"));

		$this->tpl->parseCurrentBlock();
	}

	/**
	* rename a file
	*/
	function renameFile()
	{
		global $lng;
		
		$new_name = str_replace("..", "", ilUtil::stripSlashes($_POST["new_name"]));
		$new_name = str_replace("/", "", $new_name);
		if ($new_name == "")
		{
			$this->ilias->raiseError($this->lng->txt("enter_new_name"),$this->ilias->error_obj->MESSAGE);
		}

		$cur_subdir = str_replace(".", "", ilUtil::stripSlashes($_GET["cdir"]));
		$dir = (!empty($cur_subdir))
			? $this->main_dir."/".$cur_subdir."/"
			: $this->main_dir."/";

		rename($dir.ilUtil::stripSlashes($_GET["old_name"]), $dir.$new_name);

		ilUtil::renameExecutables($this->main_dir);
		if (@is_dir($dir.$new_name))
		{
			ilUtil::sendSuccess($lng->txt("cont_dir_renamed"), true);
		}
		else
		{
			ilUtil::sendSuccess($lng->txt("cont_file_renamed"), true);
		}
		$this->ctrl->redirect($this, "listFiles");
	}

	/**
	* cancel renaming a file
	*/
	function cancelRename()
	{
		$this->ctrl->redirect($this, "listFiles");
	}

	/**
	* create directory
	*/
	function createDirectory()
	{
		global $lng;
		
		// determine directory
		$cur_subdir = str_replace(".", "", ilUtil::stripSlashes($_GET["cdir"]));
		$cur_dir = (!empty($cur_subdir))
			? $this->main_dir."/".$cur_subdir
			: $this->main_dir;

		$new_dir = str_replace(".", "", ilUtil::stripSlashes($_POST["new_dir"]));
		$new_dir = str_replace("/", "", $new_dir);

		if (!empty($new_dir))
		{
			ilUtil::makeDir($cur_dir."/".$new_dir);
			if (is_dir($cur_dir."/".$new_dir))
			{
				ilUtil::sendSuccess($lng->txt("cont_dir_created"), true);
			}
		}
		else
		{
			ilUtil::sendFailure($lng->txt("cont_enter_a_dir_name"), true);
		}
		$this->ctrl->saveParameter($this, "cdir");
		$this->ctrl->redirect($this, "listFiles");
	}

	/**
	* upload file
	*/
	function uploadFile()
	{
		global $lng;
		
		// determine directory
		$cur_subdir = str_replace(".", "", ilUtil::stripSlashes($_GET["cdir"]));
		$cur_dir = (!empty($cur_subdir))
			? $this->main_dir."/".$cur_subdir
			: $this->main_dir;


		include_once 'Services/FileSystemStorage/classes/class.ilUploadFiles.php';

		if (is_file($_FILES["new_file"]["tmp_name"]))
		{
			move_uploaded_file($_FILES["new_file"]["tmp_name"],
				$cur_dir."/".ilUtil::stripSlashes($_FILES["new_file"]["name"]));
			if (is_file($cur_dir."/".ilUtil::stripSlashes($_FILES["new_file"]["name"])))
			{
				ilUtil::sendSuccess($lng->txt("cont_file_created"), true);
			}
		}
		elseif ($_POST["uploaded_file"])
		{
			// check if the file is in the ftp directory and readable
			if (ilUploadFiles::_checkUploadFile($_POST["uploaded_file"]))
			{
				// copy uploaded file to data directory
				ilUploadFiles::_copyUploadFile($_POST["uploaded_file"],
					$cur_dir."/".ilUtil::stripSlashes($_POST["uploaded_file"]));
			}
			if (is_file($cur_dir."/".ilUtil::stripSlashes($_POST["uploaded_file"])))
			{
				ilUtil::sendSuccess($lng->txt("cont_file_created"), true);
			}
		}
		else if (trim($_FILES["new_file"]["name"]) == "")
		{
			ilUtil::sendFailure($lng->txt("cont_enter_a_file"), true);
		}

		$this->ctrl->saveParameter($this, "cdir");

		ilUtil::renameExecutables($this->main_dir);

		$this->ctrl->redirect($this, "listFiles");
	}


	/**
	* delete object file
	*/
	function deleteFile()
	{
		global $lng;
		
		if (!isset($_POST["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		
		foreach ($_POST["file"] as $post_file)
		{
			if (ilUtil::stripSlashes($post_file) == "..")
			{
				$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
				break;
			}

			$cur_subdir = str_replace(".", "", ilUtil::stripSlashes($_GET["cdir"]));
			$cur_dir = (!empty($cur_subdir))
				? $this->main_dir."/".$cur_subdir
				: $this->main_dir;
			$file = $cur_dir."/".ilUtil::stripSlashes($post_file);

			if (@is_file($file))
			{
				unlink($file);
			}

			if (@is_dir($file))
			{
				$is_dir = true;
				ilUtil::delDir($file);
			}
		}

		$this->ctrl->saveParameter($this, "cdir");
		if ($is_dir)
		{
			ilUtil::sendSuccess($lng->txt("cont_dir_deleted"), true);
		}
		else
		{
			ilUtil::sendSuccess($lng->txt("cont_file_deleted"), true);
		}
		$this->ctrl->redirect($this, "listFiles");
	}

	/**
	* delete object file
	*/
	function unzipFile()
	{
		global $lng;
		
		if (!isset($_POST["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		if (count($_POST["file"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
		}

		$cur_subdir = str_replace(".", "", ilUtil::stripSlashes($_GET["cdir"]));
		$cur_dir = (!empty($cur_subdir))
			? $this->main_dir."/".$cur_subdir
			: $this->main_dir;
		$file = $cur_dir."/".ilUtil::stripSlashes($_POST["file"][0]);

		if (@is_file($file))
		{
			ilUtil::unzip($file, true);
		}

		ilUtil::renameExecutables($this->main_dir);

		$this->ctrl->saveParameter($this, "cdir");
		ilUtil::sendSuccess($lng->txt("cont_file_unzipped"), true);
		$this->ctrl->redirect($this, "listFiles");
	}

	/**
	* delete object file
	*/
	function downloadFile()
	{
		if (!isset($_POST["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		if (count($_POST["file"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
		}

		$cur_subdir = str_replace(".", "", ilUtil::stripSlashes($_GET["cdir"]));
		$cur_dir = (!empty($cur_subdir))
			? $this->main_dir."/".$cur_subdir
			: $this->main_dir;
		$file = $cur_dir."/".$_POST["file"][0];

		if (@is_file($file) && !(@is_dir($file)))
		{
			ilUtil::deliverFile($file, $_POST["file"][0]);
			exit;
		}
		else
		{
			$this->ctrl->saveParameter($this, "cdir");
			$this->ctrl->redirect($this, "listFiles");
		}
	}

	/**
	* get tabs
	*/
	function getTabs(&$tabs_gui)
	{
		global $ilCtrl;
		
		$ilCtrl->setParameter($this, "resetoffset", 1);
		$tabs_gui->addTarget("cont_list_files",
			$this->ctrl->getLinkTarget($this, "listFiles"), "listFiles",
			get_class($this));
		$ilCtrl->setParameter($this, "resetoffset", "");
	}

}
?>
