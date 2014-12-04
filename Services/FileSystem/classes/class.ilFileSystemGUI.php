<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* File System Explorer GUI class
*
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilFileSystemGUI
{
	var $ctrl;

	protected $use_upload_directory = false;

	function ilFileSystemGUI($a_main_directory)
	{
		global $lng, $ilCtrl, $tpl, $ilias;

		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->main_dir = $a_main_directory;
		$this->post_dir_path = false;
		$this->commands = array(
			0 => array(
				"object" => $this,
				"method" => "downloadFile",
				"name" => $lng->txt("download"),
				"int" => true
			),
			1 => array(
				"object" => $this,
				"method" => "confirmDeleteFile",
				"name" => $lng->txt("delete"),
				"allow_dir" => true,
				"int" => true
			),
			2 => array(
				"object" => $this,
				"method" => "unzipFile",
				"name" => $lng->txt("unzip"),
				"int" => true
			),
			3 => array(
				"object" => $this,
				"method" => "renameFileForm",
				"name" => $lng->txt("rename"),
				"allow_dir" => true,
				"int" => true
			),
		);

		$this->file_labels = array();
		$this->label_enable = false;
		$this->ctrl->saveParameter($this, "cdir");
		$lng->loadLanguageModule("content");
		$this->setAllowDirectories(true);
		$this->setAllowDirectoryCreation(true);
		$this->setAllowFileCreation(true);
//echo "<br>main_dir:".$this->main_dir.":";
	}

	/**
	 * Set allow directories
	 *
	 * @param	boolean		allow directories
	 */
	function setAllowDirectories($a_val)
	{
		$this->allow_directories = $a_val;
	}
	
	/**
	 * Get allow directories
	 *
	 * @return	boolean		allow directories
	 */
	function getAllowDirectories()
	{
		return $this->allow_directories;
	}

	/**
	 * Set post dir path
	 *
	 * @param	boolean		post dir path
	 */
	function setPostDirPath($a_val)
	{
		$this->post_dir_path = $a_val;
	}

	/**
	 * Get post dir path
	 *
	 * @return	boolean		post dir path
	 */
	function getPostDirPath()
	{
		return $this->post_dir_path;
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
	 * Set title
	 *
	 * @param	string	title
	 */
	function setTitle($a_val)
	{
		$this->title = $a_val;
	}
	
	/**
	 * Get title
	 *
	 * @return	string	title
	 */
	function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set use upload directory
	 *
	 * @param bool $a_val use upload directory
	 */
	function setUseUploadDirectory($a_val)
	{
		$this->use_upload_directory = $a_val;
	}

	/**
	 * Get use upload directory
	 *
	 * @return bool use upload directory
	 */
	function getUseUploadDirectory()
	{
		return $this->use_upload_directory;
	}
	
	/**
	 * Set performed command
	 *
	 * @param	string	command
	 * @param	array	parameter array
	 */
	protected function setPerformedCommand($command, $pars = "")
	{
		if (!is_array($pars))
		{
			$pars = array();
		}
		$_SESSION["fsys"]["lastcomm"] = array_merge(
			array("cmd" => $command), $pars);
	}
	
	/**
	 * Get performed command
	 *
	 * @return	array	command array
	 */
	public function getLastPerformedCommand()
	{
		$ret = $_SESSION["fsys"]["lastcomm"];
		$_SESSION["fsys"]["lastcomm"] = "none";
		return $ret;
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd("listFiles");

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
	 * Add command
	 */
	function addCommand(&$a_obj, $a_func, $a_name, $a_single = true,
		$a_allow_dir = false)
	{
		$i = count($this->commands);

		$this->commands[$i]["object"] =& $a_obj;
		$this->commands[$i]["method"] = $a_func;
		$this->commands[$i]["name"] = $a_name;
		$this->commands[$i]["single"] = $a_single;
		$this->commands[$i]["allow_dir"] = $a_allow_dir;

		//$this->commands[] = $arr;
	}

	/**
	 * Clear commands
	 */
	function clearCommands()
	{
		$this->commands = array();
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
		if (isset($_POST["file"]))
		{
			// remove ".." items
			foreach ($_POST["file"] as $k => $v)
			{
				if ($_POST["file"][$k] == "..")
				{
					unset($_POST["file"][$k]);
				}
			}
		}
		// check if at least one item is select
		else
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// check if only one item is select, if command does not allow multiple selection
		if (count($_POST["file"]) > 1 && $this->commands[$a_nr]["single"])
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
		}

		$cur_subdir = str_replace(".", "", ilUtil::stripSlashes($_GET["cdir"]));

		// collect files and
		$files = array();
		foreach ($_POST["file"] as $k => $v)
		{
			$file = (!empty($cur_subdir))
				? $this->main_dir."/".$cur_subdir."/".ilUtil::stripSlashes($_POST["file"][$k])
				: $this->main_dir."/".ilUtil::stripSlashes($_POST["file"][$k]);

			// check wether selected item is a directory
			if (@is_dir($file) && !$this->commands[$a_nr]["allow_dir"])
			{
				$this->ilias->raiseError($this->lng->txt("select_a_file"),$this->ilias->error_obj->MESSAGE);
			}
			$file = (!empty($cur_subdir))
				? $cur_subdir."/".ilUtil::stripSlashes($_POST["file"][$k])
				: ilUtil::stripSlashes($_POST["file"][$k]);
			$files[] = $file;
		}

		if ($this->commands[$a_nr]["single"])
		{
			$files = $files[0];
		}

		$obj =& $this->commands[$a_nr]["object"];
		$method = $this->commands[$a_nr]["method"];

		return $obj->$method($files);
	}

	/**
	 * Set allowed directory creation
	 */
	public function setAllowDirectoryCreation($a_val)
	{
		$this->directory_creation = $a_val;
	}

	/**
	 * Get allowed directory creation
	 */
	public function getAllowDirectoryCreation()
	{
		return $this->directory_creation;
	}

	/**
	 * Set allowed file creation
	 */
	public function setAllowFileCreation($a_val)
	{
		$this->file_creation = $a_val;
	}

	/**
	 * Get allowed file creation
	 */
	public function getAllowFileCreation()
	{
		return $this->file_creation;
	}

	/**
	* list files
	*/
	function listFiles()
	{
		global $ilToolbar, $lng, $ilCtrl;
		

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
		
		if ($this->getAllowDirectories() && $this->getAllowDirectoryCreation())
		{
			$ti = new ilTextInputGUI($this->lng->txt("cont_new_dir"), "new_dir");
			$ti->setMaxLength(80);
			$ti->setSize(10);
			$ilToolbar->addInputItem($ti, true);
			$ilToolbar->addFormButton($lng->txt("create"), "createDirectory");
			
			$ilToolbar->addSeparator();
		}
		
		include_once("./Services/Form/classes/class.ilFileInputGUI.php");
		if ($this->getAllowFileCreation())
		{
			$fi = new ilFileInputGUI($this->lng->txt("cont_new_file"), "new_file");
			$fi->setSize(10);
			$ilToolbar->addInputItem($fi, true);
			$ilToolbar->addFormButton($lng->txt("upload"), "uploadFile");
		}
		
		include_once 'Services/FileSystem/classes/class.ilUploadFiles.php';
		if (ilUploadFiles::_getUploadDirectory() && $this->getAllowFileCreation() && $this->getUseUploadDirectory())
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
		include_once("./Services/FileSystem/classes/class.ilFileSystemTableGUI.php");
		$fs_table = new ilFileSystemTableGUI($this, "listFiles", $cur_dir, $cur_subdir,
			$this->label_enable, $this->file_labels, $this->label_header, $this->commands,
			$this->getPostDirPath(), $this->getTableId());
		if ($this->getTitle() != "")
		{
			$fs_table->setTitle($this->getTitle());
		}
		if ($_GET["resetoffset"] == 1)
		{
			$fs_table->resetOffset();
		}
		$this->tpl->setContent($fs_table->getHTML());
	}

	/**
	* list files
	*/
	function renameFileForm()
	{
		global $lng, $ilCtrl;
		
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

		$this->ctrl->setParameter($this, "old_name", ilUtil::stripSlashes($_POST["file"][0]));
			
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		
		// file/dir name
		$ti = new ilTextInputGUI($this->lng->txt("name"), "new_name");
		$ti->setMaxLength(200);
		$ti->setSize(40);
		$ti->setValue(ilUtil::stripSlashes($_POST["file"][0]));
		$form->addItem($ti);
		
		// save and cancel commands
		$form->addCommandButton("renameFile", $lng->txt("rename"));
		$form->addCommandButton("cancelRename", $lng->txt("cancel"));
		$form->setFormAction($ilCtrl->getFormAction($this, "renameFile"));

		if (@is_dir($file))
		{
			$form->setTitle($this->lng->txt("cont_rename_dir"));
		}
		else
		{
			$form->setTitle($this->lng->txt("rename_file"));
		}
		
		$this->tpl->setContent($form->getHTML());
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
			$this->setPerformedCommand("rename_dir", array("old_name" => $_GET["old_name"],
				"new_name" => $new_name));
		}
		else
		{
			ilUtil::sendSuccess($lng->txt("cont_file_renamed"), true);
			$this->setPerformedCommand("rename_file", array("old_name" => $_GET["old_name"],
				"new_name" => $new_name));
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
				$this->setPerformedCommand("create_dir", array("name" => $new_dir));
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

		$tgt_file = null;
		
		if (is_file($_FILES["new_file"]["tmp_name"]))
		{
			$tgt_file = $cur_dir."/".ilUtil::stripSlashes($_FILES["new_file"]["name"]);
			
			move_uploaded_file($_FILES["new_file"]["tmp_name"], $tgt_file);
			
		}
		elseif ($_POST["uploaded_file"])
		{					
			include_once 'Services/FileSystem/classes/class.ilUploadFiles.php';

			// check if the file is in the ftp directory and readable
			if (ilUploadFiles::_checkUploadFile($_POST["uploaded_file"]))
			{
				$tgt_file = $cur_dir."/".ilUtil::stripSlashes($_POST["uploaded_file"]);			
				
				// copy uploaded file to data directory
				ilUploadFiles::_copyUploadFile($_POST["uploaded_file"], $tgt_file);
			}			
		}
		else if (trim($_FILES["new_file"]["name"]) == "")
		{
			ilUtil::sendFailure($lng->txt("cont_enter_a_file"), true);
		}
		
		if($tgt_file && is_file($tgt_file))
		{			
			$unzip = null;
			
			// extract zip?						
			include_once("./Services/Utilities/classes/class.ilMimeTypeUtil.php");					
			if(ilMimeTypeUtil::getMimeType($tgt_file) == "application/zip")
			{
				$this->ctrl->setParameter($this, "upfile", basename($tgt_file));
				$url = $this->ctrl->getLinkTarget($this, "unzipFile");
				$this->ctrl->setParameter($this, "upfile", "");
				
				include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
				$unzip = ilLinkButton::getInstance();
				$unzip->setCaption("unzip");
				$unzip->setUrl($url);
				$unzip = " ".$unzip->render();
			}
			
			ilUtil::sendSuccess($lng->txt("cont_file_created").$unzip, true);
			
			$this->setPerformedCommand("create_file",
				array("name" => substr($tgt_file, strlen($this->main_dir)+1)));		
		}

		$this->ctrl->saveParameter($this, "cdir");

		ilUtil::renameExecutables($this->main_dir);

		$this->ctrl->redirect($this, "listFiles");
	}

	/**
	* Confirm file deletion
	*/
	function confirmDeleteFile()
	{
		global $ilCtrl, $tpl, $lng;

		if (!is_array($_POST["file"]) || count($_POST["file"]) == 0)
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "listFile");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("info_delete_sure"));
			$cgui->setCancel($lng->txt("cancel"), "listFiles");
			$cgui->setConfirm($lng->txt("delete"), "deleteFile");
			
			foreach ($_POST["file"] as $i)
			{
				$cgui->addItem("file[]", $i, $i);
			}
			
			$tpl->setContent($cgui->getHTML());
		}
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
			$this->setPerformedCommand("delete_dir",
				array("name" => ilUtil::stripSlashes($post_file)));
		}
		else
		{
			ilUtil::sendSuccess($lng->txt("cont_file_deleted"), true);
			$this->setPerformedCommand("delete_file",
				array("name" => ilUtil::stripSlashes($post_file)));
		}
		$this->ctrl->redirect($this, "listFiles");
	}

	/**
	* delete object file
	*/
	function unzipFile()
	{
		global $lng;
		
		if (isset($_GET["upfile"]))
		{
			$file = basename($_GET["upfile"]);
		}
		else
		{			
			if (!isset($_POST["file"]))
			{
				$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
			}

			if (count($_POST["file"]) > 1)
			{
				$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
			}
			
			$file = basename($_POST["file"][0]);			
		}

		$cur_subdir = str_replace(".", "", ilUtil::stripSlashes($_GET["cdir"]));
		$cur_dir = (!empty($cur_subdir))
			? $this->main_dir."/".$cur_subdir
			: $this->main_dir;
		$file = $cur_dir."/".ilUtil::stripSlashes($file);

		if (@is_file($file))
		{
			$cur_files = array_keys(ilUtil::getDir($cur_dir));
			
			if ($this->getAllowDirectories())
			{
				ilUtil::unzip($file, true);
			}
			else
			{
				ilUtil::unzip($file, true, true);
			}
			
			$new_files = array_keys(ilUtil::getDir($cur_dir));
			
			$diff = array_diff($new_files, $cur_files);					
			if(sizeof($diff))
			{
				if ($this->getAllowDirectories())
				{
					include_once("./Services/Utilities/classes/class.ilFileUtils.php");
					$new_files = array();
					
					foreach($diff as $new_item)
					{
						if(is_dir($cur_dir."/".$new_item))
						{
							ilFileUtils::recursive_dirscan($cur_dir."/".$new_item, $new_files);
						}
					}
					
					if(is_array($new_files["path"]))
					{						
						foreach($new_files["path"] as $idx => $path)
						{
							$path = substr($path, strlen($this->main_dir)+1);
							$diff[] = $path.$new_files["file"][$idx];							
						}
					}					
				}
				
				$this->setPerformedCommand("unzip_file",
					array("name" => substr($file, strlen($this->main_dir)+1), 						
						"added" => $diff));
			}
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
		
		// validate against files of current directory
		$valid = false;
		foreach(ilUtil::getDir($cur_dir) as $entry)
		{
			if($entry["type"] == "file" &&
				$cur_dir."/".$entry["entry"] == $file)
			{
				$valid = true;
				break;
			}
		}
		
		if (@is_file($file) && !(@is_dir($file)) && $valid)
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
