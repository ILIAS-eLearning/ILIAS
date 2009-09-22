<?php
/*
 +-----------------------------------------------------------------------------+
 | ILIAS open source                                                           |
 +-----------------------------------------------------------------------------+
 | Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

require_once("./Modules/Scorm2004/classes/class.ilSCORM2004NodeGUI.php");
require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Sco.php");
require_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Objective.php");

/**
 * Class ilSCORM2004ScoGUI
 *
 * User Interface for Scorm 2004 SCO Nodes
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilSCORM2004ScoGUI: ilMDEditorGUI, ilNoteGUI, ilPCQuestionGUI, ilSCORM2004PageGUI
 *
 * @ingroup ModulesScorm2004
 */
class ilSCORM2004ScoGUI extends ilSCORM2004NodeGUI
{

	/**
	 * Constructor
	 * @access	public
	 */

	var $ctrl = null;

	function ilSCORM2004ScoGUI($a_slm_obj, $a_node_id = 0)
	{
		global $ilCtrl;

		$ilCtrl->saveParameter($this, "obj_id");
		$this->ctrl = &$ilCtrl;

		parent::ilSCORM2004NodeGUI($a_slm_obj, $a_node_id);
	}

	/**
	 * Get Node Type
	 */
	function getType()
	{
		return "sco";
	}

	/**
	 * execute command
	 */
	function &executeCommand()
	{
		global $tpl, $ilCtrl, $ilTabs;

		$tpl->getStandardTemplate();

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();

		switch($next_class)
		{
			// notes
			case "ilnotegui":
				switch($_GET["notes_mode"])
				{
					default:
						$ilTabs->setTabActive("sahs_organization");
						return $this->showOrganization();
				}
				break;

			case 'ilmdeditorgui':
				$this->setTabs();
				$this->setLocator();
				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';

				$md_gui =& new ilMDEditorGUI($this->slm_object->getID(),
				$this->node_object->getId(), $this->node_object->getType());
				$md_gui->addObserver($this->node_object,'MDUpdateListener','General');
				$ilCtrl->forwardCommand($md_gui);
				break;
				
			case 'ilscorm2004pagegui':
				include_once("./Modules/Scorm2004/classes/class.ilSCORM2004PageGUI.php");
				$page_obj = new ilSCORM2004PageGUI("sahs",$_GET["pg_id"]);
				//$page_obj->setPresentationTitle($page["title"]);
				$page_obj->setOutputMode(IL_PAGE_PREVIEW);
				$ilCtrl->forwardCommand($page_obj);
				break;

			default:
				$ret =& $this->$cmd();
				break;
		}
	}

	/**
	 * Show Sequencing
	 */
	function showProperties()
	{
		global $tpl,$lng,$ilTabs;


		$this->setTabs();
		$this->setLocator();
		$ilTabs->setTabActive("sahs_learning_objectives");
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		// load template for table
		$tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");

		// load template for table content data
		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.scormeditor_sco_properties.html", "Modules/Scorm2004");

		$tbl = new ilTableGUI();
		$tbl->enable("action");
		$tbl->disable("sort");
		$tbl->setTitle("Learning Objectives for ".$this->node_object->getTitle());
		$tbl->setHeaderNames(array("", $lng->txt("title"),"Scope"));
		$cols = array("", "title","scope");
		$tbl->setHeaderVars($cols, $header_params);

		$tr_data = $this->node_object->getObjectives();

		$tpl->setVariable("COLUMN_COUNTS", 3);

		$tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME", "updateProperties");
		$tpl->setVariable("BTN_VALUE",  $lng->txt("save"));
		$tpl->parseCurrentBlock();
		$tbl->render();

		foreach ($tr_data as $data)
		{
			$tpl->setCurrentBlock("tbl_content");
			$tpl->setVariable("TITLE", $data->getObjectiveID());
			$tpl->setVariable("NODE_ID", "obj_".$data->getId());
			$tpl->setVariable("ICON" , ilUtil::getImagePath("icon_lobj.gif"));

			$mappings = $data->getMappings();
			$mapinfo = null;
			foreach($mappings as $map) {
				$mapinfo .= $map->getTargetObjectiveID();
			}
			if ($mapinfo == null) {
				$mapinfo = "local";
			} else {
				$mapinfo = "global to ".$mapinfo;
			}
			$tpl->setVariable("REFERENCE", $mapinfo);
			$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
			$tpl->setVariable("CSS_ROW", $css_row);
			$tpl->parseCurrentBlock();
		}
		//block sequencing rules


		//$tpl->touchBlock("adm_content");
	}

