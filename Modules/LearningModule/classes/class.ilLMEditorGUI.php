<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once ("./Modules/LearningModule/classes/class.ilLMObjectFactory.php");
include_once ("./Services/Utilities/classes/class.ilDOMUtil.php");
include_once ("./Services/COPage/classes/class.ilPageEditorGUI.php");
include_once ("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
include_once ("./Modules/LearningModule/classes/class.ilEditClipboard.php");


/**
* GUI class for learning module editor
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilLMEditorGUI: ilObjLearningModuleGUI
*
* @ingroup ModulesIliasLearningModule
*/
class ilLMEditorGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilRbacSystem
	 */
	protected $rbacsystem;

	/**
	 * @var ilNavigationHistory
	 */
	protected $nav_history;

	/**
	 * @var ilErrorHandling
	 */
	protected $error;

	/**
	 * @var ilHelpGUI
	 */
	protected $help;

	var $tpl;
	var $lng;
	var $objDefinition;
	var $ref_id;
	var $lm_obj;

	var $tree;
	var $obj_id;

	/**
	* Constructor
	* @access	public
	*/
	function __construct()
	{
		global $DIC;

		$this->rbacsystem = $DIC->rbac()->system();
		$this->nav_history = $DIC["ilNavigationHistory"];
		$this->error = $DIC["ilErr"];
		$this->help = $DIC["ilHelp"];
		$tpl = $DIC["tpl"];
		$lng = $DIC->language();
		$objDefinition = $DIC["objDefinition"];
		$ilCtrl = $DIC->ctrl();
		$rbacsystem = $DIC->rbac()->system();
		$ilNavigationHistory = $DIC["ilNavigationHistory"];
		$ilErr = $DIC["ilErr"];
		
		$lng->loadLanguageModule("content");
		$lng->loadLanguageModule("lm");

		// check write permission
		if (!$rbacsystem->checkAccess("write", $_GET["ref_id"]))
		{
			$ilErr->raiseError($lng->txt("permission_denied"), $ilErr->MESSAGE);
		}


		$this->ctrl = $ilCtrl;

		//$this->ctrl->saveParameter($this, array("ref_id", "obj_id"));
		$this->ctrl->saveParameter($this, array("ref_id", "transl"));

		// initiate variables
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->objDefinition = $objDefinition;
		$this->ref_id = $_GET["ref_id"];
		$this->obj_id = $_GET["obj_id"];

		$this->lm_obj = ilObjectFactory::getInstanceByRefId($this->ref_id);
		$this->tree = new ilTree($this->lm_obj->getId());
		$this->tree->setTableNames('lm_tree','lm_data');
		$this->tree->setTreeTablePK("lm_id");

		if ($this->obj_id > 0 && ilLMObject::_lookupContObjID($this->obj_id) != $this->lm_obj->getId())
		{
			throw new ilException("Object ID does not match learning module.");
		}
		if ($_REQUEST["active_node"] > 0 && ilLMObject::_lookupContObjID((int) $_REQUEST["active_node"]) != $this->lm_obj->getId())
		{
			throw new ilException("Active node does not match learning module.");
		}

		$ilNavigationHistory->addItem($_GET["ref_id"],
			"ilias.php?baseClass=ilLMEditorGUI&ref_id=".$_GET["ref_id"], "lm");

	}

	/**
	* execute command
	*/
	function executeCommand()
	{

		$ilHelp = $this->help;
		
		if ($_GET["to_page"]== 1)
		{
			$this->ctrl->setParameterByClass("illmpageobjectgui", "obj_id", $_GET["obj_id"]);
			$this->ctrl->redirectByClass(array("ilobjlearningmodulegui", "illmpageobjectgui"), "edit");
		}
		
		if ($cmd != "showTree")
		{
			$this->showTree();
		}

		$next_class = $this->ctrl->getNextClass($this);
//echo "lmeditorgui:$next_class:".$this->ctrl->getCmdClass().":$cmd:<br>";

		if ($next_class == "" && ($cmd != "explorer")
			&& ($cmd != "showImageMap"))
		{
			$next_class = "ilobjlearningmodulegui";
		}

		// show footer
		$show_footer = ($cmd == "explorer")
			? false
			: true;
			
// if ($this->lm_obj->getType()
		switch($next_class)
		{
			case "ilobjlearningmodulegui":
				include_once ("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
				include_once ("./Modules/LearningModule/classes/class.ilObjLearningModuleGUI.php");
				$this->main_header($this->lm_obj->getType());
				$lm_gui = new ilObjLearningModuleGUI("", $_GET["ref_id"], true, false);
				//$ret =& $lm_gui->executeCommand();
				$ret = $this->ctrl->forwardCommand($lm_gui);
				if (strcmp($cmd, "explorer") != 0)
				{
					// don't call the locator in the explorer frame
					// this prevents a lot of log errors
					// Helmut Schottmüller, 2006-07-21
					$this->displayLocator();
				}
//echo "*".$this->tpl->get()."*";
				// (horrible) workaround for preventing template engine
				// from hiding paragraph text that is enclosed
				// in curly brackets (e.g. "{a}", see ilPageObjectGUI::showPage())
//				$this->tpl->fillTabs();
				$output =  $this->tpl->get("DEFAULT", true, true, $show_footer,true);
				$output = str_replace("&#123;", "{", $output);
				$output = str_replace("&#125;", "}", $output);
				header('Content-type: text/html; charset=UTF-8');
				echo $output;
				break;

			default:
				$ret = $this->$cmd();
				break;
		}
	}

	/**
	 * Show tree
	 *
	 * @param
	 * @return
	 */
	function showTree()
	{
		$tpl = $this->tpl;

		include_once("./Modules/LearningModule/classes/class.ilLMEditorExplorerGUI.php");
		$exp = new ilLMEditorExplorerGUI($this, "showTree", $this->lm_obj);
		if (!$exp->handleCommand())
		{
			$tpl->setLeftNavContent($exp->getHTML());
		}
	}
	
	/**
	* output main header (title and locator)
	*/
	function main_header($a_type)
	{
		$lng = $this->lng;

		$this->tpl->getStandardTemplate();

		// content style
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath($this->lm_obj->getStyleSheetId()));
		$this->tpl->parseCurrentBlock();

		// syntax style
		$this->tpl->setCurrentBlock("SyntaxStyle");
		$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$this->tpl->parseCurrentBlock();

	}


	/**
	* display locator
	*/
	function displayLocator()
	{
		$lng = $this->lng;

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html", "Services/Locator");

		$modifier = 1;

		$locations = $this->ctrl->getLocations();

		foreach ($locations as $key => $row)
		{
			if ($key < count($locations)-$modifier)
			{
				$this->tpl->touchBlock("locator_separator");
			}

			if ($row["ref_id"]> 0 && $row["ref_id"] != ROOT_FOLDER_ID)
			{
				$oid = ilObject::_lookupObjId($row["ref_id"]);
				$t = ilObject::_lookupType($oid);
				$this->tpl->setCurrentBlock("locator_img");
				$this->tpl->setVariable("IMG_SRC",
					ilUtil::getImagePath("icon_".$t.".svg"));
				$this->tpl->setVariable("IMG_ALT",
					$lng->txt("obj_".$type));
				$this->tpl->parseCurrentBlock();
			}

			if ($row["link"] != "")
			{
				$this->tpl->setCurrentBlock("locator_item");
				$this->tpl->setVariable("ITEM", $row["title"]);
				$this->tpl->setVariable("LINK_ITEM", $row["link"]);
				if ($row["target"] != "")
				{
					$this->tpl->setVariable("LINK_TARGET", ' target="'.$row["target"].'" ');
				}
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("locator_item");
				$this->tpl->setVariable("PREFIX", $row["title"]);
				$this->tpl->parseCurrentBlock();
			}
		}

		$this->tpl->setCurrentBlock("locator");
		$this->tpl->parseCurrentBlock();

	}

}
?>
