<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once ("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once ("classes/class.ilTabsGUI.php");

/**
* Page Editor GUI class
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilPageEditorGUI: ilPCParagraphGUI, ilPCTableGUI, ilPCTableDataGUI
* @ilCtrl_Calls ilPageEditorGUI: ilPCMediaObjectGUI, ilPCListGUI, ilPCListItemGUI
* @ilCtrl_Calls ilPageEditorGUI: ilPCFileListGUI, ilPCFileItemGUI, ilObjMediaObjectGUI
* @ilCtrl_Calls ilPageEditorGUI: ilPCSourceCodeGUI, ilInternalLinkGUI, ilPCQuestionGUI
* @ilCtrl_Calls ilPageEditorGUI: ilPCSectionGUI, ilPCDataTableGUI, ilPCResourcesGUI
* @ilCtrl_Calls ilPageEditorGUI: ilPCMapGUI, ilPCPluggedGUI, ilPCTabsGUI, IlPCPlaceHolderGUI
* @ilCtrl_Calls ilPageEditorGUI: ilPCContentIncludeGUI, ilPCLoginPageElementGUI
* @ilCtrl_Calls ilPageEditorGUI: ilPCInteractiveImageGUI, ilPCProfileGUI, ilPCVerificationGUI
* @ilCtrl_Calls ilPageEditorGUI: ilPCBlogGUI, ilPCQuestionOverviewGUI
*
* @ingroup ServicesCOPage
*/
class ilPageEditorGUI
{
	/**
	* ilias object
	* @var object ilias
	* @access public
	*/
	var $ilias;
	var $tpl;
	var $lng;
	var $ctrl;
	var $objDefinition;
	var $page;
	var $target_script;
	var $return_location;
	var $header;
	var $tabs;
	var $cont_obj;
	var $enable_keywords;
	var $enable_anchors;

	/**
	* Constructor
	*
	* @param	object		$a_page_object		page object
	* @access	public
	*/
	function ilPageEditorGUI(&$a_page_object, &$a_page_object_gui)
	{
		global $ilias, $tpl, $lng, $objDefinition, $ilCtrl,$ilTabs;

		// initiate variables
		$this->ilias =& $ilias;
		$this->ctrl =& $ilCtrl;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->objDefinition = $objDefinition;
		$this->tabs_gui =& $ilTabs;
		$this->page =& $a_page_object;
		$this->page_gui =& $a_page_object_gui;

		$this->ctrl->saveParameter($this, array("hier_id", "pc_id"));
	}


	/**
	* set header title
	*
	* @param	string		$a_header		header title
	*/
	function setHeader($a_header)
	{
		$this->header = $a_header;
	}

	/**
	* get header title
	*
	* @return	string		header title
	*/
	function getHeader()
	{
		return $this->header;
	}

	/**
	* set locator object
	*
	* @param	object		$a_locator		locator object
	*/
	function setLocator(&$a_locator)
	{
		$this->locator =& $a_locator;
	}

	/**
	* redirect to parent context
	*/
	function returnToContext()
	{
		$this->ctrl->returnToParent($this);
	}

	function setIntLinkHelpDefault($a_type, $a_id)
	{
		$this->int_link_def_type = $a_type;
		$this->int_link_def_id = $a_id;
	}
	
	function setIntLinkReturn($a_return)
	{
		$this->int_link_return = $a_return;
	}

	
	function setPageBackTitle($a_title)
	{
		$this->page_back_title = $a_title;
	}

	/**
	 * Set enable internal links
	 *
	 * @param	boolean	enable internal links
	 */
	function setEnableInternalLinks($a_val)
	{
		$this->enable_internal_links = $a_val;
	}
	
	/**
	 * Get enable internal links
	 *
	 * @return	boolean	enable internal links
	 */
	function getEnableInternalLinks()
	{
		return $this->enable_internal_links;
	}

	/**
	 * Set enable keywords handling
	 *
	 * @param	boolean	keywords handling
	 */
	function setEnableKeywords($a_val)
	{
		$this->enable_keywords = $a_val;
	}
	
	/**
	 * Get enable keywords handling
	 *
	 * @return	boolean	keywords handling
	 */
	function getEnableKeywords()
	{
		return $this->enable_keywords;
	}

	/**
	 * Set enable anchors
	 *
	 * @param	boolean	anchors
	 */
	function setEnableAnchors($a_val)
	{
		$this->enable_anchors = $a_val;
	}
	
	/**
	 * Get enable anchors
	 *
	 * @return	boolean	anchors
	 */
	function getEnableAnchors()
	{
		return $this->enable_anchors;
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;

		$cmd = $this->ctrl->getCmd("displayPage");
		$cmdClass = strtolower($this->ctrl->getCmdClass());

		$hier_id = $_GET["hier_id"];
		$pc_id = $_GET["pc_id"];
		if(isset($_POST["new_hier_id"]))
		{
			$hier_id = $_POST["new_hier_id"];
		}
//echo "GEThier_id:".$_GET["hier_id"]."<br>";
//$this->ctrl->debug("hier_id:".$hier_id);

		$new_type = (isset($_GET["new_type"]))
			? $_GET["new_type"]
			: $_POST["new_type"];
//echo "-$cmd-";
//var_dump($_GET); var_dump($_POST); exit;
/*array
  'target' =>
    array
      0 => string '' (length=0)
  'commandpg' => string 'insertJS' (length=8)
  'cmd' =>
    array
      'exec_pg:' => string 'Ok' (length=2)
  'ajaxform_content' => string '<div class=\"ilc_text_block_Standard\">sdfsdfsd sd</div>' (length=56)
  'ajaxform_char' => string '' (length=0)*/
/*array
  'usedwsiwygeditor' => string '0' (length=1)
  'par_characteristic' => string 'Standard' (length=8)
  'par_content' => string 'adasdaasda a

' (length=14)
  'par_language' => string 'en' (length=2)
  'cmd' =>
    array
      'create_par' => string 'Save' (length=4)*/

		if (substr($cmd, 0, 5) == "exec_")
		{
//echo ":".key($_POST["cmd"]).":";
			// check whether pc id is given
			$pca = explode(":", key($_POST["cmd"]));
			$pc_id = $pca[1];
//echo "<br />exec_pc_id:-$pc_id-";
			$cmd = explode("_", $pca[0]);
			unset($cmd[0]);
			$hier_id = implode($cmd, "_");
			$cmd = $_POST["command".$hier_id];
		}
//echo "<br>cmd:$cmd:";exit;
		// strip "c" "r" of table ids from hierarchical id
		$first_hier_character = substr($hier_id, 0, 1);
		if ($first_hier_character == "c" ||
			$first_hier_character == "r" ||
			$first_hier_character == "i")
		{
			$hier_id = substr($hier_id, 1);
		}
		$this->page->buildDom();
		$this->page->addHierIDs();

		// determine command and content object
		if ($cmdClass != "ilfilesystemgui")
		{
			$com = explode("_", $cmd);
			$cmd = $com[0];
		}
		

		$next_class = $this->ctrl->getNextClass($this);


		// determine content type
		if ($com[0] == "insert" || $com[0] == "create")
		{
			$cmd = $com[0];
			$ctype = $com[1];
			$add_type = $com[2];
			if ($ctype == "mob") $ctype = "media";
		}
		else
		{
			// setting cmd and cmdclass for editing of linked media
			if ($cmd == "editLinkedMedia")
			{
				$this->ctrl->setCmd("edit");
				$cmd = "edit";
				$_GET["pgEdMediaMode"] = "editLinkedMedia";
				$_GET["mob_id"] = $_POST["mob_id"];
			}
			if ($_GET["pgEdMediaMode"] == "editLinkedMedia")
			{
				$this->ctrl->setParameter($this, "pgEdMediaMode", "editLinkedMedia");
				$this->ctrl->setParameter($this, "mob_id", $_GET["mob_id"]);
				if ($cmdClass != "ilinternallinkgui" && $cmdClass != "ilmdeditorgui"
					&& $cmdClass != "ilimagemapeditorgui" && $cmdClass != "ilfilesystemgui")
				{
					$this->ctrl->setCmdClass("ilobjmediaobjectgui");
					$cmdClass = "ilobjmediaobjectgui";
				}
			}
if (false)
{
var_dump($_POST);
var_dump($_GET);
echo ";$cmd;".$next_class.";";
echo "-$pc_id-";
echo "-$cmd-".$this->ctrl->getCmd()."-";
}

//var_dump($_POST);
			// note: ilinternallinkgui for page: no cont_obj is received
			// ilinternallinkgui for mob: cont_obj is received
			if ($cmd != "insertFromClipboard" && $cmd != "pasteFromClipboard" &&
				$cmd != "setMediaMode" && $cmd != "copyLinkedMediaToClipboard" &&
				$cmd != "activatePage" && $cmd != "deactivatePage" &&
				$cmd != "copyLinkedMediaToMediaPool" && $cmd != "showSnippetInfo" &&
				$cmd != "deleteSelected" && $cmd != "paste" &&
				$cmd != "copySelected" && $cmd != "cutSelected" &&
				($cmd != "displayPage" || $_POST["editImagemapForward_x"] != "" || $_POST["imagemap_x"] != "") &&
				($cmd != "displayPage" || $_POST["editImagemapForward_x"] != "") &&
				$cmd != "activateSelected" && $cmd != "assignCharacteristicForm" &&
				$cmd != "assignCharacteristic" &&
				$cmd != "cancelCreate" && $cmd != "popup" &&
				$cmdClass != "ileditclipboardgui" && $cmd != "addChangeComment" &&
				($cmdClass != "ilinternallinkgui" || ($next_class == "ilpcmediaobjectgui")))
			{
				if ($_GET["pgEdMediaMode"] != "editLinkedMedia")
				{
//$this->ctrl->debug("gettingContentObject (no linked media)");
//echo $hier_id."-".$pc_id;
					$cont_obj =& $this->page->getContentObject($hier_id, $pc_id);
					if (!is_object($cont_obj))
					{
						$ilCtrl->returnToParent($this);
					}
					$ctype = $cont_obj->getType();
				}
			}
		}
//$this->ctrl->debug("+ctype:".$ctype."+");
//		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
//		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		if ($ctype != "media" || !is_object ($cont_obj))
		{
			if ($this->getHeader() != "")
			{
				$this->tpl->setTitle($this->getHeader());
			}
			$this->displayLocator();
		}

		$this->cont_obj =& $cont_obj;


		// special command / command class handling
		$this->ctrl->setParameter($this, "hier_id", $hier_id);
		$this->ctrl->setParameter($this, "pc_id", $pc_id);
		$this->ctrl->setCmd($cmd);
		//$next_class = $this->ctrl->getNextClass($this);
//$this->ctrl->debug("+next_class:".$next_class."+");
//echo("+next_class:".$next_class."+".$ctype."+"); exit;

		if ($next_class == "")
		{
			switch($ctype)
			{
				case "src":
					$this->ctrl->setCmdClass("ilPCSourcecodeGUI");
					break;

				case "par":
					$this->ctrl->setCmdClass("ilPCParagraphGUI");
					break;

				// advanced table
				case "tab":
					$this->ctrl->setCmdClass("ilPCTableGUI");
					break;

				// data table
				case "dtab":
					$this->ctrl->setCmdClass("ilPCDataTableGUI");
					break;

				case "td":
					$this->ctrl->setCmdClass("ilPCTableDataGUI");
					break;

				case "media":
					$this->ctrl->setCmdClass("ilPCMediaObjectGUI");
					break;

				case "list":
					$this->ctrl->setCmdClass("ilPCListGUI");
					break;

				case "li":
					$this->ctrl->setCmdClass("ilPCListItemGUI");
					break;

				case "flst":
					$this->ctrl->setCmdClass("ilPCFileListGUI");
					break;

				case "flit":
					$this->ctrl->setCmdClass("ilPCFileItemGUI");
					break;

				case "pcqst":
					$this->ctrl->setCmdClass("ilPCQuestionGUI");
					break;

				case "sec":
					$this->ctrl->setCmdClass("ilPCSectionGUI");
					break;
					
				case "repobj":
					$this->ctrl->setCmdClass("ilPCResourcesGUI");
					break;

				case 'lpe':
					$this->ctrl->setCmdClass('ilPCLoginPageElementGUI');
					break;

				case "map":
					$this->ctrl->setCmdClass("ilPCMapGUI");
					break;

				case "tabs":
					$this->ctrl->setCmdClass("ilPCTabsGUI");
					break;
					
				case "tabstab":
					$this->ctrl->setCmdClass("ilPCTabGUI");
					break;

				case "plug":
					$this->ctrl->setCmdClass("ilPCPluggedGUI");
					break;

				case "plach":
					$this->ctrl->setCmdClass("ilPCPlaceHolderGUI");
					break;

				case "incl":
					$this->ctrl->setCmdClass("ilPCContentIncludeGUI");
					break;
					
				case "iim":
					$this->ctrl->setCmdClass("ilPCInteractiveImageGUI");
					break;

				case "prof":
					$this->ctrl->setCmdClass("ilPCProfileGUI");
					break;
				
				case "vrfc":
					$this->ctrl->setCmdClass("ilPCVerificationGUI");
					break;
				
				case "blog":
					$this->ctrl->setCmdClass("ilPCBlogGUI");
					break;
					
				case "qover":
					$this->ctrl->setCmdClass("ilPCQuestionOverviewGUI");
					break;

			}
			$next_class = $this->ctrl->getNextClass($this);
		}

		// do not do this while imagemap editing is ongoing
		if ($cmd == "displayPage" && $_POST["editImagemapForward_x"] == "" && $_POST["imagemap_x"] == "")
		{
			$next_class = "";
		}
		
//echo "hier_id:$hier_id:type:$type:cmd:$cmd:ctype:$ctype:next_class:$next_class:<br>"; exit;
		switch($next_class)
		{
			case "ilinternallinkgui":
				$link_gui = new ilInternalLinkGUI(
					$this->int_link_def_type, $this->int_link_def_id);
				$link_gui->setMode("normal");
				$link_gui->setFilterWhiteList(
					$this->page_gui->getPageConfig()->getIntLinkFilterWhiteList());
				foreach ($this->page_gui->getPageConfig()->getIntLinkFilters() as $filter)
				{
					$link_gui->filterLinkType($filter);
				}
//				$link_gui->setSetLinkTargetScript(
//					$this->ctrl->getLinkTarget($this, "setInternalLink"));
				$link_gui->setReturn($this->int_link_return);
				if ($ilCtrl->isAsynch())
				{
					$link_gui->setMode("asynch");
				}

				$ret =& $this->ctrl->forwardCommand($link_gui);
				break;

			// Sourcecode
			case "ilpcsourcecodegui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCSourcecodeGUI.php");
				$src_gui =& new ilPCSourcecodeGUI($this->page, $cont_obj, $hier_id, $pc_id);
				$ret =& $this->ctrl->forwardCommand($src_gui);
				break;

			// Paragraph
			case "ilpcparagraphgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCParagraphGUI.php");
				$par_gui =& new ilPCParagraphGUI($this->page, $cont_obj, $hier_id, $pc_id);
				$par_gui->setEnableWikiLinks($this->page_gui->getEnabledWikiLinks());
				$par_gui->setStyleId($this->page_gui->getStyleId());
				$par_gui->setEnableInternalLinks($this->getEnableInternalLinks());
				$par_gui->setEnableKeywords($this->getEnableKeywords());
				$par_gui->setEnableAnchors($this->getEnableAnchors());
				$ret =& $this->ctrl->forwardCommand($par_gui);
				break;

			// Table
			case "ilpctablegui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCTableGUI.php");
				$tab_gui =& new ilPCTableGUI($this->page, $cont_obj, $hier_id, $pc_id);
				$tab_gui->setStyleId($this->page_gui->getStyleId());
				$ret =& $this->ctrl->forwardCommand($tab_gui);
				break;

			// Table Cell
			case "ilpctabledatagui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCTableDataGUI.php");
				$td_gui =& new ilPCTableDataGUI($this->page, $cont_obj, $hier_id, $pc_id);
				$ret =& $this->ctrl->forwardCommand($td_gui);
				break;

			// Data Table
			case "ilpcdatatablegui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCDataTableGUI.php");
				$tab_gui =& new ilPCDataTableGUI($this->page, $cont_obj, $hier_id, $pc_id);
				$tab_gui->setStyleId($this->page_gui->getStyleId());
				$tab_gui->setEnableInternalLinks($this->getEnableInternalLinks());
				$tab_gui->setEnableKeywords($this->getEnableKeywords());
				$tab_gui->setEnableAnchors($this->getEnableAnchors());
				$ret =& $this->ctrl->forwardCommand($tab_gui);
				break;

			// PC Media Object
			case "ilpcmediaobjectgui":
				include_once ("./Services/COPage/classes/class.ilPCMediaObjectGUI.php");

				$this->tabs_gui->clearTargets();
				$this->tabs_gui->setBackTarget($this->page_gui->page_back_title,
				$ilCtrl->getLinkTarget($this->page_gui, "edit"));
				$pcmob_gui =& new ilPCMediaObjectGUI($this->page, $cont_obj, $hier_id, $pc_id);
				$pcmob_gui->setStyleId($this->page_gui->getStyleId());
				$pcmob_gui->setEnabledMapAreas($this->page_gui->getEnabledInternalLinks());
				$ret =& $this->ctrl->forwardCommand($pcmob_gui);
				break;

			// only for "linked" media
			case "ilobjmediaobjectgui":
				$this->tabs_gui->clearTargets();
				$this->tabs_gui->setBackTarget($this->lng->txt("back"),
					$ilCtrl->getParentReturn($this));
				$mob_gui =& new ilObjMediaObjectGUI("", $_GET["mob_id"],false, false);
				$mob_gui->getTabs($this->tabs_gui);
				$mob_gui->setEnabledMapAreas($this->page_gui->getEnabledInternalLinks());
				$this->tpl->setTitle($this->lng->txt("mob").": ".
					ilObject::_lookupTitle($_GET["mob_id"]));
				$ret =& $this->ctrl->forwardCommand($mob_gui);
				break;

			// List
			case "ilpclistgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCListGUI.php");
				$list_gui =& new ilPCListGUI($this->page, $cont_obj, $hier_id, $pc_id);
				$list_gui->setStyleId($this->page_gui->getStyleId());
				$ret =& $this->ctrl->forwardCommand($list_gui);
				break;

			// List Item
			case "ilpclistitemgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCListItemGUI.php");
				$list_item_gui =& new ilPCListItemGUI($this->page, $cont_obj, $hier_id, $pc_id);
				//$ret =& $list_item_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($list_item_gui);
				break;

			// File List
			case "ilpcfilelistgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCFileListGUI.php");
				$file_list_gui =& new ilPCFileListGUI($this->page, $cont_obj, $hier_id, $pc_id);
				// scorm2004-start
				$file_list_gui->setStyleId($this->page_gui->getStyleId());
				// scorm2004-end
				//$ret =& $file_list_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($file_list_gui);
				break;

			// File List Item
			case "ilpcfileitemgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCFileItemGUI.php");
				$file_item_gui =& new ilPCFileItemGUI($this->page, $cont_obj, $hier_id, $pc_id);
				//$ret =& $file_item_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($file_item_gui);
				break;

			// Question
			case "ilpcquestiongui":
				include_once("./Services/COPage/classes/class.ilPCQuestionGUI.php");
				$pc_question_gui =& new ilPCQuestionGUI($this->page, $cont_obj, $hier_id, $pc_id);
				$pc_question_gui->setSelfAssessmentMode($this->page_gui->getEnabledSelfAssessment());
				$pc_question_gui->setPageConfig($this->page_gui->getPageConfig());

				if ($this->page_gui->getEnabledSelfAssessment())
				{
					$this->tabs_gui->clearTargets();
					$this->tabs_gui->setBackTarget($this->lng->txt("back"),
						$ilCtrl->getParentReturn($this));
					$ret = $this->ctrl->forwardCommand($pc_question_gui);
				}
				else
				{
					$cmd = $this->ctrl->getCmd();
					$pc_question_gui->$cmd();
					$this->ctrl->redirectByClass(array("ilobjquestionpoolgui", get_class($cont_obj)), "editQuestion");
				}
				break;


			// PlaceHolder
			case "ilpcplaceholdergui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCPlaceHolderGUI.php");	
				$plch_gui =& new ilPCPlaceHolderGUI($this->page, $cont_obj, $hier_id, $pc_id);
				$plch_gui->setEnableInternalLinks($this->getEnableInternalLinks());
				$plch_gui->setEnableKeywords($this->getEnableKeywords());
				$plch_gui->setEnableAnchors($this->getEnableAnchors());
				$plch_gui->setStyleId($this->page_gui->getStyleId());
				$ret =& $this->ctrl->forwardCommand($plch_gui);
				break;
					
			// Section
			case "ilpcsectiongui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCSectionGUI.php");
				$sec_gui =& new ilPCSectionGUI($this->page, $cont_obj, $hier_id, $pc_id);
				$sec_gui->setStyleId($this->page_gui->getStyleId());
				$ret =& $this->ctrl->forwardCommand($sec_gui);
				break;

			// Resources
			case "ilpcresourcesgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCResourcesGUI.php");
				$res_gui =& new ilPCResourcesGUI($this->page, $cont_obj, $hier_id, $pc_id);
				$ret =& $this->ctrl->forwardCommand($res_gui);
				break;

			// Login Page elements
			case 'ilpcloginpageelementgui':
				$this->tabs_gui->clearTargets();
				include_once './Services/COPage/classes/class.ilPCLoginPageElementGUI.php';
				$res_gui = new ilPCLoginPageElementGUI($this->page,$cont_obj,$hier_id,$pc_id);
				$ret = $this->ctrl->forwardCommand($res_gui);
				break;

			// Map
			case "ilpcmapgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCMapGUI.php");
				$map_gui =& new ilPCMapGUI($this->page, $cont_obj, $hier_id, $pc_id);
				$ret =& $this->ctrl->forwardCommand($map_gui);
				break;

			// Tabs
			case "ilpctabsgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCTabsGUI.php");
				$tabs_gui =& new ilPCTabsGUI($this->page, $cont_obj, $hier_id, $pc_id);
				$tabs_gui->setStyleId($this->page_gui->getStyleId());
				$ret =& $this->ctrl->forwardCommand($tabs_gui);
				break;

			// Tab
			case "ilpctabgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCTabGUI.php");
				$tab_gui = new ilPCTabGUI($this->page, $cont_obj, $hier_id, $pc_id);
				//$ret =& $list_item_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($tab_gui);
				break;

			// Plugged Component
			case "ilpcpluggedgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCPluggedGUI.php");
				$plugged_gui =& new ilPCPluggedGUI($this->page, $cont_obj, $hier_id,
					$add_type, $pc_id);
				$ret =& $this->ctrl->forwardCommand($plugged_gui);
				break;

			// Content Include
			case "ilpccontentincludegui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCContentIncludeGUI.php");
				$incl_gui = new ilPCContentIncludeGUI($this->page, $cont_obj, $hier_id, $pc_id);
				$ret =& $this->ctrl->forwardCommand($incl_gui);
				break;

			// Interactive Image
			case "ilpcinteractiveimagegui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCInteractiveImageGUI.php");
				$iim_gui = new ilPCInteractiveImageGUI($this->page, $cont_obj, $hier_id, $pc_id);
				$ret = $this->ctrl->forwardCommand($iim_gui);
				break;

			// Profile
			case "ilpcprofilegui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCProfileGUI.php");
				$prof_gui = new ilPCProfileGUI($this->page, $cont_obj, $hier_id, $pc_id);
				$ret = $this->ctrl->forwardCommand($prof_gui);
				break;
			
			// Verification
			case "ilpcverificationgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCVerificationGUI.php");
				$vrfc_gui = new ilPCVerificationGUI($this->page, $cont_obj, $hier_id, $pc_id);
				$ret = $this->ctrl->forwardCommand($vrfc_gui);
				break;
			
			// Blog
			case "ilpcbloggui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCBlogGUI.php");
				$blog_gui = new ilPCBlogGUI($this->page, $cont_obj, $hier_id, $pc_id);
				$ret = $this->ctrl->forwardCommand($blog_gui);
				break;
				
			// Question Overview
			case "ilpcquestionoverviewgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCQuestionOverviewGUI.php");
				$qover_gui =& new ilPCQuestionOverviewGUI($this->page, $cont_obj, $hier_id, $pc_id);
				$ret = $this->ctrl->forwardCommand($qover_gui);
				break;

			default:
				if ($cmd == "pasteFromClipboard")
				{
					$ret = $this->pasteFromClipboard($hier_id);
				}
				else if ($cmd == "paste")
				{
					$ret = $this->paste($hier_id);
				}
				else
				{
					$ret = $this->$cmd();
				}
				break;

		}

		return $ret;
	}
	
	/**
	* checks if current user has activated js editing and
	* if browser is js capable
	*/
	function _doJSEditing()
	{
		global $ilUser, $ilias, $ilSetting;

		if ($ilUser->getPref("ilPageEditor_JavaScript") != "disable"
			&& $ilSetting->get("enable_js_edit", 1)
			&& ilPageEditorGUI::_isBrowserJSEditCapable())
		{
			return true;
		}
		return false;
	}

	/**
	* checks wether browser is javascript editing capable
	*/
	function _isBrowserJSEditCapable()
	{
		global $ilBrowser;
return true;
		$version = $ilBrowser->getVersion();

		if ($ilBrowser->isFirefox() ||
			($ilBrowser->isIE() && !$ilBrowser->isMac()) ||
			($ilBrowser->isMozilla() && $version[0] >= 5))
		{
			return true;
		}
		return false;
	}

	function activatePage()
	{
		$this->page_gui->activatePage();
	}

	function deactivatePage()
	{
		$this->page_gui->deactivatePage();
	}

	/**
	* set media and editing mode
	*/
	function setMediaMode()
	{
		global $ilUser, $ilias;

		$ilUser->writePref("ilPageEditor_MediaMode", $_POST["media_mode"]);
		$ilUser->writePref("ilPageEditor_HTMLMode", $_POST["html_mode"]);
		if ($ilias->getSetting("enable_js_edit"))
		{
			if ($ilUser->getPref("ilPageEditor_JavaScript") != $_POST["js_mode"])
			{
				// not nice, should be solved differently in the future
				if ($this->page->getParentType() == "lm" ||
					$this->page->getParentType() == "dbk")
				{
					$this->ctrl->setParameterByClass("illmpageobjectgui", "reloadTree", "y");
				}
			}
			$ilUser->writePref("ilPageEditor_JavaScript", $_POST["js_mode"]);
		}
		
		// again not so nice...
		if ($this->page->getParentType() == "lm" ||
			$this->page->getParentType() == "dbk")
		{
			$this->ctrl->redirectByClass("illmpageobjectgui", "edit");
		}
		else
		{
			$this->ctrl->returnToParent($this);
		}
	}
	
	/**
	* copy linked media object to clipboard
	*/
	function copyLinkedMediaToClipboard()
	{
		global $ilUser;
		
		ilUtil::sendSuccess($this->lng->txt("copied_to_clipboard"), true);
		$ilUser->addObjectToClipboard($_POST["mob_id"], "mob", ilObject::_lookupTitle($_POST["mob_id"]));
		$this->ctrl->returnToParent($this);
	}

	/**
	* copy linked media object to media pool
	*/
	function copyLinkedMediaToMediaPool()
	{
		global $ilUser;
		
		$this->ctrl->setParameterByClass("ilmediapooltargetselector", "mob_id", $_POST["mob_id"]); 
		$this->ctrl->redirectByClass("ilmediapooltargetselector", "listPools");
	}
	
	/**
	* add change comment to history
	*/
	function addChangeComment()
	{
		include_once("classes/class.ilHistory.php");
		ilHistory::_createEntry($this->page->getId(), "update",
			"", $this->page->getParentType().":pg",
			ilUtil::stripSlashes($_POST["change_comment"]), true);
		ilUtil::sendSuccess($this->lng->txt("cont_added_comment"), true);
		$this->ctrl->returnToParent($this);
	}
	
	/**
	 * Delete selected items
	 */
	function deleteSelected()
	{
		if (is_int(strpos($_POST["target"][0], ";")))
		{
			$_POST["target"] = explode(";", $_POST["target"][0]);
		}
		if (is_array($_POST["target"]))
		{
			$updated = $this->page->deleteContents($_POST["target"], true,
				$this->page_gui->getEnabledSelfAssessment());
			if($updated !== true)
			{
				$_SESSION["il_pg_error"] = $updated;
			}
			else
			{
				unset($_SESSION["il_pg_error"]);
			}
		}
		$this->ctrl->returnToParent($this);
	}

	/**
	 * Copy selected items
	 */
	function copySelected()
	{
		if (is_int(strpos($_POST["target"][0], ";")))
		{
			$_POST["target"] = explode(";", $_POST["target"][0]);
		}
		if (is_array($_POST["target"]))
		{
			$this->page->copyContents($_POST["target"]);
		}
		$this->ctrl->returnToParent($this);
	}

	/**
	 * Cut selected items
	 */
	function cutSelected()
	{
		if (is_int(strpos($_POST["target"][0], ";")))
		{
			$_POST["target"] = explode(";", $_POST["target"][0]);
		}
		if (is_array($_POST["target"]))
		{
			$updated = $this->page->cutContents($_POST["target"]);
			if($updated !== true)
			{
				$_SESSION["il_pg_error"] = $updated;
			}
			else
			{
				unset($_SESSION["il_pg_error"]);
			}
		}
		$this->ctrl->returnToParent($this);
	}

	/**
	 * paste from clipboard (redirects to clipboard)
	 */
	function paste($a_hier_id)
	{
		global $ilCtrl;
		$this->page->pasteContents($a_hier_id, $this->page_gui->getEnabledSelfAssessment());
		include_once("./Modules/LearningModule/classes/class.ilEditClipboard.php");
		ilEditClipboard::setAction("");
		$this->ctrl->returnToParent($this);
	}

	/**
	* (de-)activate selected items
	*/
	function activateSelected()
	{
		if (is_int(strpos($_POST["target"][0], ";")))
		{
			$_POST["target"] = explode(";", $_POST["target"][0]);
		}
		if (is_array($_POST["target"]))
		{
			$updated = $this->page->switchEnableMultiple($_POST["target"], true,
				$this->page_gui->getEnabledSelfAssessment());
			if($updated !== true)
			{
				$_SESSION["il_pg_error"] = $updated;
			}
			else
			{
				unset($_SESSION["il_pg_error"]);
			}
		}
		$this->ctrl->returnToParent($this);
	}

	/**
	* Assign characeristic to text blocks/sections
	*/
	function assignCharacteristicForm()
	{
		global $tpl, $lng;
		
		if (is_int(strpos($_POST["target"][0], ";")))
		{
			$_POST["target"] = explode(";", $_POST["target"][0]);
		}
		if (is_array($_POST["target"]))
		{
			$types = array();
			
			// check what content element types have been selected
			foreach ($_POST["target"] as $t)
			{
				$tarr = explode(":", $t);
				$cont_obj =& $this->page->getContentObject($tarr[0], $tarr[1]);
				if (is_object($cont_obj) && $cont_obj->getType() == "par")
				{
					$types["par"] = "par";
				}
				if (is_object($cont_obj) && $cont_obj->getType() == "sec")
				{
					$types["sec"] = "sec";
				}
			}
		
			if (count($types) == 0)
			{
				ilUtil::sendFailure($lng->txt("cont_select_par_or_section"), true);
				$this->ctrl->returnToParent($this);
			}
			else
			{
				$this->initCharacteristicForm($_POST["target"], $types);
				$tpl->setContent($this->form->getHTML());
			}
		}
		else
		{
			$this->ctrl->returnToParent($this);
		}
	}

	/**
	* Init map creation/update form
	*/
	function initCharacteristicForm($a_target, $a_types)
	{
		global $ilCtrl, $lng;
		
		
		// edit form
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setTitle($this->lng->txt("cont_choose_characteristic"));
		
		if ($a_types["par"] == "par")
		{
			$select_prop = new ilSelectInputGUI($this->lng->txt("cont_choose_characteristic_text"),
				"char_par");
			include_once("./Services/COPage/classes/class.ilPCParagraphGUI.php");
			$options = ilPCParagraphGUI::_getCharacteristics($this->page_gui->getStyleId());
			$select_prop->setOptions($options);
			$this->form->addItem($select_prop);
		}
		if ($a_types["sec"] == "sec")
		{
			$select_prop = new ilSelectInputGUI($this->lng->txt("cont_choose_characteristic_section"),
				"char_sec");
			include_once("./Services/COPage/classes/class.ilPCSectionGUI.php");
			$options = ilPCSectionGUI::_getCharacteristics($this->page_gui->getStyleId());
			$select_prop->setOptions($options);
			$this->form->addItem($select_prop);
		}
		
		foreach ($a_target as $t)
		{
			$hidden = new ilHiddenInputGUI("target[]");
			$hidden->setValue($t);
			$this->form->addItem($hidden);
		}

		$this->form->setFormAction($ilCtrl->getFormAction($this));
		$this->form->addCommandButton("assignCharacteristic", $lng->txt("save"));
		$this->form->addCommandButton("showPage", $lng->txt("cancel"));

	}

	/**
	* Assign characteristic
	*/
	function assignCharacteristic()
	{
		$char_par = ilUtil::stripSlashes($_POST["char_par"]);
		$char_sec = ilUtil::stripSlashes($_POST["char_sec"]);
		if (is_array($_POST["target"]))
		{
			foreach ($_POST["target"] as $t)
			{
				$tarr = explode(":", $t);
				$cont_obj =& $this->page->getContentObject($tarr[0], $tarr[1]);
				if (is_object($cont_obj) && $cont_obj->getType() == "par")
				{
					$cont_obj->setCharacteristic($char_par);
				}
				if (is_object($cont_obj) && $cont_obj->getType() == "sec")
				{
					$cont_obj->setCharacteristic($char_sec);
				}
			}
			$updated = $this->page->update();
			if($updated !== true)
			{
				$_SESSION["il_pg_error"] = $updated;
			}
			else
			{
				unset($_SESSION["il_pg_error"]);
			}
		}
		$this->ctrl->returnToParent($this);
	}

	/**
	* paste from clipboard (redirects to clipboard)
	*/
	function pasteFromClipboard($a_hier_id)
	{
		global $ilCtrl;
//var_dump($a_hier_id);
		$ilCtrl->setParameter($this, "hier_id", $a_hier_id);
		$ilCtrl->setParameterByClass("ilEditClipboardGUI", "returnCommand",
			rawurlencode($ilCtrl->getLinkTarget($this,
			"insertFromClipboard", "", false, false)));
//echo ":".$ilCtrl->getLinkTarget($this, "insertFromClipboard").":";
		$ilCtrl->redirectByClass("ilEditClipboardGUI", "getObject");
	}

	/**
	* insert object from clipboard
	*/
	function insertFromClipboard()
	{
		include_once("./Services/Clipboard/classes/class.ilEditClipboardGUI.php");
		$ids = ilEditClipboardGUI::_getSelectedIDs();
		include_once ("./Services/COPage/classes/class.ilPCMediaObject.php");
		if ($ids != "")
		{
			foreach ($ids as $id2)
			{
				$id = explode(":", $id2);
				$type = $id[0];
				$id = $id[1];
				if ($type == "mob")
				{
					$this->content_obj = new ilPCMediaObject($this->page->getDom());
					$this->content_obj->readMediaObject($id);
					$this->content_obj->createAlias($this->page, $_GET["hier_id"]);
					$this->updated = $this->page->update();
				}
				if ($type == "incl")
				{
					include_once("./Services/COPage/classes/class.ilPCContentInclude.php");
					$this->content_obj = new ilPCContentInclude($this->page->getDom());
					$this->content_obj->create($this->page, $_GET["hier_id"]);
					$this->content_obj->setContentType("mep");
					$this->content_obj->setContentId($id);
					$this->updated = $this->page->update();
				}
			}
		}
		$this->ctrl->returnToParent($this);
	}

	/**
	* Default for POST reloads and missing 
	*/
	function displayPage()
	{
		$this->ctrl->returnToParent($this);
	}
	
	/**
	* display locator
	*/
	function displayLocator()
	{
		if(is_object($this->locator))
		{
			$this->locator->display();
		}
	}

	/**
	* Show snippet info
	*/
	function showSnippetInfo()
	{
		global $tpl, $lng, $ilAccess, $ilCtrl;
		
		$stpl = new ilTemplate("tpl.snippet_info.html", true, true, "Services/COPage");
		
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
		$mep_pools = ilMediaPoolItem::getPoolForItemId($_POST["ci_id"]);
		foreach ($mep_pools as $mep_id)
		{
			$ref_ids = ilObject::_getAllReferences($mep_id);
			$edit_link = false;
			foreach ($ref_ids as $rid)
			{
				if (!$edit_link && $ilAccess->checkAccess("write", "", $rid))
				{
					$stpl->setCurrentBlock("edit_link");
					$stpl->setVariable("TXT_EDIT", $lng->txt("edit"));
					$stpl->setVariable("HREF_EDIT",
						"./goto.php?target=mep_".$rid);
					$stpl->parseCurrentBlock();
				}
			}
			$stpl->setCurrentBlock("pool");
			$stpl->setVariable("TXT_MEDIA_POOL", $lng->txt("obj_mep"));
			$stpl->setVariable("VAL_MEDIA_POOL", ilObject::_lookupTitle($mep_id));
			$stpl->parseCurrentBlock();
		}
		
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolPage.php");
		$stpl->setVariable("TXT_TITLE", $lng->txt("title"));
		$stpl->setVariable("VAL_TITLE", ilMediaPoolPage::lookupTitle($_POST["ci_id"]));
		$stpl->setVariable("TXT_BACK", $lng->txt("back"));
		$stpl->setVariable("HREF_BACK",
			$ilCtrl->getLinkTargetByClass("ilpageobjectgui", "edit"));
		$tpl->setContent($stpl->get());
	}
	
}
?>