	/**
	 * update Properties
	 */
	function updateProperties()
	{
		global $tpl,$lng;
		$empty = false;

		foreach ($_POST as $key=>$value) {
			if(preg_match('/(obj_)(.+)/', $key,$match)){
				$objective = new ilScorm2004Objective($this->node_object->getId(),$match[2]);
				if (!$value)
				{
					$empty=true;
				} else {
					$objective->setObjectiveID($value);
					$objective->updateObjective();
				}
			}
		}
		if (!$empty) {
			ilUtil::sendInfo($lng->txt("saved_successfully"),true);
		} else {
			ilUtil::sendInfo("Objective titles can't be blank",true);
		}
		$this->showProperties();
	}

	function sahs_questions()
	{
		global $tpl,$lng, $ilCtrl;

		$this->setTabs();
		$this->setLocator();

		include_once "./Services/Table/classes/class.ilTableGUI.php";
		include_once "./Modules/Scorm2004/classes/class.ilSCORM2004Page.php";
		include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
		include_once("./Services/COPage/classes/class.ilPCQuestionGUI.php");

		// load template for table
		$tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.scormeditor_sco_question.html", "Modules/Scorm2004");

		$tbl = new ilTableGUI();
		$tbl->setTitle("Questions for ".$this->node_object->getTitle());
		$tbl->setHeaderNames(array("Question","Page"));
		$cols = array("question","page");
		$tbl->setHeaderVars($cols, $header_params);
		$tbl->setColumnWidth(array("50%", "50%"));
		$tbl->disable("sort");
		$tbl->disable("footer");

		$tree = new ilTree($this->slm_object->getId());
		$tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		$tree->setTreeTablePK("slm_id");

		foreach($tree->getSubTree($tree->getNodeData($this->node_object->getId()),true,'page') as $page)
		{
			// get question ids
			$qids = ilSCORM2004Page::_getQuestionIdsForPage("sahs", $page["obj_id"]);
			if (count($qids) > 0)
			{
				// output questions
				foreach ($qids as $qid)
				{
					$tpl->setCurrentBlock("tbl_content");
					$tpl->setVariable("TXT_PAGE_TITLE", $page["title"]);
					$ilCtrl->setParameterByClass("ilscorm2004pagenodegui", "obj_id", $page["obj_id"]);
					$tpl->setVariable("HREF_EDIT_PAGE", $ilCtrl->getLinkTargetByClass("ilscorm2004pagenodegui", "edit"));
					
					$qtitle = assQuestion::_getTitle($qid);
					$tpl->setVariable("TXT_QUESTION", $qtitle);
					$ilCtrl->setParameterByClass("ilscorm2004pagenodegui", "obj_id", $page["obj_id"]);
					$tpl->setVariable("HREF_EDIT_QUESTION", $ilCtrl->getLinkTargetByClass("ilscorm2004pagenodegui", "edit"));
					
					$tpl->setVariable("CSS_ROW", ilUtil::switchColor($i++, "tblrow1", "tblrow2"));
					$tpl->parseCurrentBlock();
				}
			}
		}
		$tbl->render();
	}

	function getEditTree()
	{
		$slm_tree = new ilTree($this->node_object->getId(),$this->slm_object->getId());
		$slm_tree->setTreeTablePK("slm_id");
		$slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		return $slm_tree;
	}

	/**
	 * output tabs
	 */
	function setTabs()
	{
		global $ilTabs, $ilCtrl, $tpl, $lng;

		// subelements
		$ilTabs->addTarget("sahs_organization",
		$ilCtrl->getLinkTarget($this,'showOrganization'),
			 "showOrganization", get_class($this));

		// properties (named learning objectives, since here is currently
		// no other property)
		$ilTabs->addTarget("sahs_learning_objectives",
		$ilCtrl->getLinkTarget($this,'showProperties'),
			 "showProperties", get_class($this));

		// questions
		$ilTabs->addTarget("sahs_questions",
		$ilCtrl->getLinkTarget($this,'sahs_questions'),
			 "sahs_questions", get_class($this));

		// preview
		$ilTabs->addTarget("cont_preview",
		$ilCtrl->getLinkTarget($this,'sco_preview'),
			 "sco_preview", get_class($this));

		// resources 
		$ilTabs->addTarget("cont_files",
		$ilCtrl->getLinkTarget($this,'sco_resources'),
			 "sco_resources", get_class($this));
		
		// metadata
		$ilTabs->addTarget("meta_data",
		$ilCtrl->getLinkTargetByClass("ilmdeditorgui",''),
			 "", "ilmdeditorgui");
		
		// export
		$ilTabs->addTarget("export",
		$ilCtrl->getLinkTarget($this, "showExportList"), "showExportList",
		get_class($this));
		
		// import
		$ilTabs->addTarget("import",
		$ilCtrl->getLinkTarget($this, "import"), "import",
		get_class($this));
		
		$tpl->setTitleIcon(ilUtil::getImagePath("icon_sco_b.gif"));
		$tpl->setTitle(
		$lng->txt("sahs_unit").": ".$this->node_object->getTitle());
	}

