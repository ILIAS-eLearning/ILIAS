<?php

/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* GUI class for personal bookmark administration. It manages folders and bookmarks
* with the help of the two corresponding core classes ilBookmarkFolder and ilBookmark.
* Their methods are called in this User Interface class.
*
* @author Alex Killing <alex.killing@gmx.de>
* @author Manfred Thaler <manfred.thaler@endo7.com>
* @version $Id$
*
* @ilCtrl_Calls ilBookmarkAdministrationGUI:
*/

require_once ("./Services/PersonalDesktop/classes/class.ilBookmarkExplorer.php");
require_once ("./Services/PersonalDesktop/classes/class.ilBookmarkFolder.php");
require_once ("./Services/PersonalDesktop/classes/class.ilBookmark.php");
require_once ("./Services/Table/classes/class.ilTableGUI.php");

class ilBookmarkAdministrationGUI
{
	/**
	* User Id
	* @var integer
	* @access public
	*/
	var $user_id;

	/**
	* ilias object
	* @var object ilias
	* @access public
	*/
	var $ilias;
	var $tpl;
	var $lng;

	var $tree;
	var $id;
	var $data;
	var $textwidth=100;

	/**
	* Constructor
	* @access	public
	* @param	integer		user_id (optional)
	*/
	function ilBookmarkAdministrationGUI()
	{
		global $ilias, $tpl, $lng, $ilCtrl;
		//print_r($_SESSION["error_post_vars"]);
		// if no bookmark folder id is given, take dummy root node id (that is 1)
		$this->id = (empty($_GET["bmf_id"]))
			? $bmf_id = 1
			: $_GET["bmf_id"];

		// initiate variables
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->ctrl =& $ilCtrl;
		$this->ctrl->setParameter($this, "bmf_id", $this->id);
		$this->user_id = $_SESSION["AccountId"];

		$this->tree = new ilTree($_SESSION["AccountId"]);
		$this->tree->setTableNames('bookmark_tree','bookmark_data');
		$this->root_id = $this->tree->readRootId();
		// set current bookmark view mode
		//if (!empty($_GET["set_mode"]))
		//{
		//	$this->ilias->account->writePref("il_bkm_mode", $_GET["set_mode"]);
		//}
		//$this->mode = $this->ilias->account->getPref("il_bkm_mode");
		$this->mode = "tree";
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass();

		switch($next_class)
		{
			default:
				$cmd = $this->ctrl->getCmd("view");
				$this->displayHeader();
				$this->$cmd();
				if ($this->getMode() == 'tree')
				{
					$this->explorer();
				}
				break;
		}
		$this->tpl->show(true);
		return true;
	}
	function executeAction()
	{
		switch($_POST["selected_cmd"])
		{
			case "delete":
				$this->delete();
				break;
			case "export":
				$this->export();
				break;
			case "sendmail":
				$this->sendmail();
				break;
			default:
				$this->view();
				break;
		}
		return true;
	}

	/**
	* return display mode
	* flat or tree
	*/
	function getMode() {
		return $this->mode;
	}

