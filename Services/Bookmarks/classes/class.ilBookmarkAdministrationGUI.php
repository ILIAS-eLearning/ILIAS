<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once ("./Services/Bookmarks/classes/class.ilBookmarkFolder.php");
require_once ("./Services/Bookmarks/classes/class.ilBookmark.php");
require_once ("./Services/Table/classes/class.ilTableGUI.php");

/**
 * GUI class for personal bookmark administration. It manages folders and bookmarks
 * with the help of the two corresponding core classes ilBookmarkFolder and ilBookmark.
 * Their methods are called in this User Interface class.
 * @author       Alex Killing <alex.killing@gmx.de>
 * @author       Manfred Thaler <manfred.thaler@endo7.com>
 * @version      $Id$
 * @ingroup      ServicesBookmarks
 * @ilCtrl_Calls ilBookmarkAdministrationGUI:
 */
class ilBookmarkAdministrationGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * @var ilErrorHandling
	 */
	protected $error;

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

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
	var $tpl;
	var $lng;

	var $tree;
	var $id;
	var $data;
	var $textwidth = 100;

	/**
	 * Constructor
	 * @access    public
	 * @param    integer        user_id (optional)
	 */
	function __construct()
	{
		global $DIC;

		$this->user = $DIC->user();
		$this->toolbar = $DIC->toolbar();
		$this->error = $DIC["ilErr"];
		$this->tabs = $DIC->tabs();
		$tpl = $DIC["tpl"];
		$lng = $DIC->language();
		$ilCtrl = $DIC->ctrl();
		$ilUser = $DIC->user();

//		$tpl->enableAdvancedColumnLayout(true, false);

		$tpl->getStandardTemplate();
		
		//print_r($_SESSION["error_post_vars"]);
		// if no bookmark folder id is given, take dummy root node id (that is 1)
		$this->id = (empty($_GET["bmf_id"]))
			? 1
			: $_GET["bmf_id"];
	
		// initiate variables
		$this->tpl   = $tpl;
		$this->lng   = $lng;
		$this->ctrl  = $ilCtrl;
		$this->ctrl->setParameter($this, "bmf_id", $this->id);
		$this->user_id = $ilUser->getId();

		$this->tree = new ilTree($this->user_id);
		$this->tree->setTableNames('bookmark_tree', 'bookmark_data');
		$this->root_id = $this->tree->readRootId();

		$this->lng->loadLanguageModule("bkm");
		
		$this->mode = "tree";
	}

	/**
	 * execute command
	 */
	function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass();

		switch($next_class)
		{
			default:
				$cmd = $this->ctrl->getCmd("view");
				$this->displayHeader();
				$this->$cmd();
				if($this->getMode() == 'tree')
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
	function getMode()
	{
		return $this->mode;
	}

	/**
	 * output explorer tree with bookmark folders
	 */
	function explorer()
	{
		$tpl = $this->tpl;

		include_once("./Services/Bookmarks/classes/class.ilBookmarkExplorerGUI.php");
		$exp = new ilBookmarkExplorerGUI($this, "explorer");
		if (!$exp->handleCommand())
		{
			$tpl->setLeftNavContent($exp->getHTML());
		}
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

		$this->tpl->setTitle($this->lng->txt("bookmarks"));
	}

	/*
	* display content of bookmark folder
	*/
	function view()
	{		
		$ilToolbar = $this->toolbar;
		
		if($this->id > 0 && !$this->tree->isInTree($this->id))
		{
			$this->ctrl->setParameter($this, 'bmf_id', '');
			$this->ctrl->redirect($this);
		}
		
		$ilToolbar->addButton($this->lng->txt("bookmark_new"), 
			$this->ctrl->getLinkTarget($this, "newFormBookmark"));
		$ilToolbar->addButton($this->lng->txt("bookmark_folder_new"), 
			$this->ctrl->getLinkTarget($this, "newFormBookmarkFolder"));

		$objects = ilBookmarkFolder::getObjects($this->id);

		include_once 'Services/Bookmarks/classes/class.ilBookmarkAdministrationTableGUI.php';
		$table = new ilBookmarkAdministrationTableGUI($this);
		$table->setId('bookmark_adm_table');		
		$table->setData($objects);
		$this->tpl->setVariable("ADM_CONTENT", $table->getHTML());
	}

	/**
	 * output a cell in object list
	 */
	function add_cell($val, $link = "")
	{
		if(!empty($link))
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
		$lng = $this->lng;

		if(empty($this->id))
		{
			return;
		}

		if(!$this->tree->isInTree($this->id))
		{
			return;
		}

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html", "Services/Locator");

		$path = $this->tree->getPathFull($this->id);
//print_r($path);
		$modifier = 1;

		return;
		$this->tpl->setVariable("TXT_LOCATOR", $this->lng->txt("locator"));
		$this->tpl->touchBlock("locator_separator");
		$this->tpl->touchBlock("locator_item");
		//$this->tpl->setCurrentBlock("locator_item");
		//$this->tpl->setVariable("ITEM", $this->lng->txt("personal_desktop"));
		//$this->tpl->setVariable("LINK_ITEM", $this->ctrl->getLinkTargetByClass("ilpersonaldesktopgui"));
		//$this->tpl->setVariable("LINK_TARGET","target=\"".
		//	ilFrameTargetInfo::_getFrame("MainContent")."\"");
		//$this->tpl->parseCurrentBlock();

		foreach($path as $key => $row)
		{
			if($key < count($path) - $modifier)
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
		if(!$type)
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
		if(!$this->tree->isInTree($this->id))
		{
			$this->ctrl->setParameter($this, 'bmf_id', '');
			$this->ctrl->redirect($this);
		}

		$form = $this->initFormBookmarkFolder();
		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}

	/**
	 * init bookmark folder create/edit form
	 * @param string form action type; valid values: createBookmark, updateBookmark
	 */
	private function initFormBookmarkFolder($action = 'createBookmarkFolder')
	{
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$ilUser = $this->user;

		if(!$this->tree->isInTree($this->id))
		{
			$this->ctrl->setParameter($this, 'bmf_id', '');
			$this->ctrl->redirect($this);
		}

		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setTopAnchor("bookmark_top");

		$form->setTitle($lng->txt("bookmark_folder_new"));

		if($action == 'updateBookmarkFolder')
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
		if(!$this->tree->isInTree($_GET["obj_id"]))
		{
			$this->ctrl->setParameter($this, 'bmf_id', '');
			$this->ctrl->setParameter($this, 'obj_id', '');
			$this->ctrl->redirect($this);
		}
		
		$bmf  = new ilBookmarkFolder($_GET["obj_id"]);
		$form = $this->initFormBookmarkFolder('updateBookmarkFolder', $this->id);
		$form->setValuesByArray
		(
			array
			(
				"title"  => $this->get_last("title", $bmf->getTitle()),
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
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$ilUser = $this->user;

		if(!$this->tree->isInTree($this->id))
		{
			$this->ctrl->setParameter($this, 'bmf_id', '');
			$this->ctrl->redirect($this);
		}

		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setTopAnchor("bookmark_top");

		$form->setTitle($lng->txt("bookmark_new"));

		if($action == 'updateBookmark')
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
		$prop->setValue($_GET['bm_title']);
		$prop->setRequired(true);
		$form->addItem($prop);

		// description
		$prop = new ilTextAreaInputGUI($lng->txt('description'), 'description');
		$form->addItem($prop);

		// target link
		$prop = new ilTextInputGUI($lng->txt('bookmark_target'), 'target');
		$prop->setValue($_GET['bm_link']);
		$prop->setRequired(true);
		$form->addItem($prop);

		// hidden redirect field
		if($_GET['return_to'])
		{
			$prop = new ilHiddenInputGUI('return_to');
			$prop->setValue($_GET['return_to']);
			$form->addItem($prop);

			$prop = new ilHiddenInputGUI('return_to_url');
			if($_GET['return_to_url'])
				$prop->setValue($_GET['return_to_url']);
			else
				$prop->setValue($_GET['bm_link']);
			$form->addItem($prop);
		}

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
	 * Init import bookmark form

	 */
	private function initImportBookmarksForm()
	{
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$ilUser = $this->user;

		if(!$this->tree->isInTree($this->id))
		{
			$this->ctrl->setParameter($this, 'bmf_id', '');
			$this->ctrl->redirect($this);
		}

		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, "importFile")); // #16133
		$form->setTopAnchor("bookmark_top");
		$form->setTitle($lng->txt("bkm_import"));

		$fi = new ilFileInputGUI($lng->txt("file_add"), "bkmfile");
		$fi->setRequired(true);
		$form->addItem($fi);

		$form->addCommandButton("importFile", $lng->txt('import'));
		$form->addCommandButton('cancel', $lng->txt('cancel'));

		return $form;
	}

	/**
	 * display new bookmark form
	 */
	function newFormBookmark()
	{
		$form  = $this->initFormBookmark();
		$html1 = $form->getHTML();
		$html2 = '';
		if(!$_REQUEST["bm_link"])
		{
			$form2 = $this->initImportBookmarksForm();
			$html2 = "<br />" . $form2->getHTML();
		}
		$this->tpl->setVariable("ADM_CONTENT", $html1 . $html2);
	}


	/**
	 * get stored post var in case of an error/warning otherwise return passed value
	 */
	function get_last($a_var, $a_value)
	{
		return (!empty($_POST[$a_var])) ?
			ilUtil::prepareFormOutput(($_POST[$a_var]), true) :
			ilUtil::prepareFormOutput($a_value);
	}

	/**
	 * display edit bookmark form
	 */
	function editFormBookmark()
	{
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;

		if(!$this->tree->isInTree($_GET["obj_id"]))
		{
			$this->ctrl->setParameter($this, 'obj_id', '');
			$this->ctrl->setParameter($this, 'bmf_id', '');
			$this->ctrl->redirect($this);
		}

		$form     = $this->initFormBookmark('updateBookmark');
		$bookmark = new ilBookmark($_GET["obj_id"]);
		$form->setValuesByArray
		(
			array
			(
				"title"       => $bookmark->getTitle(),
				"target"      => $bookmark->getTarget(),
				"description" => $bookmark->getDescription(),
				"obj_id"      => $_GET["obj_id"],
			)
		);
		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}


	/**
	 * create new bookmark folder in db
	 */
	function createBookmarkFolder()
	{
		if(!$this->tree->isInTree($this->id))
		{
			$this->ctrl->setParameter($this, 'bmf_id', '');
			$this->ctrl->redirect($this);
		}

		// check title
		if(empty($_POST["title"]))
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

			ilUtil::sendSuccess($this->lng->txt("bkm_fold_created"), true);

		$ilCtrl = $this->ctrl;
			$ilCtrl->saveParameter($this, 'bmf_id');
			$ilCtrl->redirect($this, 'view');
		}
	}


	/**
	 * update bookmark folder
	 */
	function updateBookmarkFolder()
	{
		if(!$this->tree->isInTree($_GET["obj_id"]))
		{
			$this->ctrl->setParameter($this, 'obj_id', '');
			$this->ctrl->setParameter($this, 'bmf_id', '');
			$this->ctrl->redirect($this);
		}

		// check title
		if(empty($_POST["title"]))
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

		$ilCtrl = $this->ctrl;
			$ilCtrl->saveParameter($this, 'bmf_id');
			$ilCtrl->redirect($this, 'view');
		}
	}


	/**
	 * create new bookmark in db
	 */
	function createBookmark()
	{
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;

		if(!$this->tree->isInTree($this->id))
		{
			$this->ctrl->setParameter($this, 'bmf_id', '');
			$this->ctrl->redirect($this);
		}

		// check title and target
		if(empty($_POST["title"]))
		{
			ilUtil::sendFailure($this->lng->txt("please_enter_title"));
			$this->newFormBookmark();
		}
		else if(empty($_POST["target"]))
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

			ilUtil::sendInfo($lng->txt('bookmark_added'), true);

			$ilCtrl->saveParameter($this, 'bmf_id');
			$ilCtrl->redirect($this, 'view');
		}
	}

	/**
	 * update bookmark in db
	 */
	function updateBookmark()
	{
		if(!$this->tree->isInTree($_GET["obj_id"]))
		{
			$this->ctrl->setParameter($this, 'obj_id', '');
			$this->ctrl->setParameter($this, 'bmf_id', '');
			$this->ctrl->redirect($this);
		}

		// check title and target
		if(empty($_POST["title"]))
		{
			ilUtil::sendFailure($this->lng->txt("please_enter_title"));
			$this->editFormBookmark();
		}
		else if(empty($_POST["target"]))
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
	function export($deliver = true)
	{
		$ilErr = $this->error;
		$ilUser = $this->user;

		$bm_ids = $_GET['bm_id'] ? array($_GET['bm_id']) : $_POST['bm_id'];
		if(!$bm_ids)
		{
			$ilErr->raiseError($this->lng->txt("no_checkbox"), $ilErr->MESSAGE);
		}
		$export_ids = array();
		foreach($bm_ids as $id)
		{
			if($this->tree->isInTree($id))
			{
				//list($type, $obj_id) = explode(":", $id);
				//$export_ids[]=$obj_id;
				$export_ids[] = $id;
			}
		}

		require_once ("./Services/Bookmarks/classes/class.ilBookmarkImportExport.php");
		$html_content = ilBookmarkImportExport::_exportBookmark($export_ids, true,
			$this->lng->txt("bookmarks_of") . " " . $ilUser->getFullname());

		if($deliver)
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
		$ilUser = $this->user;
		include_once './Services/Mail/classes/class.ilFileDataMail.php';
		require_once "Services/Mail/classes/class.ilFormatMail.php";
		$mfile = new ilFileDataMail($ilUser->getId());
		$umail = new ilFormatMail($ilUser->getId());

		$html_content = $this->export(false);
		$tempfile     = ilUtil::ilTempnam();
		$fp           = fopen($tempfile, 'w');
		fwrite($fp, $html_content);
		fclose($fp);
		$filename = 'bookmarks.html';
		$mfile->copyAttachmentFile($tempfile, $filename);
		$umail->savePostData($ilUser->getId(), array($filename),
			'', '', '', '', '',
			'',
			'', 0);

		require_once 'Services/Mail/classes/class.ilMailFormCall.php';
		ilUtil::redirect(ilMailFormCall::getRedirectTarget($this, '', array(), array('type' => 'attach')));
	}

	/**
	 * display deletion conformation screen
	 */
	function delete()
	{
		$ilErr = $this->error;

		$bm_ids = $_GET['bm_id'] ? array($_GET['bm_id']) : $_POST['bm_id'];
		if(!$bm_ids)
		{
			$ilErr->raiseError($this->lng->txt("no_checkbox"), $ilErr->MESSAGE);
		}

		$this->ctrl->setParameter($this, "bmf_id", $this->id);

		// display confirmation message
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setHeaderText($this->lng->txt("info_delete_sure"));
		$cgui->setCancel($this->lng->txt("cancel"), "cancel");
		$cgui->setConfirm($this->lng->txt("confirm"), "confirm");

		foreach($bm_ids as $obj_id)
		{
			$type = ilBookmark::_getTypeOfId($obj_id);

			if(!$this->tree->isInTree($obj_id))
			{
				continue;
			}

			switch($type)
			{
				case "bmf":
					$BookmarkFolder = new ilBookmarkFolder($obj_id);
					$title          = $BookmarkFolder->getTitle();
					$target         = "";
					unset($BookmarkFolder);
					break;

				case "bm":
					$Bookmark = new ilBookmark($obj_id);
					$title    = $Bookmark->getTitle();
					$target   = $Bookmark->getTarget();
					unset($Bookmark);
					break;
			}

			$caption = ilUtil::getImageTagByType($type, $this->tpl->tplPath) .
				" " . $title;
			if($target)
			{
				$caption .= " (" . ilUtil::shortenText($target, $this->textwidth, true) . ")";
			}

			$cgui->addItem("id[]", $obj_id, $caption);
		}

		$this->tpl->setContent($cgui->getHTML());
	}

	/**
	 * cancel deletion,insert, update
	 */
	function cancel()
	{
		$this->view();
	}

	/**
	 * deletion confirmed -> delete folders / bookmarks
	 */
	function confirm()
	{
		$ilErr = $this->error;

		// AT LEAST ONE OBJECT HAS TO BE CHOSEN.
		if(!$_POST["id"])
		{
			$ilErr->raiseError($this->lng->txt("no_checkbox"), $ilErr->MESSAGE);
		}

		// FOR ALL SELECTED OBJECTS
		foreach($_POST["id"] as $id)
		{
			$type = ilBookmark::_getTypeOfId($id);

			// get node data and subtree nodes
			if($this->tree->isInTree($id))
			{
				$node_data     = $this->tree->getNodeData($id);
				$subtree_nodes = $this->tree->getSubTree($node_data);
			}
			else
			{
				continue;
			}

			// delete tree
			$this->tree->deleteTree($node_data);

			// delete objects of subtree nodes
			foreach($subtree_nodes as $node)
			{
				switch($node["type"])
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
		ilUtil::sendSuccess($this->lng->txt("info_deleted"), true);

		$this->view();
	}


	/**
	 * display subobject addition selection
	 */
	function showPossibleSubObjects()
	{
		$actions = array(
			"delete"  => $this->lng->txt("delete"),
			"export"  => $this->lng->txt("export"),
			"sendmail"=> $this->lng->txt("bkm_sendmail"),
		);

		$subobj = array("bm", "bmf");

		if(is_array($subobj))
		{
			//build form
			$opts = ilUtil::formSelect("", "type", $subobj);

			$this->tpl->setCurrentBlock("add_object");
			$this->tpl->setVariable("COLUMN_COUNTS", 7);
			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("BTN_NAME", "newForm");
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("TPLPATH", $this->tpl->tplPath);

		$this->tpl->setCurrentBlock("tbl_action_select");
		$this->tpl->setVariable("SELECT_ACTION", ilUtil::formSelect($_SESSION["error_post_vars"]['action'], "action", $actions, false, true));
		$this->tpl->setVariable("BTN_NAME", "executeAction");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("execute"));

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
	function getHTML()
	{
		include_once("./Services/Bookmarks/classes/class.ilBookmarkBlockGUI.php");
		$bookmark_block_gui = new ilBookmarkBlockGUI("ilpersonaldesktopgui", "show");

		return $bookmark_block_gui->getHTML();
	}

	/**
	 * imports a bookmark file into database
	 * display status information or report errors messages
	 * in case of error
	 * @access    public
	 */
	function importFile()
	{
		if(!$this->tree->isInTree($this->id))
		{
			$this->ctrl->setParameter($this, 'bmf_id', '');
			$this->ctrl->redirect($this);
		}

		if($_FILES["bkmfile"]["error"] > UPLOAD_ERR_OK)
		{
			ilUtil::sendFailure($this->lng->txt("import_file_not_valid"));
			$this->newFormBookmark();
			return;
		}
		require_once ("./Services/Bookmarks/classes/class.ilBookmarkImportExport.php");
		$objects = ilBookmarkImportExport::_parseFile($_FILES["bkmfile"]['tmp_name']);
		if($objects === false)
		{
			ilUtil::sendFailure($this->lng->txt("import_file_not_valid"));
			$this->newFormBookmark();
			return;
		}
		// holds the number of created objects
		$num_create = array('bm'=> 0, 'bmf'=> 0);
		$this->__importBookmarks($objects, $num_create, $this->id, 0);

		ilUtil::sendSuccess(sprintf($this->lng->txt("bkm_import_ok"), $num_create['bm'],
			$num_create['bmf']));
		$this->view();


	}

	/**
	 * creates the bookmarks and folders
	 * @param    array            array of objects
	 * @param    array            stores the number of created objects
	 * @param    folder_id        id where to store the bookmarks
	 * @param    start_key        key of the objects array where to start
	 * @access    private
	 */
	function __importBookmarks(&$objects, &$num_create, $folder_id, $start_key = 0)
	{
		if(is_array($objects[$start_key]))
		{
			foreach($objects[$start_key] as $obj_key=> $object)
			{
				switch($object['type'])
				{
					case 'bm':
						if(!$object["title"]) continue 2;
						if(!$object["target"]) continue 2;
						$bm = new ilBookmark();
						$bm->setTitle($object["title"]);
						$bm->setDescription($object["description"]);
						$bm->setTarget($object["target"]);
						$bm->setParent($folder_id);
						$bm->create();
						$num_create['bm']++;
						break;
					case 'bmf':
						if(!$object["title"]) continue 2;
						$bmf = new ilBookmarkFolder();
						$bmf->setTitle($object["title"]);
						$bmf->setParent($folder_id);
						$bmf->create();
						$num_create['bmf']++;
						if(is_array($objects[$obj_key]))
						{
							$this->__importBookmarks($objects, $num_create,
								$bmf->getId(), $obj_key);
						}
						break;
				}
			}
		}
	}

	function move()
	{
		$ilUser = $this->user;
		$ilTabs = $this->tabs;
		$tpl = $this->tpl;

		$bm_ids = $_REQUEST['bm_id'];
		if(!$bm_ids && $_GET["bm_id_tgt"] == "")
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			return $this->view();
		}

		$ilTabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this));

		$this->ctrl->setParameter($this, "bm_id_tgt", $_GET["bm_id_tgt"] ?  $_GET["bm_id_tgt"] : implode(";", $bm_ids));
		ilUtil::sendInfo($this->lng->txt("bookmark_select_target"));
		include_once("./Services/Bookmarks/classes/class.ilBookmarkMoveExplorerGUI.php");
		$exp = new ilBookmarkMoveExplorerGUI($this, "move");
		if (!$exp->handleCommand())
		{
			$this->mode = "flat";
			$this->tpl->setContent($exp->getHTML());
		}
	}

	function confirmedMove()
	{
		$ilUser = $this->user;

		$tgt    = (int)$_REQUEST["bmfmv_id"];
		$bm_ids = explode(";", $_REQUEST['bm_id_tgt']);
		if(!$bm_ids || !$tgt)
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			return $this->view();
		}

		$tree = new ilTree($ilUser->getId());
		$tree->setTableNames('bookmark_tree', 'bookmark_data');

		$tgt_node = $tree->getNodeData($tgt);

		// sanity check
		foreach($bm_ids as $node_id)
		{
			if($tree->isGrandChild($node_id, $tgt))
			{
				ilUtil::sendFailure($this->lng->txt("error"), true);
				$this->ctrl->redirect($this, "view");
			}

			$node = $tree->getNodeData($node_id);

			// already at correct position
			if($node["parent"] == $tgt)
			{
				continue;
			}

			$tree->moveTree($node_id, $tgt);
		}

		ilUtil::sendSuccess($this->lng->txt("bookmark_moved_ok"), true);
		$this->ctrl->setParameter($this, "bmf_id", $tgt);
		$this->ctrl->redirect($this, "view");
	}
}