	/**
	 * Perform drag and drop action
	 */
	function proceedDragDrop()
	{
		global $ilCtrl;

		//echo "-".$_POST["il_hform_source_id"]."-".$_POST["il_hform_target_id"]."-".$_POST["il_hform_fc"]."-";
		$this->slm_object->executeDragDrop($_POST["il_hform_source_id"], $_POST["il_hform_target_id"],
		$_POST["il_hform_fc"], $_POST["il_hform_as_subitem"]);
		$ilCtrl->redirect($this, "showOrganization");
	}

	function sco_preview()
	{
		global $tpl, $ilCtrl, $lng;
		
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$tpl->setCurrentBlock("ContentStyle");
		$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath($this->slm_object->getStyleSheetId()));
		$tpl->parseCurrentBlock();
		
		$tpl->addJavaScript("./Modules/Scorm2004/scripts/questions/jquery.js");
		$tpl->addJavaScript("./Modules/Scorm2004/scripts/questions/jquery-ui-min.js");
		$tpl->addJavaScript("./Modules/Scorm2004/scripts/questions/pure.js");
		
		$tpl->addJavaScript("./Modules/Scorm2004/scripts/pager.js");

		$this->setTabs();
		$this->setLocator();
		
		$tree = new ilTree($this->slm_object->getId());
		$tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		$tree->setTreeTablePK("slm_id");
		include_once "./Modules/Scorm2004/classes/class.ilSCORM2004PageGUI.php";
		include_once "./Services/MetaData/classes/class.ilMD.php";
		
		$meta = new ilMD($this->node_object->getSLMId(), $this->node_object->getId(), $this->node_object->getType());
				$desc_ids = $meta->getGeneral()->getDescriptionIds();
				$sco_description = $meta->getGeneral()->getDescription($desc_ids[0])->getDescription();
		
		// @todo
		// Why is that much HTML code in an application class?
		// Please extract all this HTML to a tpl.<t_name>.html file and use
		// placeholders and the template engine to insert data.
		//
		// There copy/paste code residenting in ilSCORM2004Sco. This
		// should be merged.
		//
		// alex, 4 Apr 09
		//

		$output =	'<!-- BEGIN ilLMNavigation -->
					<div class="ilc_page_tnav_TopNavigation">
					<!-- BEGIN ilLMNavigation_Prev -->
					<div class="ilc_page_lnav_LeftNavigation">
					<a class="ilc_page_lnavlink_LeftNavigationLink">
					<img class="ilc_page_lnavimage_LeftNavigationImage" border="0" src="/templates/default/images/spacer.gif" alt="" title="" class="ilc_page_rnavimage_RightNavigationImage" />&nbsp;Prev</a>
					</div>
					<!-- END ilLMNavigation_Prev -->
					<!-- BEGIN ilLMNavigation_Next -->
					<div class="ilc_page_rnav_RightNavigation">
					<a class="ilc_page_rnavlink_RightNavigationLink">Next&nbsp;<img class="ilc_page_rnavimage_RightNavigationImage" border="0" src="/templates/default/images/spacer.gif" alt="" title="" class="ilc_page_rnavimage_RightNavigationImage" /></a>
					</div>
					<!-- END ilLMNavigation_Next -->
					<div style="clear:both;"></div>
					</div>
					<!-- END ilLMNavigation -->';
		
		$output .='<table class="ilc_page_cont_PageContainer" width="100%" cellspacing="0" cellpadding="0" style="display: table;"><tbody><tr><td><div class="ilc_page_Page"><div class="ilc_sco_title_Title">'.$this->node_object->getTitle().'</div>';
		