	/**
	* output explorer tree with bookmark folders
	*/
	function explorer()
	{
		$this->tpl->setCurrentBlock("left_column");
		$this->tpl->addBlockFile("LEFT_CONTENT", "adm_tree_content", "tpl.bookmark_explorer.html");
		$exp = new ilBookmarkExplorer($this->ctrl->getLinkTarget($this),$_SESSION["AccountId"]);
		$exp->setAllowedTypes(array('dum','bmf'));
		$exp->setTargetGet("bmf_id");
		$exp->setSessionExpandVariable('mexpand');
		$exp->setExpand($this->id);
		$this->ctrl->setParameter($this, "bmf_id", $this->id);
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this));
		if ($_GET["mexpand"] == "")
		{
			$mtree = new ilTree($_SESSION["AccountId"]);
			$mtree->setTableNames('bookmark_tree','bookmark_data');
			$expanded = $mtree->readRootId();
		}
		else
		{
			$expanded = $_GET["mexpand"];
		}

		$exp->setExpand($expanded);
		$exp->highlightNode($_GET["bmf_id"]);

		// build html-output
		$exp->setOutput(0);
		$exp->highlightNode($this->id);
		$output = $exp->getOutput();

		$this->tpl->setCurrentBlock("adm_tree_content");
		$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("bookmarks"));
		$this->ctrl->setParameter($this, "bmf_id", 1);
		$this->tpl->setVariable("LINK_EXPLORER_HEADER",$this->ctrl->getLinkTarget($this));

		$this->tpl->setVariable("EXPLORER",$output);
		$this->tpl->parseCurrentBlock();
	}


	/**
	* display header and locator
	*/
	function displayHeader()
	{
		// output locator
		$this->displayLocator();

		// output message
		if($this->message)
		{
			ilUtil::sendInfo($this->message);
		}
		ilUtil::infoPanel();

		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
			$this->lng->txt("personal_desktop"));
		$this->tpl->setTitle($this->lng->txt("personal_desktop"));
	}

	/*
	* display content of bookmark folder
	*/
	function view()
	{
		global $tree, $ilCtrl;

		include_once("classes/class.ilFrameTargetInfo.php");

		$mtree = new ilTree($_SESSION["AccountId"]);
		$mtree->setTableNames('bookmark_tree','bookmark_data');

		$objects = ilBookmarkFolder::getObjects($this->id);

		$s_mode = ($this->mode == "tree")
				? "flat"
				: "tree";

//		$this->tpl->setTreeFlatIcon($this->ctrl->getLinkTarget($this)."&set_mode=".$s_mode,
//			$s_mode);

		include_once 'Services/PersonalDesktop/classes/class.ilBookmarkAdministrationTableGUI.php';
		$table = new ilBookmarkAdministrationTableGUI($this);

		/*
		// return to parent folder
		// disabled
		if ($this->id != $mtree->readRootId() || $this->id =="")
		{
			$ilCtrl->setParameter($this, "bmf_id", $mtree->getParentId($this->id));
			$objects = array_merge
			(
				array
				(
					array
					(
						"title" => "..",
						"target" => $ilCtrl->getLinkTarget($this),
						"type" => 'parent',
						"obj_id" => $mtree->getParentId($this->id),
					)
				),
				$objects
			);
		}
		*/
		$table->setData($objects);
		$this->tpl->setVariable("ADM_CONTENT", $table->getHTML());
	}

	/**
	* output a cell in object list
	*/
	function add_cell($val, $link = "")
	{
		if (!empty($link))
		{
			$this->tpl->setCurrentBlock("begin_link");
			$this->tpl->setVariable("LINK_TARGET", $link);
			$this->tpl->parseCurrentBlock();
			$this->tpl->touchBlock("end_link");
		}

		$this->tpl->setCurrentBlock("text");
		$this->tpl->setVariable("TEXT_CONTENT", $val);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("table_cell");
		$this->tpl->parseCurrentBlock();
	}

	/**
	* display locator
	*/
	function displayLocator()
	{
		global $lng;

		if (empty($this->id))
		{
			return;
		}

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html", "Services/Locator");

		$path = $this->tree->getPathFull($this->id);
//print_r($path);
		$modifier = 1;

return;
		$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));
		$this->tpl->touchBlock("locator_separator");
		$this->tpl->touchBlock("locator_item");
		//$this->tpl->setCurrentBlock("locator_item");
		//$this->tpl->setVariable("ITEM", $this->lng->txt("personal_desktop"));
		//$this->tpl->setVariable("LINK_ITEM", $this->ctrl->getLinkTargetByClass("ilpersonaldesktopgui"));
		//$this->tpl->setVariable("LINK_TARGET","target=\"".
		//	ilFrameTargetInfo::_getFrame("MainContent")."\"");
		//$this->tpl->parseCurrentBlock();

		foreach ($path as $key => $row)
		{
			if ($key < count($path)-$modifier)
			{
				$this->tpl->touchBlock("locator_separator");
			}

			$this->tpl->setCurrentBlock("locator_item");
			$title = ($row["child"] == 1) ?
				$lng->txt("bookmarks") :
				$row["title"];
			$this->tpl->setVariable("ITEM", $title);
			$this->ctrl->setParameter($this, "bmf_id", $row["child"]);
			$this->tpl->setVariable("LINK_ITEM",
				$this->ctrl->getLinkTarget($this));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("locator");

		$this->tpl->parseCurrentBlock();
	}

	/**
	* new form
	*/
	function newForm($type)
	{
		if (!$type)
			$type = $_POST["type"];
		switch($type)
		{
			case "bmf":
				$this->newFormBookmarkFolder();
				break;

			case "bm":
				$this->newFormBookmark();
				break;
		}
	}

	/**
	* display new bookmark folder form
	*/
	function newFormBookmarkFolder()
	{
		$form = $this->initFormBookmarkFolder();
		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}

	/**
	* init bookmark folder create/edit form
	* @param string form action type; valid values: createBookmark, updateBookmark
	*/
	private function initFormBookmarkFolder($action = 'createBookmarkFolder')
	{
		global $lng, $ilCtrl, $ilUser;

		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setTopAnchor("bookmark_top");
		
		$form->setTitle($lng->txt("bookmark_folder_new"));
		
		if ($action == 'updateBookmarkFolder')
		{
			$ilCtrl->setParameter($this, 'bmf_id', $this->id);
			$ilCtrl->setParameter($this, 'obj_id', $_GET["obj_id"]);
		}
		
		$hash = ($ilUser->prefs["screen_reader_optimization"])
			? "bookmark_top"
			: "";

		$form->setFormAction($ilCtrl->getFormAction($this, $action, $hash));
		
		$ilCtrl->clearParameters($this);

		// title
		$prop = new ilTextInputGUI($lng->txt("title"), "title");
		$prop->setRequired(true);
		$form->addItem($prop);
		
		// buttons
		$form->addCommandButton($action, $lng->txt('save'));
		$form->addCommandButton('cancel', $lng->txt('cancel'));
		return $form;
	}

	/**
	* display edit bookmark folder form
	*/
	function editFormBookmarkFolder()
	{
		$bmf = new ilBookmarkFolder($_GET["obj_id"]);
		$form = $this->initFormBookmarkFolder('updateBookmarkFolder', $this->id);
		$form->setValuesByArray
		(
			array
			(
				"title" => $this->get_last("title", $bmf->getTitle()),
				"obj_id" => $_GET["obj_id"],
			)
		);
		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}


	/**
	* init Bookmark create/edit form
	* @param string form action type; valid values: createBookmark, updateBookmark
	*/
	private function initFormBookmark($action = 'createBookmark')
	{
		global $lng, $ilCtrl, $ilUser;

		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setTopAnchor("bookmark_top");
		
		$form->setTitle($lng->txt("bookmark_new"));
		
		if ($action == 'updateBookmark')
		{
			$ilCtrl->setParameter($this, 'bmf_id', $this->id);
			$ilCtrl->setParameter($this, 'obj_id', $_GET["obj_id"]);
		}
		
		$hash = ($ilUser->prefs["screen_reader_optimization"])
			? "bookmark_top"
			: "";

		$form->setFormAction($ilCtrl->getFormAction($this, $action, $hash));
		$ilCtrl->clearParameters($this);
		// title
		$prop = new ilTextInputGUI($lng->txt("title"), "title");
		$prop->setRequired(true);
		$form->addItem($prop);
		
		// description
		$prop = new ilTextAreaInputGUI($lng->txt('description'), 'description');
		$form->addItem($prop);
		
		// target link
		$prop = new ilTextInputGUI($lng->txt('bookmark_target'), 'target');
		$prop->setRequired(true);
		$form->addItem($prop);
		
		// buttons
		$form->addCommandButton($action, $lng->txt('save'));
		$form->addCommandButton('cancel', $lng->txt('cancel'));
		
		// keep imports? 
		/*
		$this->tpl->setCurrentBlock('bkm_import');
		$this->tpl->setVariable("TXT_IMPORT_BKM", $this->lng->txt("bkm_import"));
		$this->tpl->setVariable("TXT_FILE", $this->lng->txt("file_add"));
		$this->tpl->setVariable("TXT_IMPORT", $this->lng->txt("import"));
		$this->tpl->parseCurrentBlock();
		//vd($_POST);
		*/
		
		return $form;
	}
	
	/**
	* display new bookmark form
	*/
	function newFormBookmark()
	{
		$form = $this->initFormBookmark();
		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}


	/**
	* get stored post var in case of an error/warning otherwise return passed value
	*/
	function get_last($a_var, $a_value)
	{
		return 	(!empty($_POST[$a_var])) ?
			ilUtil::prepareFormOutput(($_POST[$a_var]),true) :
			ilUtil::prepareFormOutput($a_value);
	}

	/**
	* display edit bookmark form
	*/
	function editFormBookmark()
	{
		global $lng, $ilCtrl;
		$form = $this->initFormBookmark('updateBookmark');
		$bookmark = new ilBookmark($_GET["obj_id"]);
		$form->setValuesByArray
		(
			array
			(
				"title" => $bookmark->getTitle(),
				"target" => $bookmark->getTarget(),
				"description" => $bookmark->getDescription(),
				"obj_id" => $_GET["obj_id"],
			)
		);
		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}


	/**
	* create new bookmark folder in db
	*/
	function createBookmarkFolder()
	{
		// check title
		if (empty($_POST["title"]))
		{
			ilUtil::sendFailure($this->lng->txt("please_enter_title"));
			$this->newFormBookmarkFolder();
		}
		else
		{
			// create bookmark folder
			$bmf = new ilBookmarkFolder();
			$bmf->setTitle(ilUtil::stripSlashes($_POST["title"]));
			$bmf->setParent($this->id);
			$bmf->create();

			$this->view();
		}
	}


	/**
	* update bookmark folder
	*/
	function updateBookmarkFolder()
	{
		// check title
		if (empty($_POST["title"]))
		{
			ilUtil::sendFailure($this->lng->txt("please_enter_title"));
			$this->editFormBookmarkFolder();
		}
		else
		{
			// update bookmark folder
			$bmf = new ilBookmarkFolder($_GET["obj_id"]);
			$bmf->setTitle(ilUtil::stripSlashes($_POST["title"]));
			$bmf->update();

			$this->view();
		}
	}


	/**
	* create new bookmark in db
	*/
	function createBookmark()
	{
		// check title and target
		if (empty($_POST["title"]))
		{
			ilUtil::sendFailure($this->lng->txt("please_enter_title"));
			$this->newFormBookmark();
		}
		else if (empty($_POST["target"]))
		{
			ilUtil::sendFailure($this->lng->txt("please_enter_target"));
			$this->newFormBookmark();
		}
		else
		{
			// create bookmark
			$bm = new ilBookmark();
			$bm->setTitle(ilUtil::stripSlashes($_POST["title"]));
			$bm->setDescription(ilUtil::stripSlashes($_POST["description"]));
			$bm->setTarget(ilUtil::stripSlashes($_POST["target"]));
			$bm->setParent($this->id);
			$bm->create();

			$this->view();
		}
	}

	/**
	* update bookmark in db
	*/
	function updateBookmark()
	{
		// check title and target
		if (empty($_POST["title"]))
		{
			ilUtil::sendFailure($this->lng->txt("please_enter_title"));
			$this->editFormBookmark();
		}
		else if (empty($_POST["target"]))
		{
			ilUtil::sendFailure($this->lng->txt("please_enter_target"));
			$this->editFormBookmark();
		}
		else
		{
			// update bookmark
			$bm = new ilBookmark($_GET["obj_id"]);
			$bm->setTitle(ilUtil::stripSlashes($_POST["title"]));
			$bm->setTarget(ilUtil::stripSlashes($_POST["target"]));
			$bm->setDescription(ilUtil::stripSlashes($_POST["description"]));
			$bm->update();

			$this->view();
		}
	}

	/**
	* export bookmarks
	*/
	function export($deliver=true)
	{
		if (!isset($_POST["bm_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		$export_ids=array();
		foreach($_POST["bm_id"] as $id)
		{
			list($type, $obj_id) = explode(":", $id);
			$export_ids[]=$obj_id;
		}
		require_once ("./Services/PersonalDesktop/classes/class.ilBookmarkImportExport.php");
		$html_content=ilBookmarkImportExport::_exportBookmark ($export_ids,true,
			$this->lng->txt("bookmarks_of")." ".$this->ilias->account->getFullname());
		if ($deliver)
		{
			ilUtil::deliverData($html_content, 'bookmarks.html', "application/save", $charset = "");
		}
		else
		{
			return $html_content;
		}
	}
	/**
	* send  bookmarks as attachment
	*/
	function sendmail()
	{
		global $ilUser;
		include_once 'classes/class.ilFileDataMail.php';
		require_once "Services/Mail/classes/class.ilFormatMail.php";
		$mfile = new ilFileDataMail($ilUser->getId());
		$umail = new ilFormatMail($ilUser->getId());


		$html_content=$this->export(false);
		$tempfile=ilUtil::ilTempnam();
		$fp=fopen($tempfile,'w');
		fwrite($fp, $html_content);
		fclose($fp);
		$filename='bookmarks.html';
		$mfile->copyAttachmentFile($tempfile,$filename);
		$umail->savePostData($ilUser->getId(),array($filename),
						 '','','','','',
						 '',
						 '', 0);
		ilUtil::redirect('ilias.php?baseClass=ilMailGUI&type=attach');
	}
	/**
	* display deletion conformation screen
	*/
	function delete()
	{
		if (!isset($_POST["bm_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "objects", "tpl.obj_confirm.html");

		ilUtil::sendQuestion($this->lng->txt("info_delete_sure"));
		$this->ctrl->setParameter($this, "bmf_id", $this->id);
		$this->tpl->setVariable("FORMACTION",
			$this->ctrl->getFormAction($this));

		// output table header
		$cols = array("type", "title");
		foreach ($cols as $key)
		{
			$this->tpl->setCurrentBlock("table_header");
			$this->tpl->setVariable("TEXT",$this->lng->txt($key));
			$this->tpl->parseCurrentBlock();
		}

		$_SESSION["saved_post"] = $_POST["bm_id"];

		foreach($_POST["bm_id"] as $obj_id)
		{
			$type = ilBookmark::_getTypeOfId($obj_id);
			switch($type)
			{
				case "bmf":
					$BookmarkFolder = new ilBookmarkFolder($obj_id);
					$title = $BookmarkFolder->getTitle();
					$target = "";
					unset($BookmarkFolder);
					break;

				case "bm":
					$Bookmark = new ilBookmark($obj_id);
					$title = $Bookmark->getTitle();
					$target = $Bookmark->getTarget();
					unset($Bookmark);
					break;
			}

			// output type icon
			$this->tpl->setCurrentBlock("table_cell");
			$this->tpl->setVariable("TEXT_CONTENT",ilUtil::getImageTagByType($type, $this->tpl->tplPath));
			$this->tpl->parseCurrentBlock();

			// output title
			$this->tpl->setCurrentBlock("table_cell");
			$this->tpl->setVariable("TEXT_CONTENT",ilUtil::prepareFormOutput($title));

			// output target
			if ($target)
			{
				$this->tpl->setVariable("DESC",ilUtil::prepareFormOutput(ilUtil::shortenText(
					$target,$this->textwidth, true)));
			}
			$this->tpl->parseCurrentBlock();

			// output table row
			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->parseCurrentBlock();
		}

		// cancel and confirm button
		$buttons = array( "cancel"  => $this->lng->txt("cancel"),
			"confirm"  => $this->lng->txt("confirm"));

		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		foreach($buttons as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* cancel deletion,insert, update
	*/
	function cancel()
	{
		session_unregister("saved_post");
		$this->view();
	}

	/**
	* deletion confirmed -> delete folders / bookmarks
	*/
	function confirm()
	{
		global $tree, $rbacsystem, $rbacadmin;
		// AT LEAST ONE OBJECT HAS TO BE CHOSEN.
		if (!isset($_SESSION["saved_post"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// FOR ALL SELECTED OBJECTS
		foreach ($_SESSION["saved_post"] as $id)
		{
			$type = ilBookmark::_getTypeOfId($id);

			// get node data and subtree nodes
			if ($this->tree->isInTree($id))
			{
				$node_data = $this->tree->getNodeData($id);
				$subtree_nodes = $this->tree->getSubTree($node_data);
			}
			else
			{
				continue;
			}

			// delete tree
			$this->tree->deleteTree($node_data);

			// delete objects of subtree nodes
			foreach ($subtree_nodes as $node)
			{
				switch ($node["type"])
				{
					case "bmf":
						$BookmarkFolder = new ilBookmarkFolder($node["obj_id"]);
						$BookmarkFolder->delete();
						break;

					case "bm":
						$Bookmark = new ilBookmark($node["obj_id"]);
						$Bookmark->delete();
						break;
				}
			}
		}

		// Feedback
		ilUtil::sendSuccess($this->lng->txt("info_deleted"),true);

		$this->view();
	}


	/**
	* display subobject addition selection
	*/
	function showPossibleSubObjects()
	{
		$actions = array(
				"delete"=>$this->lng->txt("delete"),
				"export"=>$this->lng->txt("export"),
				"sendmail"=>$this->lng->txt("bkm_sendmail"),
		);

		$subobj = array("bm", "bmf");
		
		if (is_array($subobj))
		{
			//build form
			$opts = ilUtil::formSelect("","type",$subobj);

			$this->tpl->setCurrentBlock("add_object");
			$this->tpl->setVariable("COLUMN_COUNTS", 7);
			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("BTN_NAME", "newForm");
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("TPLPATH",$this->tpl->tplPath);

		$this->tpl->setCurrentBlock("tbl_action_select");
		$this->tpl->setVariable("SELECT_ACTION",ilUtil::formSelect($_SESSION["error_post_vars"]['action'],"action",$actions,false,true));
		$this->tpl->setVariable("BTN_NAME","executeAction");
		$this->tpl->setVariable("BTN_VALUE",$this->lng->txt("execute"));

		/*
		$this->tpl->setVariable("BTN_NAME","delete");
		$this->tpl->setVariable("BTN_VALUE",$this->lng->txt("delete"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("BTN_NAME","export");
		$this->tpl->setVariable("BTN_VALUE",$this->lng->txt("export"));
		$this->tpl->parseCurrentBlock();
		*/
		$this->tpl->parseCurrentBlock();

	}

	/**
	* Get Bookmark list for personal desktop.
	*/
	function &getHTML()
	{
		include_once("./Services/PersonalDesktop/classes/class.ilBookmarkBlockGUI.php");
		$bookmark_block_gui = new ilBookmarkBlockGUI("ilpersonaldesktopgui", "show");
		
		return $bookmark_block_gui->getHTML();
	}

	/**
	* imports a bookmark file into database
	* display status information or report errors messages
	* in case of error
	*
	* @access	public
	*/
	function importFile()
	{
		if ($_FILES["bkmfile"]["error"] > UPLOAD_ERR_OK)
		{
			ilUtil::sendFailure($this->lng->txt("import_file_not_valid"));
			$this->newFormBookmark();
			return;
		}
		require_once ("./Services/PersonalDesktop/classes/class.ilBookmarkImportExport.php");
		$objects=ilBookmarkImportExport::_parseFile ($_FILES["bkmfile"]['tmp_name']);
		if ($objects===false)
		{
			ilUtil::sendFailure($this->lng->txt("import_file_not_valid"));
			$this->newFormBookmark();
			return;
		}
		// holds the number of created objects
		$num_create=array('bm'=>0,'bmf'=>0);
		$this->__importBookmarks($objects,$num_create,$this->id,0);

		ilUtil::sendSuccess(sprintf($this->lng->txt("bkm_import_ok"),$num_create['bm'],
			$num_create[ 'bmf']));
		$this->view();


	}
	/**
	* creates the bookmarks and folders
	* @param	array		array of objects
	* @param	array		stores the number of created objects
	* @param	folder_id		id where to store the bookmarks
	* @param	start_key		key of the objects array where to start
	* @access	private
	*/
	function __importBookmarks(&$objects,&$num_create,$folder_id,$start_key=0)
	{
		if (is_array($objects[$start_key]))
		{
			foreach ($objects[$start_key] as $obj_key=>$object)
			{
				switch ($object['type'])
				{
					case 'bm':
						if (!$object["title"]) continue;
						if (!$object["target"]) continue;
						$bm = new ilBookmark();
						$bm->setTitle($object["title"]);
						$bm->setDescription($object["description"]);
						$bm->setTarget($object["target"]);
						$bm->setParent($folder_id);
						$bm->create();
						$num_create['bm']++;
					break;
					case 'bmf':
						if (!$object["title"]) continue;
						$bmf = new ilBookmarkFolder();
						$bmf->setTitle($object["title"]);
						$bmf->setParent($folder_id);
						$bmf->create();
						$num_create['bmf']++;
						if (is_array($objects[$obj_key]))
						{
							$this->__importBookmarks($objects,$num_create,
								$bmf->getId(),$obj_key);
						}
					break;
				}
			}
		}
	}

}
?>
