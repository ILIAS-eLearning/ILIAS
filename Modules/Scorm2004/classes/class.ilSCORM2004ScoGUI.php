<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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
				$cmd = $ilCtrl->getCmd("showOrganization");
				$ret =& $this->$cmd();
				break;
		}
	}

	/**
	 * Show learning objectives
	 */
	function showProperties()
	{
		global $tpl, $lng, $ilTabs, $ilCtrl;

		$this->setTabs();
		$this->setLocator();
		$ilTabs->setTabActive("sahs_desc_objectives");

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();

		// hide objectives page
		$cb = new ilCheckboxInputGUI($lng->txt("sahs_hide_objectives_page"), "hide_objectives_page");
		$cb->setInfo($lng->txt("sahs_hide_objectives_page_info"));
		$form->addItem($cb);
		$cb->setChecked($this->node_object->getHideObjectivePage());

		// description
		$ta = new ilTextAreaInputGUI($lng->txt("description"), "desc");
		$ta->setRows(4);
		$ta->setCols(55);
		$ta->setInfo($lng->txt("sahs_list_info"));
		$form->setTitle($lng->txt("properties"));
		$form->addItem($ta);
		include_once "./Services/MetaData/classes/class.ilMD.php";
		$meta = new ilMD($this->node_object->getSLMId(), $this->node_object->getId(), $this->node_object->getType());
		$desc_ids = $meta->getGeneral()->getDescriptionIds();
		$ta->setValue($meta->getGeneral()->getDescription($desc_ids[0])->getDescription());

		// objectives
		$sh = new ilFormSectionHeaderGUI();
		$sh->setTitle($lng->txt("sahs_learning_objectives"));
		$form->addItem($sh);

		$objectives = $this->node_object->getObjectives();

		foreach ($objectives as $ob)
		{
			// map info
			$mappings = $ob->getMappings();
			$mapinfo = null;
			foreach($mappings as $map)
			{
				$mapinfo .= $map->getTargetObjectiveID();
			}

			if ($mapinfo == null)
			{
				$mapinfo = "local";
			}
			else
			{
				$mapinfo = "global to ".$mapinfo;
			}

			// objective
			$ta = new ilTextAreaInputGUI($mapinfo,
				"obj_".$ob->getId());
			$ta->setCols(55);
			$ta->setRows(4);
			$ta->setInfo($lng->txt("sahs_list_info"));
			$form->addItem($ta);
			$ta->setValue($ob->getObjectiveID());
		}
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->addCommandButton("updateProperties",
			$lng->txt("save"));
		$tpl->setContent($form->getHTML());
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
//				if (!$value)
//				{
//					$empty=true;
//				} else {
					$objective->setObjectiveID(ilUtil::stripSlashes($value));
					$objective->updateObjective();
//				}
			}
		}

		$this->node_object->setHideObjectivePage(ilUtil::stripSlashes($_POST["hide_objectives_page"]));
		$this->node_object->update();

		include_once "./Services/MetaData/classes/class.ilMD.php";
		$meta = new ilMD($this->node_object->getSLMId(), $this->node_object->getId(), $this->node_object->getType());
		$gen = $meta->getGeneral();
		$desc_ids = $gen->getDescriptionIds();
		$des = $gen->getDescription($desc_ids[0]);
		$des->setDescription(ilUtil::stripSlashes($_POST["desc"]));
		$des->update();
		$gen->update();

		if (!$empty)
		{
			ilUtil::sendInfo($lng->txt("saved_successfully"),true);
		}
		else
		{
			ilUtil::sendFailure($lng->txt("sahs_empty_objectives_are_not_allowed"), true);
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
			include_once("./Services/COPage/classes/class.ilPCQuestion.php");
			$qids = ilPCQuestion::_getQuestionIdsForPage("sahs", $page["obj_id"]);
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
					//$tpl->setVariable("HREF_EDIT_QUESTION", $ilCtrl->getLinkTargetByClass("ilscorm2004pagenodegui", "edit"));
					
					$tpl->setVariable("CSS_ROW", ilUtil::switchColor($i++, "tblrow1", "tblrow2"));
					$tpl->parseCurrentBlock();
				}
			}
		}
		$tbl->render();
	}

	function getEditTree()
	{
die("deprecated");
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
		$ilTabs->addTarget("sahs_desc_objectives",
		$ilCtrl->getLinkTarget($this,'showProperties'),
			 "showProperties", get_class($this));

		// questions
		$ilTabs->addTarget("sahs_questions",
		$ilCtrl->getLinkTarget($this,'sahs_questions'),
			 "sahs_questions", get_class($this));

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
		
		// preview
		$ilTabs->addNonTabbedLink("preview",
			$lng->txt("cont_preview"),
			$ilCtrl->getLinkTarget($this,'sco_preview'), "_blank");
		
		$tpl->setTitleIcon(ilUtil::getImagePath("icon_sco.svg"));
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

	/**
	 * SCO preview
	 */
	function sco_preview()
	{
		global $tpl, $ilCtrl, $lng;
		
		// init main template
		$tpl = new ilTemplate("tpl.main.html", true, true);
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$tpl->setBodyClass("");
		$tpl->setCurrentBlock("ContentStyle");
		$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath($this->slm_object->getStyleSheetId()));
		$tpl->parseCurrentBlock();
		
		// get javascript
		include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
		iljQueryUtil::initjQuery();
		iljQueryUtil::initjQueryUI();
		$tpl->addJavaScript("./Modules/Scorm2004/scripts/questions/pure.js");
		$tpl->addJavaScript("./Modules/Scorm2004/scripts/pager.js");

		$tpl->addOnLoadCode("pager.Init();");
		
		$tree = new ilTree($this->slm_object->getId());
		$tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		$tree->setTreeTablePK("slm_id");
		include_once "./Modules/Scorm2004/classes/class.ilSCORM2004PageGUI.php";
		include_once "./Services/MetaData/classes/class.ilMD.php";
		
		$meta = new ilMD($this->node_object->getSLMId(), $this->node_object->getId(), $this->node_object->getType());
		$desc_ids = $meta->getGeneral()->getDescriptionIds();
		$sco_description = $meta->getGeneral()->getDescription($desc_ids[0])->getDescription();
		
		// get sco template
		$sco_tpl = new ilTemplate("tpl.sco.html", true, true, "Modules/Scorm2004");
		
		// navigation
		$lk = ilObjSAHSLearningModule::getAffectiveLocalization($this->node_object->getSLMId());
		ilSCORM2004Asset::renderNavigation($sco_tpl, "", $lk);

		// meta page (description and objectives)
		ilSCORM2004Asset::renderMetaPage($sco_tpl, $this->node_object,
			$this->node_object->getType());
				
		// init export (this initialises glossary template)
		ilSCORM2004PageGUI::initExport();
		$terms = $this->node_object->getGlossaryTermIds();
		
		// render page
		foreach($tree->getSubTree($tree->getNodeData($this->node_object->getId()),true,'page') as $page)
		{
			$page_obj = new ilSCORM2004PageGUI($this->node_object->getType(),$page["obj_id"],
				0, $this->slm_object->getId());
			$page_obj->setPresentationTitle($page["title"]);
			$page_obj->setOutputMode(IL_PAGE_PREVIEW);
			$page_obj->setStyleId($this->slm_object->getStyleSheetId());
			if (count($terms) > 1)
			{
				$page_obj->setGlossaryOverviewInfo(
					ilSCORM2004ScoGUI::getGlossaryOverviewId(), $this->node_object);
			}
			$sco_tpl->setCurrentBlock("page_preview");
			$html = $ilCtrl->getHTML($page_obj);
			//$sco_tpl->setVariable("PAGE_PRV", $page_obj->showPage("export"));
			$sco_tpl->setVariable("PAGE_PRV", $html);
			$sco_tpl->parseCurrentBlock();
		}

		$output = $sco_tpl->get();
					
		// append glossary entries on the sco level
		$output.= ilSCORM2004PageGUI::getGlossaryHTML($this->node_object);
		
		//insert questions
		require_once './Modules/Scorm2004/classes/class.ilQuestionExporter.php';
		$output = preg_replace_callback("/{{{{{(Question;)(il__qst_[0-9]+)}}}}}/",array(get_class($this), 'insertQuestion'),$output);
//		$output = preg_replace("/&#123;/","",$output);
//		$output = preg_replace("/&#125;/","",$output);
		$output = "<script>var ScormApi=null;".ilQuestionExporter::questionsJS()."</script>".$output;
		
		$lk = ilObjSAHSLearningModule::getAffectiveLocalization($this->node_object->getSLMId());
//		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004PageGUI.php");
//		ilSCORM2004PageGUI::addPreparationJavascript($tpl, $lk);

		$tpl->addJavaScript("./Modules/Scorm2004/scripts/questions/question_handling.js");
		$tpl->addCss("./Modules/Scorm2004/templates/default/question_handling.css");
		
		include_once("./Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php");
		ilOverlayGUI::initJavascript();
		
		//inline JS
		$output .='<script type="text/javascript" src="./Modules/Scorm2004/scripts/questions/question_handling.js"></script>';
		$tpl->setVariable("CONTENT", $output);
	}
	
	//callback function for question export
	private function insertQuestion($matches)
	{
		$q_exporter = new ilQuestionExporter(false);
		return $q_exporter->exportQuestion($matches[2]);
	}

	/**
	* Select the export type of the SCORM 2004 module
	*/
	public function selectExport()
	{
		switch ($_POST['select_export'])
		{
			case "exportScorm12":
			case "exportScorm2004_3rd":
			case "exportScorm2004_4th":
			case "exportPDF":
			case "exportHTML":
				$this->ctrl->redirect($this, $_POST['select_export']);
				break;
			default:
				$this->ctrl->redirect($this, 'showExportList');
				break;
		}
	}
	
	function showExportList()
	{
		global $tpl, $ilCtrl, $lng;
		
		$this->setTabs();
		$this->setLocator();
		

		$template = new ilTemplate("tpl.scorm2004_export_buttons.html", true, true, 'Modules/Scorm2004');

		$buttons = array(
			"exportScorm2004_3rd" => $lng->txt("scorm_create_export_file_scrom2004"),
			"exportScorm2004_4th" => $lng->txt("scorm_create_export_file_scrom2004_4th"),
			"exportScorm12" => $lng->txt("scorm_create_export_file_scrom12"),
			"exportPDF" => $lng->txt("scorm_create_export_file_pdf"),
			"exportHTML" => $lng->txt("scorm_create_export_file_html")
		);
		foreach ($buttons as $value => $text)
		{
			$template->setCurrentBlock('option');
			$template->setVariable('OPTION_VALUE', $value);
			$template->setVariable('OPTION_TITLE', ilUtil::prepareFormOutput($text));
			$template->parseCurrentBlock();
		}
		$template->setVariable('EXPORT_TITLE', $lng->txt('export'));
		$template->setVariable('EXPORT_LABEL', $lng->txt('type'));
		$template->setVariable('FORMACTION', $ilCtrl->getFormAction($this, 'selectExport'));

		$export_files = $this->node_object->getExportFiles();

		include_once "./Modules/Scorm2004/classes/class.ilSCORM2004ExportTableGUI.php";
		$table_gui = new ilSCORM2004ExportTableGUI($this, 'showExportList');
		$data = array();
		foreach ($export_files as $exp_file)
		{
			$filetype = $exp_file['type'];
		//	$public_str = ($exp_file["file"] == $this->object->getPublicExportFile($filetype))
		//		? " <b>(".$this->lng->txt("public").")<b>"
		//		: "";
			$file_arr = explode("__", $exp_file["file"]);
			array_push($data, array('file' => $exp_file['file'], 'filetype' => $filetype, 'date' => ilDatePresentation::formatDate(new ilDateTime($file_arr[0], IL_CAL_UNIX)), 'size' => $exp_file['size'], 'type' => $exp_file['type'].$public_str));
		}
		$table_gui->setData($data);
		$tpl->setVariable('ADM_CONTENT', $template->get() . "\n" . $table_gui->getHTML());
		
		
	}

	function exportScorm2004_4th()
	{
		$export = new ilScorm2004Export($this->node_object,'SCORM 2004 4th');
		$export->buildExportFile();
		$this->ctrl->redirect($this, "showExportList");
	}

	
	function exportScorm2004_3rd()
	{
		$export = new ilScorm2004Export($this->node_object,'SCORM 2004 3rd');
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
		if(!isset($_POST["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		if (count($_POST["file"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
		}
		$export = new ilSCORM2004Export($this->node_object);
		$export_dir = $export->getExportDirectoryForType($_POST['type'][$_POST['file'][0]]);
		ilUtil::deliverFile($export_dir."/".$_POST['file'][0], $_POST['file'][0]);
	}
	
	/**
	* confirmation screen for export file deletion
	*/
	function confirmDeleteExportFile()
	{
		global $lng, $tpl;
		
		if(!isset($_POST["file"]))
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"),true);
			$this->ctrl->redirect($this, "showExportList");
		}

		ilUtil::sendQuestion($lng->txt("info_delete_sure"));
		$export_files = $this->node_object->getExportFiles();

		include_once "./Modules/Scorm2004/classes/class.ilSCORM2004ExportTableGUI.php";
		$table_gui = new ilSCORM2004ExportTableGUI($this, 'showExportList', true);
		$data = array();
		foreach ($export_files as $exp_file)
		{
			foreach ($_POST['file'] as $delete_file)
			{
				if (strcmp($delete_file, $exp_file['file']) == 0)
				{
			//		$public_str = ($exp_file["file"] == $this->object->getPublicExportFile($exp_file["type"]))
			//			? " <b>(".$this->lng->txt("public").")<b>"
			//			: "";
					$file_arr = explode("__", $exp_file["file"]);
					array_push($data, array('file' => $exp_file['file'], 'date' => ilDatePresentation::formatDate(new ilDateTime($file_arr[0], IL_CAL_UNIX)), 'size' => $exp_file['size'], 'type' => $exp_file['type'].$public_str));
				}
			}
		}
		$table_gui->setData($data);
		$tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}

	/**
	* cancel deletion of export files
	*/
	function cancelDeleteExportFile()
	{
		ilSession::clear("ilExportFiles");
		$this->ctrl->redirect($this, "showExportList");
	}


	/**
	* delete export files
	*/
	function deleteExportFile()
	{
		global $lng;
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$export = new ilSCORM2004Export($this->node_object);
		foreach($_POST['file'] as $idx => $file)
		{
			$export_dir = $export->getExportDirectoryForType($_POST['type'][$idx]);
			$exp_file = $export_dir."/".$file;
			if (@is_file($exp_file))
			{
				unlink($exp_file);
			}
		}
		ilUtil::sendSuccess($lng->txt('msg_deleted_export_files'), true);
		$this->ctrl->redirect($this, "showExportList");
	}
	
	function getExportResources()
	{
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
						$path = ilObjMediaObject::_lookupStandardItemPath($mob_id,false,false);
						
						$media_obj = new ilObjMediaObject($mob_id);
						$export_files[$i]["date"] = $media_obj->getCreateDate();
						$export_files[$i]["size"] = @filesize($path); // could be remote, e.g. youtube video
						$export_files[$i]["file"] = $media_obj->getTitle();
						$export_files[$i]["type"] = $media_obj->getDescription();
						$export_files[$i]["path"] = $path;
						$this->ctrl->setParameter($this, "resource",
							rawurlencode(ilObjMediaObject::_lookupStandardItemPath($mob_id,false,false)));
						$export_files[$i]["link"] = $this->ctrl->getLinkTarget($this,"downloadResource");
						$i++;
					}
				}
				include_once("./Services/COPage/classes/class.ilPCFileList.php");
				$file_ids =ilPCFileList::collectFileItems($page_obj, $page_obj->getDomDoc());
				foreach($file_ids as $file_id)
				{
					$file_obj = new ilObjFile($file_id, false);
					$export_files[$i]["date"] = $file_obj->getCreateDate();
					$export_files[$i]["size"] = $file_obj->getFileSize();
					$export_files[$i]["file"] = $file_obj->getFileName();
					$export_files[$i]["type"] = $file_obj->getFileType();
					$export_files[$i]["file_id"] = $file_id;
					$this->ctrl->setParameter($this, "file_id",$file_id);
					$export_files[$i]["link"] = $this->ctrl->getLinkTarget($this,"downloadFile","");
					$i++;
				}
				unset($page_obj);
		}
		
		return $export_files;
	}
	
	function sco_resources()
	{
		global $tpl, $lng, $ilCtrl;;
		
		$this->setTabs();
		$this->setLocator();
		$i = 0;
				
		$export_files = $this->getExportResources();

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
			"cmd" => "sco_resources", "cmdClass" => strtolower(get_class($this)),
			"cmdNode" => $_GET["cmdNode"], "baseClass" => $_GET["baseClass"]);
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
				/* remote files (youtube videos) have no size, so we allow them now
				if (!$exp_file["size"] > 0)
				{
					continue;
				}
				*/			
				
				$tpl->setCurrentBlock("tbl_content");
				$tpl->setVariable("TXT_FILENAME", $exp_file["file"]);

				$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
				$tpl->setVariable("CSS_ROW", $css_row);

				$tpl->setVariable("TXT_SIZE", $exp_file["size"]);
				$tpl->setVariable("TXT_FORMAT", $exp_file["type"]);
				
				$tpl->setVariable("TXT_DATE", $exp_file["date"]);
												
				if($exp_file["size"] > 0)
				{
					$tpl->setVariable("TXT_DOWNLOAD", $lng->txt("download"));
					$ilCtrl->setParameter($this, "resource", rawurlencode($exp_file["path"]));
					$ilCtrl->setParameter($this, "file_id", rawurlencode($exp_file["file_id"]));
					$tpl->setVariable("LINK_DOWNLOAD",
						$ilCtrl->getLinkTarget($this, "downloadResource"));
				}
				else
				{
					$tpl->setVariable("TXT_DOWNLOAD", $lng->txt("show"));
					$tpl->setVariable("LINK_TARGET", " target=\"_blank\"");
					$tpl->setVariable("LINK_DOWNLOAD", $exp_file["path"]);
				}

				$tpl->parseCurrentBlock();
			}
		} //if is_array
		/* not found in template?
		else
		{
			$tpl->setCurrentBlock("notfound");
			$tpl->setVariable("TXT_OBJECT_NOT_FOUND", $lng->txt("obj_not_found"));
			$tpl->setVariable("NUM_COLS", 4);
			$tpl->parseCurrentBlock();
		}		
		 */
	    // $tpl->parseCurrentBlock();
	}
	
	function downloadResource()
	{
		$export_files = $this->getExportResources();

		if ($_GET["file_id"] > 0)
		{
			$file = new ilObjFile($_GET["file_id"], false);
		}

		// check that file really belongs to SCORM module (security)
		foreach ($export_files as $f)
		{
			if (is_object($file))
			{
				if ($f["file"] == $file->getFileName())
				{
					$file->sendFile();
				}
			}
			else
			{
				if ($f["path"] == $_GET["resource"])
				{
					if (is_file($f["path"]))
					{
						ilUtil::deliverFile($f["path"], $f["file"]);
					}
				}
			}
		}
		exit;
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
		
		$tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_slm.svg'));
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
	
	/**
	 * Get sco glossary overlay id
	 *
	 * @param
	 * @return
	 */
	static function getGlossaryOverviewId()
	{
		return "sco_glo_ov";
	}
	
	/**
	 * des
	 *
	 * @param
	 * @return
	 */
	static function getGloOverviewOv($a_sco)
	{
		global $lng;
		
		$tpl = new ilTemplate("tpl.sco_glossary_overview.html", true, true, "Modules/Scorm2004");
		
		$terms = $a_sco->getGlossaryTermIds();
		$lk = ilObjSAHSLearningModule::getAffectiveLocalization($a_sco->getSLMId());
		foreach ($terms as $k => $t)
		{
			$tpl->setCurrentBlock("link");
			$tpl->setVariable("TXT_LINK", $t);
			$tpl->setVariable("ID_LINK", "glo_ov_t".$k);
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("DIV_ID", ilSCORM2004ScoGUI::getGlossaryOverviewId());
		$tpl->setVariable("TXT_SCO_GLOSSARY", $lng->txtlng("content", "cont_sco_glossary", $lk));
		$tpl->setVariable("TXT_CLOSE", $lng->txtlng("common", "close", $lk));

		if (count($terms) > 1)
		{
			return $tpl->get();
		}
		else
		{
			return "";
		}
	}
	
}
?>