		// sco description
		if (trim($sco_description) != "")
		{
			$output .='<div class="ilc_sco_desct_DescriptionTop">'.$lng->txt("description").'</div>';
			$output .='<div class="ilc_sco_desc_Description">'.$sco_description.'</div>';
		}

		// sco objective(s)
		$objs = $this->node_object->getObjectives();
		if (count($objs) > 0)
		{
			$output .='<div class="ilc_sco_objt_ObjectiveTop">'.$lng->txt("sahs_objectives").'</div>';
			foreach ($objs as $objective)
			{
				$output .= '<div class="ilc_sco_obj_Objective">'.nl2br($objective->getObjectiveID()).'</div>';
			}
			$output .= "</div>";
		}
		$output .='</td></tr></table>';
		
		foreach($tree->getSubTree($tree->getNodeData($this->node_object->getId()),true,'page') as $page)
		{
			$page_obj = new ilSCORM2004PageGUI($this->node_object->getType(),$page["obj_id"]);
			$page_obj->setPresentationTitle($page["title"]);
			$page_obj->setOutputMode(IL_PAGE_PREVIEW);
			$output .= $page_obj->showPage("export");
		}
		$output .=	'<!-- BEGIN ilLMNavigation2 -->
					<div class="ilc_page_bnav_BottomNavigation">
					<!-- BEGIN ilLMNavigation_Prev -->
					<div class="ilc_page_lnav_LeftNavigation">
					<a class="ilc_page_lnavlink_LeftNavigationLink">
					<img class="ilc_page_lnavimage_LeftNavigationImage" border="0" src="/templates/default/images/spacer.gif" alt="" title="" class="ilc_page_rnavimage_RightNavigationImage" />&nbsp;Prev</a>
					</div>
					<!-- END ilLMNavigation_Prev -->
					<!-- BEGIN ilLMNavigation_Next -->
					<div class="ilc_page_rnav_RightNavigation">
					<a class="ilc_page_rnavlink_RightNavigationLink">Next&nbsp;<img class="ilc_page_rnavimage_RightNavigationImage" border="0" src="/templates/default/images/spacer.gif" alt="" title="" class="ilc_page_rnavimage_RightNavigationImage" /></a>
					</div>
					<!-- END ilLMNavigation_Next -->
					<div style="clear:both;"></div>
					</div>
					<!-- END ilLMNavigation2 -->';

		//insert questions
		require_once './Modules/Scorm2004/classes/class.ilQuestionExporter.php';
		$output = preg_replace_callback("/(Question;)(il__qst_[0-9]+)/",array(get_class($this), 'insertQuestion'),$output);
		$output = preg_replace("/&#123;/","",$output);
		$output = preg_replace("/&#125;/","",$output);
		$output = "<script>var ScormApi=null;".ilQuestionExporter::questionsJS()."</script>".$output;
		
		$tpl->addJavaScript("./Modules/Scorm2004/scripts/questions/question_handling.js");
		//inline JS
		$output .='<script type="text/javascript" src="./Modules/Scorm2004/scripts/questions/question_handling.js"></script>';
		$tpl->setContent($output);
		
	}
	
	//callback function for question export
	private function insertQuestion($matches) {
		$q_exporter = new ilQuestionExporter(false);
		return $q_exporter->exportQuestion($matches[2]);
	}
	
	function showExportList()
	{
		global $tpl, $ilCtrl, $lng;
		
		$this->setTabs();
		$this->setLocator();
		
		$tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		//create SCORM 1.2 export file button
		$tpl->setCurrentBlock("btn_cell");
		$tpl->setVariable("BTN_LINK", $ilCtrl->getLinkTarget($this, "exportScorm12"));
		$tpl->setVariable("BTN_TXT", $lng->txt("scorm_create_export_file_scrom12"));
		$tpl->parseCurrentBlock();

		//create SCORM 2004 export file button
		$tpl->setCurrentBlock("btn_cell");
		$tpl->setVariable("BTN_LINK", $ilCtrl->getLinkTarget($this, "exportScorm2004"));
		$tpl->setVariable("BTN_TXT", $lng->txt("scorm_create_export_file_scrom2004"));
		$tpl->parseCurrentBlock();

		//create PDF export file button
		$tpl->setCurrentBlock("btn_cell");
		$tpl->setVariable("BTN_LINK", $ilCtrl->getLinkTarget($this, "exportPDF"));
		$tpl->setVariable("BTN_TXT", $lng->txt("scorm_create_export_file_pdf"));
		$tpl->parseCurrentBlock();

		//create HTML export file button
		$tpl->setCurrentBlock("btn_cell");
		$tpl->setVariable("BTN_LINK", $ilCtrl->getLinkTarget($this, "exportHTML"));
		$tpl->setVariable("BTN_TXT", $lng->txt("scorm_create_export_file_html"));
		$tpl->parseCurrentBlock();
		
		$export_files = $this->node_object->getExportFiles();

		// create table
		require_once("./Services/Table/classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI();

		// load files templates
		$tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");

		// load template for table content data
		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.export_file_row.html", "Modules/LearningModule");

		$num = 0;

		$tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$tbl->setTitle($lng->txt("cont_export_files"));

		$tbl->setHeaderNames(array("", $lng->txt("type"),
			$lng->txt("cont_file"),
			$lng->txt("cont_size"), $lng->txt("date") ));

		$cols = array("", "type", "file", "size", "date");
		$header_params = array("ref_id" => $_GET["ref_id"], "baseClass" => $_GET["baseClass"],
			"cmd" => "showExportList", "cmdClass" => strtolower(get_class($this)),
			"cmdNode" => $_GET["cmdNode"], "obj_id" => $_GET["obj_id"]);
		$tbl->setHeaderVars($cols, $header_params);
		$tbl->setColumnWidth(array("1%", "9%", "40%", "25%", "25%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);		// ???
		$tbl->disable("sort");


		$tpl->setVariable("COLUMN_COUNTS", 5);

		// delete button
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME", "confirmDeleteExportFile");
		$tpl->setVariable("BTN_VALUE", $lng->txt("delete"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME", "downloadExportFile");
		$tpl->setVariable("BTN_VALUE", $lng->txt("download"));
		$tpl->parseCurrentBlock();

//		$tpl->setCurrentBlock("tbl_action_btn");
//		$tpl->setVariable("BTN_NAME", "publishExportFile");
//		$tpl->setVariable("BTN_VALUE", $lng->txt("cont_public_access"));
//		$tpl->parseCurrentBlock();

		// footer
		$tbl->setFooter("tblfooter",$lng->txt("previous"),$lng->txt("next"));
		//$tbl->disable("footer");

		$tbl->setMaxCount(count($export_files));
		$export_files = array_slice($export_files, $_GET["offset"], $_GET["limit"]);
		$tbl->render();
		if(count($export_files) > 0)
		{
			$i=0;
			foreach($export_files as $exp_file)
			{
				$tpl->setCurrentBlock("tbl_content");
				$tpl->setVariable("TXT_FILENAME", $exp_file["file"]);

				$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
				$tpl->setVariable("CSS_ROW", $css_row);

				$tpl->setVariable("TXT_SIZE", $exp_file["size"]);
				//$public_str = ($exp_file["file"] == $this->node_object->getPublicExportFile($exp_file["type"]))
				//	? " <b>(".$lng->txt("public").")<b>"
				//	: "";
				$tpl->setVariable("TXT_TYPE", $exp_file["type"]);
				$tpl->setVariable("CHECKBOX_ID", $exp_file["type"].":".$exp_file["file"]);

				$file_arr = explode("__", $exp_file["file"]);
				$tpl->setVariable("TXT_DATE", date("Y-m-d H:i:s",$file_arr[0]));

				$tpl->parseCurrentBlock();
			}
		} //if is_array
		else
		{
			$tpl->setCurrentBlock("notfound");
			$tpl->setVariable("TXT_OBJECT_NOT_FOUND", $lng->txt("obj_not_found"));
			$tpl->setVariable("NUM_COLS", 4);
			$tpl->parseCurrentBlock();
		}
		$tpl->parseCurrentBlock();
		
	}
	
	function exportScorm2004()
	{
		$export = new ilScorm2004Export($this->node_object,'SCORM 2004');
		$export->buildExportFile();
		$this->ctrl->redirect($this, "showExportList");
	}
	
	function exportScorm12()
	{
		$export = new ilScorm2004Export($this->node_object,'SCORM 1.2');
		$export->buildExportFile();
		$this->ctrl->redirect($this, "showExportList");
	}
	
	function exportHTML()
	{
		$export = new ilScorm2004Export($this->node_object,'HTML');
		$export->buildExportFile();
		$this->ctrl->redirect($this, "showExportList");
	}

	function exportISO()
	{
		$export = new ilScorm2004Export($this->node_object,'ISO');
		$export->buildExportFile();
		$this->ctrl->redirect($this, "showExportList");
	}
	
	function exportPDF()
	{
		$export = new ilScorm2004Export($this->node_object,'PDF');
		$export->buildExportFile();
		$this->ctrl->redirect($this, "showExportList");
	}
	
	function downloadExportFile()
	{
		global $ilias, $lng;
		
		if(!isset($_POST["file"]))
		{
			$ilias->raiseError($lng->txt("no_checkbox"),$ilias->error_obj->MESSAGE);
		}

		if (count($_POST["file"]) > 1)
		{
			$ilias->raiseError($lng->txt("cont_select_max_one_item"),$ilias->error_obj->MESSAGE);
		}

		$file = explode(":", $_POST["file"][0]);
		$export = new ilSCORM2004Export($this->node_object);
		$export_dir = $export->getExportDirectoryForType($file[0]);
		ilUtil::deliverFile($export_dir."/".$file[1], $file[1]);
	}
	
/* confirmation screen for export file deletion
	*/
	function confirmDeleteExportFile()
	{
		global $ilias, $lng, $tpl;
		
		if(!isset($_POST["file"]))
		{
			$ilias->raiseError($lng->txt("no_checkbox"),$ilias->error_obj->MESSAGE);
		}

		$this->setTabs();

		// SAVE POST VALUES
		$_SESSION["ilExportFiles"] = $_POST["file"];

		$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.confirm_deletion.html", "Modules/LearningModule");

		ilUtil::sendInfo($lng->txt("info_delete_sure"));

		$tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		// BEGIN TABLE HEADER
		$tpl->setCurrentBlock("table_header");
		$tpl->setVariable("TEXT",$lng->txt("objects"));
		$tpl->parseCurrentBlock();

		// BEGIN TABLE DATA
		$counter = 0;
		foreach($_POST["file"] as $file)
		{
			$file = explode(":", $file);
			$tpl->setCurrentBlock("table_row");
			$tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$tpl->setVariable("TEXT_CONTENT", $file[1]." (".$file[0].")");
			$tpl->parseCurrentBlock();
		}

		// cancel/confirm button
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$buttons = array( "cancelDeleteExportFile"  => $lng->txt("cancel"),
			"deleteExportFile"  => $lng->txt("confirm"));
		foreach ($buttons as $name => $value)
		{
			$tpl->setCurrentBlock("operation_btn");
			$tpl->setVariable("BTN_NAME",$name);
			$tpl->setVariable("BTN_VALUE",$value);
			$tpl->parseCurrentBlock();
		}
	}


	/**
	* cancel deletion of export files
	*/
	function cancelDeleteExportFile()
	{
		session_unregister("ilExportFiles");
		$this->ctrl->redirect($this, "showExportList");
	}

	/**
	* delete export files
	*/
	function deleteExportFile()
	{
		foreach($_SESSION["ilExportFiles"] as $file)
		{
			$file = explode(":", $file);
			$export = new ilSCORM2004Export($this->node_object);
			$export_dir = $export->getExportDirectoryForType($file[0]);
		
			$exp_file = $export_dir."/".$file[1];
			$exp_dir = $export_dir."/".substr($file[1], 0, strlen($file[1]) - 4);
			if (@is_file($exp_file))
			{
				unlink($exp_file);
			}
			if (@is_dir($exp_dir))
			{
				ilUtil::delDir($exp_dir);
			}
		}
		$this->ctrl->redirect($this, "showExportList");
	}
	
	function sco_resources()
	{
		global $tpl, $lng;
		
		$this->setTabs();
		$this->setLocator();
		$i = 0;
		$export_files = array();
		
		require_once "./Modules/Scorm2004/classes/class.ilSCORM2004Page.php";
		include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
		include_once "./Modules/File/classes/class.ilObjFile.php";
		$tree = new ilTree($this->slm_object->getId());
		$tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		$tree->setTreeTablePK("slm_id");
		foreach($tree->getSubTree($tree->getNodeData($this->node_object->getId()),true,'page') as $page)
		{
				$page_obj = new ilSCORM2004Page($page["obj_id"]);
				$page_obj->buildDom();
				$mob_ids = $page_obj->collectMediaObjects(false);
				foreach($mob_ids as $mob_id)
				{
					if ($mob_id > 0 && ilObject::_exists($mob_id))
					{
						$media_obj = new ilObjMediaObject($mob_id);
						$export_files[$i]["date"] = $media_obj->getCreateDate();
						$export_files[$i]["size"] = filesize(ilObjMediaObject::_lookupStandardItemPath($mob_id,false,false));
						$export_files[$i]["file"] = $media_obj->getTitle();
						$export_files[$i]["type"] = $media_obj->getDescription();
						$export_files[$i]["link"] = ilObjMediaObject::_lookupStandardItemPath($mob_id);
						$i++;
					}
				}
				$file_ids = $page_obj->collectFileItems();
				foreach($file_ids as $file_id)
				{
					$file_obj = new ilObjFile($file_id, false);
					$export_files[$i]["date"] = $file_obj->getCreateDate();
					$export_files[$i]["size"] = $file_obj->getFileSize();
					$export_files[$i]["file"] = $file_obj->getFileName();
					$export_files[$i]["type"] = $file_obj->getFileType();
					$this->ctrl->setParameter($this, "file_id",$file_id);
					$export_files[$i]["link"] = $this->ctrl->getLinkTarget($this,"downloadFile","");
					$i++;
				}
				unset($page_obj);
		}
				
		// create table
		require_once("./Services/Table/classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI();

		// load files templates
		$tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");

		// load template for table content data
		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.download_file_row.html", "Modules/LearningModule");

		$num = 0;

		$tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$tbl->setTitle($lng->txt("cont_files"));

		$tbl->setHeaderNames(array($lng->txt("cont_format"),
			$lng->txt("cont_file"),
			$lng->txt("size"), $lng->txt("date"),
			""));
		
		$cols = array("format", "file", "size", "date", "download");
		$header_params = array("ref_id" => $_GET["ref_id"], "obj_id" => $_GET["obj_id"],
			"cmd" => "sco_resources", "cmdClass" => strtolower(get_class($this)));
		$tbl->setHeaderVars($cols, $header_params);
		$tbl->setColumnWidth(array("10%", "30%", "20%", "20%","20%"));
		$tbl->disable("sort");

		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);		// ???
		

		$tbl->setMaxCount(count($export_files));

		// footer
		$tbl->setFooter("tblfooter",$lng->txt("previous"),$lng->txt("next"));
		//$tbl->disable("footer");

		$tbl->setMaxCount(count($export_files));
		$export_files = array_slice($export_files, $_GET["offset"], $_GET["limit"]);
		
		$tbl->render();
		if(count($export_files) > 0)
		{
			$i=0;
			foreach($export_files as $exp_file)
			{
				if (!$exp_file["size"] > 0)
				{
					continue;
				}
				
				$tpl->setCurrentBlock("tbl_content");
				$tpl->setVariable("TXT_FILENAME", $exp_file["file"]);

				$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
				$tpl->setVariable("CSS_ROW", $css_row);

				$tpl->setVariable("TXT_SIZE", $exp_file["size"]);
				$tpl->setVariable("TXT_FORMAT", $exp_file["type"]);
				
				$tpl->setVariable("TXT_DATE", $exp_file["date"]);

				$tpl->setVariable("TXT_DOWNLOAD", $lng->txt("download"));
				$tpl->setVariable("LINK_DOWNLOAD", $exp_file["link"]);

				$tpl->parseCurrentBlock();
			}
		} //if is_array
		else
		{
			$tpl->setCurrentBlock("notfound");
			$tpl->setVariable("TXT_OBJECT_NOT_FOUND", $lng->txt("obj_not_found"));
			$tpl->setVariable("NUM_COLS", 4);
			$tpl->parseCurrentBlock();
		}
		$tpl->parseCurrentBlock();
	}
	
	function downloadFile()
	{
		$file = explode("_", $_GET["file_id"]);
		require_once("./Modules/File/classes/class.ilObjFile.php");
		$fileObj =& new ilObjFile($file[count($file) - 1], false);
		$fileObj->sendFile();
		exit;
	}
	
	function import()
	{
		global $tpl, $lng;
		
		$this->setTabs();
		$this->setLocator();
		
		$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.scormeditor_sco_import.html", "Modules/Scorm2004");
		
		$tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_slm.gif'));
		$tpl->setVariable("ALT_IMG", $lng->txt("obj_sahs"));
		
		$tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$tpl->setVariable("BTN_NAME", "importSave");
		
		// this leads to _top target which removes the left organization frame
		//$tpl->setVariable("TARGET", ' target="'.ilFrameTargetInfo::_getFrame("MainContent").'" ');

		$tpl->setVariable("TXT_UPLOAD", $lng->txt("upload"));
		$tpl->setVariable("TXT_CANCEL", $lng->txt("cancel"));
		$tpl->setVariable("TXT_IMPORT_SCO", $lng->txt("import_sco_object"));
		$tpl->setVariable("TXT_SELECT_FILE", $lng->txt("select_file"));
		$tpl->setVariable("TXT_VALIDATE_FILE", $lng->txt("cont_validate_file"));

		// get the value for the maximal uploadable filesize from the php.ini (if available)
		$umf=get_cfg_var("upload_max_filesize");
		// get the value for the maximal post data from the php.ini (if available)
		$pms=get_cfg_var("post_max_size");
		
		//convert from short-string representation to "real" bytes
		$multiplier_a=array("K"=>1024, "M"=>1024*1024, "G"=>1024*1024*1024);
		
		$umf_parts=preg_split("/(\d+)([K|G|M])/", $umf, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
        $pms_parts=preg_split("/(\d+)([K|G|M])/", $pms, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
        
        if (count($umf_parts) == 2) { $umf = $umf_parts[0]*$multiplier_a[$umf_parts[1]]; }
        if (count($pms_parts) == 2) { $pms = $pms_parts[0]*$multiplier_a[$pms_parts[1]]; }
        
        // use the smaller one as limit
		$max_filesize=min($umf, $pms);

		if (!$max_filesize) $max_filesize=max($umf, $pms);
	
    	//format for display in mega-bytes
		$max_filesize=sprintf("%.1f MB",$max_filesize/1024/1024);

		// gives out the limit as a little notice
		$tpl->setVariable("TXT_FILE_INFO", $lng->txt("file_notice")." $max_filesize");	
	}
	
	function importSave()
	{
		global $_FILES, $rbacsystem;
		global $ilias, $lng;

		// check if file was uploaded
		$source = $_FILES["scormfile"]["tmp_name"];
		if (($source == 'none') || (!$source))
		{
			$ilias->raiseError("No file selected!",$ilias->error_obj->MESSAGE);
		}
		// check create permission
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], "sahs"))
		{
			$ilias->raiseError($lng->txt("no_create_permission"), $ilias->error_obj->WARNING);
		}
		// get_cfg_var("upload_max_filesize"); // get the may filesize form t he php.ini
		switch ($__FILES["scormfile"]["error"])
		{
			case UPLOAD_ERR_INI_SIZE:
				$ilias->raiseError($lng->txt("err_max_file_size_exceeds"),$ilias->error_obj->MESSAGE);
				break;

			case UPLOAD_ERR_FORM_SIZE:
				$ilias->raiseError($lng->txt("err_max_file_size_exceeds"),$ilias->error_obj->MESSAGE);
				break;

			case UPLOAD_ERR_PARTIAL:
				$ilias->raiseError($lng->txt("err_partial_file_upload"),$ilias->error_obj->MESSAGE);
				break;

			case UPLOAD_ERR_NO_FILE:
				$ilias->raiseError($lng->txt("err_no_file_uploaded"),$ilias->error_obj->MESSAGE);
				break;
		}

		$file = pathinfo($_FILES["scormfile"]["name"]);
		$name = substr($file["basename"], 0, strlen($file["basename"]) - strlen($file["extension"]) - 1);
		$file_path = $this->slm_object->getDataDirectory()."/".$this->node_object->getId()."/".$_FILES["scormfile"]["name"];
		ilUtil::createDirectory($this->slm_object->getDataDirectory()."/".$this->node_object->getId());
		ilUtil::moveUploadedFile($_FILES["scormfile"]["tmp_name"], $_FILES["scormfile"]["name"], $file_path);
		ilUtil::unzip($file_path);
		ilUtil::renameExecutables($this->slm_object->getDataDirectory()."/".$this->node_object->getId());
		
		include_once ("./Modules/Scorm2004/classes/ilSCORM13Package.php");
		$newPack = new ilSCORM13Package();
		$newPack->il_importSco($this->slm_object->getId(),$this->node_object->getId(),$this->slm_object->getDataDirectory()."/".$this->node_object->getId());
			
		$this->ctrl->redirect($this, "showOrganization");
	}
	
	/**
	* Cancel action
	*/
	function cancel()
	{
		$this->ctrl->redirect($this, "showOrganization");
	}
	
}
?>
