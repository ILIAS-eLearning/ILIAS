<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");

/**
 * Class ilSCORM2004Asset
 *
 * Asset class for SCORM 2004 Editing
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesScorm2004
 */
class ilSCORM2004Asset extends ilSCORM2004Node
{
	var $q_media = null;		// media files in questions

	/**
	 * Constructor
	 */
	function __construct($a_slm_object, $a_id = 0)
	{
		parent::ilSCORM2004Node($a_slm_object, $a_id);
		$this->setType("ass");
	}

	/**
	 * Delete a SCO
	 */
	function delete($a_delete_meta_data = true)
	{
		$node_data = $this->tree->getNodeData($this->getId());
		$this->delete_rec($a_delete_meta_data);
		$this->tree->deleteTree($node_data);
		parent::deleteSeqInfo();
	}

	/**
	 * Create asset
	 */
	function create($a_upload = false, $a_template = false)
	{
		include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Item.php");
		include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Objective.php");
		parent::create($a_upload);
		if (!$a_template) {
			$seq_item = new ilSCORM2004Item($this->getId());
			$seq_item->insert();
		}
	}

	/**
	 * Delete Nested Page Objects
	 */
	private function delete_rec($a_delete_meta_data = true)
	{
		$childs = $this->tree->getChilds($this->getId());
		foreach ($childs as $child)
		{
			$obj =& ilSCORM2004NodeFactory::getInstance($this->slm_object, $child["obj_id"], false);
			if (is_object($obj))
			{
				if ($obj->getType() == "page")
				{
					$obj->delete($a_delete_meta_data);
				}
			}
			unset($obj);
		}
		parent::delete($a_delete_meta_data);
	}

	/**
	 * Copy sco
	 */
	function copy($a_target_slm)
	{
		$ass = new ilSCORM2004Asset($a_target_slm);
		$ass->setTitle($this->getTitle());
		if ($this->getSLMId() != $a_target_slm->getId())
		{
			$sco->setImportId("il__ass_".$this->getId());
		}
		$ass->setSLMId($a_target_slm->getId());
		$ass->setType($this->getType());
		$ass->setDescription($this->getDescription());
		$ass->create(true);
		$a_copied_nodes[$this->getId()] = $ass->getId();

		// copy meta data
		include_once("Services/MetaData/classes/class.ilMD.php");
		$md = new ilMD($this->getSLMId(), $this->getId(), $this->getType());
		$new_md = $md->cloneMD($a_target_slm->getId(), $ass->getId(), $this->getType());

		return $ass;
	}

	// @todo: more stuff similar to ilSCORM2004Chapter needed...

	function exportScorm($a_inst, $a_target_dir, $ver, &$expLog)
	{
		copy('./xml/ilias_co_3_7.dtd',$a_target_dir.'/ilias_co_3_7.dtd');
		copy('./Modules/Scorm2004/templates/xsl/sco.xsl',$a_target_dir.'/sco.xsl');

		$a_xml_writer = new ilXmlWriter;
		// MetaData
		//file_put_contents($a_target_dir.'/indexMD.xml','<lom xmlns="http://ltsc.ieee.org/xsd/LOM"><general/><classification/></lom>');
		$this->exportXMLMetaData($a_xml_writer);
		$metadata_xml = $a_xml_writer->xmlDumpMem(false);
		$a_xml_writer->_XmlWriter;
		$xsl = file_get_contents("./Modules/Scorm2004/templates/xsl/metadata.xsl");
		$args = array( '/_xml' => $metadata_xml , '/_xsl' => $xsl );
		$xh = xslt_create();
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args,NULL);
		xslt_free($xh);
		file_put_contents($a_target_dir.'/indexMD.xml',$output);

		$a_xml_writer = new ilXmlWriter;
		// set dtd definition
		$a_xml_writer->xmlSetDtdDef("<!DOCTYPE ContentObject SYSTEM \"http://www.ilias.de/download/dtd/ilias_co_3_7.dtd\">");

		// set generated comment
		$a_xml_writer->xmlSetGenCmt("Export of ILIAS Content Module ".	$this->getId()." of installation ".$a_inst.".");

		// set xml header
		$a_xml_writer->xmlHeader();

		global $ilBench;

		$a_xml_writer->xmlStartTag("ContentObject", array("Type"=>"SCORM2004SCO"));

		$this->exportXMLMetaData($a_xml_writer);

		$this->exportXMLPageObjects($a_target_dir, $a_xml_writer, $a_inst, $expLog);

		$this->exportXMLMediaObjects($a_xml_writer, $a_inst, $a_target_dir, $expLog);

		$this->exportHTML($a_inst, $a_target_dir, $expLog);

		//overwrite scorm.js for scrom 1.2
		if ($ver=="12")
			copy('./Modules/Scorm2004/scripts/scorm_12.js',$a_target_dir.'/js/scorm.js');

		$a_xml_writer->xmlEndTag("ContentObject");

		$a_xml_writer->xmlDumpFile($a_target_dir.'/index.xml', false);

		$a_xml_writer->_XmlWriter;
		
		// export sco data (currently only objective) to sco.xml
		if ($this->getType() == "sco")
		{
			$objectives_text = "";
			$a_xml_writer = new ilXmlWriter;
			
			$tr_data = $this->getObjectives();
			foreach ($tr_data as $data)
			{
				$objectives_text.= $data->getObjectiveID();
			}
			$a_xml_writer->xmlStartTag("sco");
			$a_xml_writer->xmlElement("objective", null, $objectives_text);
			$a_xml_writer->xmlEndTag("sco");
			$a_xml_writer->xmlDumpFile($a_target_dir.'/sco.xml', false);
			$a_xml_writer->_XmlWriter;
		}
	}

	function exportHTML($a_inst, $a_target_dir, &$expLog, $a_asset_type = "sco")
	{
		ilUtil::makeDir($a_target_dir.'/css');
		ilUtil::makeDir($a_target_dir.'/css/yahoo');
		ilUtil::makeDir($a_target_dir.'/objects');
		ilUtil::makeDir($a_target_dir.'/images');
		ilUtil::makeDir($a_target_dir.'/js');
		ilUtil::makeDir($a_target_dir.'/players');

		copy('./Services/MediaObjects/flash_flv_player/flvplayer.swf', $a_target_dir.'/players/flvplayer.swf');
		copy('./Services/MediaObjects/flash_mp3_player/mp3player.swf', $a_target_dir.'/players/mp3player.swf');
		copy('./Modules/Scorm2004/scripts/scorm_2004.js',$a_target_dir.'/js/scorm.js');
		copy('./Modules/Scorm2004/scripts/pager.js',$a_target_dir.'/js/pager.js');
		copy('./Modules/Scorm2004/scripts/questions/pure.js',$a_target_dir.'/js/pure.js');
		
		// jquery
		//copy('./Modules/Scorm2004/scripts/questions/jquery.js',$a_target_dir.'/js/jquery.js');
		//copy('./Modules/Scorm2004/scripts/questions/jquery-ui-min.js',$a_target_dir.'/js/jquery-ui-min.js');
		include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
		copy(iljQueryUtil::getLocaljQueryPath(), $a_target_dir.'/js/jquery.js');
		copy(iljQueryUtil::getLocaljQueryUIPath(), $a_target_dir.'/js/jquery-ui-min.js');

		// accordion stuff
		ilUtil::makeDir($a_target_dir.'/js/yahoo');
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		copy(ilYuiUtil::getLocalPath('yahoo/yahoo-min.js'), $a_target_dir.'/js/yahoo/yahoo-min.js');
		copy(ilYuiUtil::getLocalPath('yahoo-dom-event/yahoo-dom-event.js'), $a_target_dir.'/js/yahoo/yahoo-dom-event.js');
		copy(ilYuiUtil::getLocalPath('container/container_core-min.js'), $a_target_dir.'/js/yahoo/container_core-min.js');
		copy(ilYuiUtil::getLocalPath('animation/animation-min.js'), $a_target_dir.'/js/yahoo/animation-min.js');
		copy(ilYuiUtil::getLocalPath('container/assets/skins/sam/container.css'), $a_target_dir.'/css/yahoo/container.css');
		copy('./Services/Accordion/js/accordion.js',$a_target_dir.'/js/accordion.js');
		copy('./Services/Accordion/css/accordion.css',$a_target_dir.'/css/accordion.css');
		copy('./Services/JavaScript/js/Basic.js',$a_target_dir.'/js/Basic.js');
		copy('./Services/UIComponent/Overlay/js/ilOverlay.js',$a_target_dir.'/js/ilOverlay.js');
		copy('./Services/COPage/js/ilCOPagePres.js',$a_target_dir.'/js/ilCOPagePres.js');

		// export content css
		include_once("./Modules/Scorm2004/classes/class.ilScormExportUtil.php");
		ilScormExportUtil::exportContentCSS($this->slm_object, $a_target_dir);

		// export system style sheet
		$css = fread(fopen(ilUtil::getStyleSheetLocation("filesystem"),'r'),filesize(ilUtil::getStyleSheetLocation("filesystem")));
		preg_match_all("/url\(([^\)]*)\)/",$css,$files);
		$currdir = getcwd();
		chdir(dirname(ilUtil::getStyleSheetLocation("filesystem")));
		foreach (array_unique($files[1]) as $fileref)
		{
			if(file_exists($fileref))
			{
				copy($fileref,$a_target_dir."/images/".basename($fileref));
				$css = str_replace($fileref,"../images/".basename($fileref),$css);
			}
		}
		copy('images/spacer.gif',$a_target_dir."/images/spacer.gif");
		copy('images/enlarge.gif',$a_target_dir."/images/enlarge.gif");
		chdir($currdir);
		fwrite(fopen($a_target_dir.'/css/system.css','w'),$css);

		$this->exportHTMLPageObjects($a_inst, $a_target_dir, $expLog, 'full',
			$a_asset_type);

	}


	function exportHTML4PDF($a_inst, $a_target_dir, &$expLog)
	{
		ilUtil::makeDir($a_target_dir.'/css');
		ilUtil::makeDir($a_target_dir.'/objects');
		ilUtil::makeDir($a_target_dir.'/images');
		$this->exportHTMLPageObjects($a_inst, $a_target_dir, $expLog, 'pdf');
	}

	function exportPDF($a_inst, $a_target_dir, &$expLog)
	{
		global $tpl, $lng, $ilCtrl;
		$a_xml_writer = new ilXmlWriter;
		$a_xml_writer->xmlStartTag("ContentObject", array("Type"=>"SCORM2004SCO"));
		$this->exportPDFPrepareXmlNFiles($a_inst, $a_target_dir, $expLog,$a_xml_writer);
		$a_xml_writer->xmlEndTag("ContentObject");
		copy('./templates/default/images/icon_attachment_s.png',$a_target_dir."/icon_attachment_s.png");
		include_once 'Services/Transformation/classes/class.ilXML2FO.php';
		$xml2FO = new ilXML2FO();
		$xml2FO->setXSLTLocation('./Modules/Scorm2004/templates/xsl/contentobject2fo.xsl');
		$xml2FO->setXMLString($a_xml_writer->xmlDumpMem());
		$xml2FO->setXSLTParams(array ('target_dir' => $a_target_dir));
		$xml2FO->transform();
		$fo_string = $xml2FO->getFOString();
		$fo_xml = simplexml_load_string($fo_string);
        $fo_ext = $fo_xml->xpath("//fo:declarations");
        $fo_ext = $fo_ext[0];
        $results = array();
        include_once "./Services/Utilities/classes/class.ilFileUtils.php";
        ilFileUtils::recursive_dirscan($a_target_dir."/objects", $results);
        if (is_array($results["file"]))
		{
            foreach ($results["file"] as $key => $value)
            {
                $e = $fo_ext->addChild("fox:embedded-file","","http://xml.apache.org/fop/extensions");
                $e->addAttribute("src",$results[path][$key].$value);
                $e->addAttribute("name",$value);
                $e->addAttribute("desc","");
            }
        }
        $fo_string = $fo_xml->asXML();
		$a_xml_writer->_XmlWriter;
		return $fo_string;
	}

	function exportPDFPrepareXmlNFiles($a_inst, $a_target_dir, &$expLog, &$a_xml_writer)
	{

		$this->exportHTML4PDF($a_inst, $a_target_dir, $expLog);
		global $tpl, $lng, $ilCtrl;
		$this->exportXMLPageObjects($a_target_dir, $a_xml_writer, $a_inst, $expLog);
		$this->exportXMLMediaObjects($a_xml_writer, $a_inst, $a_target_dir, $expLog);
		$this->exportFileItems($a_target_dir,$expLog);

		include_once "./Modules/Scorm2004/classes/class.ilSCORM2004PageNode.php";
		include_once "./Modules/Scorm2004/classes/class.ilSCORM2004Page.php";

		$tree = new ilTree($this->slm_id);
		$tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		$tree->setTreeTablePK("slm_id");
		foreach($tree->getSubTree($tree->getNodeData($this->getId()),true,'page') as $page)
		{
			$page_obj = new ilSCORM2004Page($page["obj_id"]);
		$q_ids = ilSCORM2004Page::_getQuestionIdsForPage("sahs", $page["obj_id"]);
		if (count($q_ids) > 0)
		{
			include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
			foreach ($q_ids as $q_id)
			{
				$q_obj =& assQuestion::_instanciateQuestion($q_id);
				$qti_file = fopen($a_target_dir."/qti_".$q_id.".xml", "w");
				fwrite($qti_file, $q_obj->toXML());
				fclose($qti_file);
					$x = file_get_contents($a_target_dir."/qti_".$q_id.".xml");
					$x = str_replace('<?xml version="1.0" encoding="utf-8"?>', '', $x);
					$a_xml_writer->appendXML($x);
			}
		}
			unset($page_obj);
		}
	}

	/**
	 * Export HTML pages of SCO
	 */
	function exportHTMLPageObjects($a_inst, $a_target_dir, &$expLog, $mode,
		$a_asset_type = "sco")
	{
		global $tpl, $ilCtrl, $ilBench,$ilLog, $lng;

		include_once "./Modules/Scorm2004/classes/class.ilSCORM2004PageGUI.php";
		include_once "./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModuleGUI.php";
		include_once "./Services/MetaData/classes/class.ilMD.php";

		$output = "";
		$tree = new ilTree($this->slm_id);
		$tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		$tree->setTreeTablePK("slm_id");

		// @todo
		// Why is that much HTML code in an application class?
		// Please extract all this HTML to a tpl.<t_name>.html file and use
		// placeholders and the template engine to insert data.
		//
		// There copy/paste code residenting in ilSCORM2004ScoGUI. This
		// should be merged.
		//
		// alex, 4 Apr 09
		//

		$sco_tpl = new ilTemplate("tpl.sco.html", true, true, "Modules/Scorm2004");
		if ($mode != 'pdf')
		{
			// init and question lang vars
			$sco_tpl->setCurrentBlock("init");
			$lvs = array("wrong_answers", "tries_remaining",
				"please_try_again", "all_answers_correct",
				"nr_of_tries_exceeded", "correct_answers_shown");
			foreach ($lvs as $lv)
			{
				$sco_tpl->setVariable("TXT_".strtoupper($lv),
					$lng->txt("cont_".$lv));
			}
			$sco_tpl->parseCurrentBlock();
			
			// style sheets needed
			$styles = array("./css/system.css", "./css/style.css",
				"./css/accordion.css", "./css/yahoo/container.css",
				"./css/question_handling.css");
			foreach ($styles as $style)
			{
				$sco_tpl->setCurrentBlock("style_sheet");
				$sco_tpl->setVariable("STYLE_HREF", $style);
				$sco_tpl->parseCurrentBlock();
			}
			
			// scripts needed
			$scripts = array("./js/scorm.js", "./js/jquery.js", "./js/jquery-ui-min.js",
				"./js/pager.js", "./js/pure.js", "./js/yahoo/yahoo-min.js", "./js/yahoo/yahoo-dom-event.js",
				"./js/yahoo/container_core-min.js", "./js/yahoo/animation-min.js", "./js/Basic.js",
				"./js/ilCOPagePres.js",
				"./js/ilOverlay.js", "./js/questions_".$this->getId().".js");
			foreach ($scripts as $script)
			{
				$sco_tpl->setCurrentBlock("script");
				$sco_tpl->setVariable("SCRIPT_SRC", $script);
				$sco_tpl->parseCurrentBlock();
			}
			
			
			if ($a_asset_type != "entry_asset" && $a_asset_type != "final_asset")
			{
				self::renderNavigation($sco_tpl, "./images/spacer.gif");
			}

			$sco_tpl->touchBlock("finish");
		}
		// render head
		$sco_tpl->setCurrentBlock("head");
		$sco_tpl->setVariable("SCO_TITLE", $this->getTitle());
		$sco_tpl->parseCurrentBlock();
		$sco_tpl->touchBlock("tail");

		// meta page (meta info at SCO beginning) start...
		self::renderMetaPage($sco_tpl, $this, $a_asset_type, $mode);

		//notify Question Exporter of new SCO
		require_once './Modules/Scorm2004/classes/class.ilQuestionExporter.php';
		ilQuestionExporter::indicateNewSco();

		// init export (this initialises glossary template)
		ilSCORM2004PageGUI::initExport();
		$terms = array();
		/*if ($a_asset_type == "entry_asset")
		{
			$pages[] = array("obj_id" => $this->slm_object->getEntryPage());
		}
		else if ($a_asset_type == "final_asset")
		{
			$pages[] = array("obj_id" => $this->slm_object->getFinalLMPage());
		}
		else
		{*/
			$terms = $this->getGlossaryTermIds();
			include_once("./Modules/Scorm2004/classes/class.ilSCORM2004ScoGUI.php");
			$pages = $tree->getSubTree($tree->getNodeData($this->getId()),true,'page');

			/*if ($this->getSLMObject()->getFinalScoPage() > 0)
			{
				$pages[] = array("obj_id" => $this->getSLMObject()->getFinalScoPage());
			}*/
		//}

		foreach($pages as $page)
		{
			//echo(print_r($page));
			$page_obj = new ilSCORM2004PageGUI($this->getType(),$page["obj_id"]);
			$page_obj->setPresentationTitle($page["title"]);
			$page_obj->setOutputMode(IL_PAGE_OFFLINE);
			$page_obj->setStyleId($this->slm_object->getStyleSheetId());
			if (count($terms) > 1)
			{
				$page_obj->setGlossaryOverviewInfo(
					ilSCORM2004ScoGUI::getGlossaryOverviewId(), $this);
			}

			$page_output = $page_obj->showPage("export");

			// collect media objects
			$mob_ids = $page_obj->getSCORM2004Page()->collectMediaObjects(false);
			foreach($mob_ids as $mob_id)
			{
				$this->mob_ids[$mob_id] = $mob_id;
				$media_obj = new ilObjMediaObject($mob_id);
				if($media_obj->hasFullscreenItem())
					$media_obj->exportMediaFullscreen($a_target_dir, $page_obj->getPageObject());
			}

			// collect glossary items
			$int_links = $page_obj->getPageObject()->getInternalLinks(true);
			include_once("./Services/COPage/classes/class.ilInternalLink.php");
			include_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
			include_once("./Services/COPage/classes/class.ilPageObject.php");
			if (is_array($int_links))
			{
				foreach ($int_links as $k => $e)
				{
					// glossary link
					if ($e["Type"] == "GlossaryItem")
					{
						$karr = explode(":", $k);
						$tid = ilInternalLink::_extractObjIdOfTarget($karr[0]);
						$dids = ilGlossaryDefinition::getDefinitionList($tid);
						foreach ($dids as $did)
						{
							$def_pg = new ilPageObject("gdf", $did["id"]);
							$def_pg->buildDom();
							$mob_ids = $def_pg->collectMediaObjects(false);
							foreach($mob_ids as $mob_id)
							{
								$this->mob_ids[$mob_id] = $mob_id;
//echo "<br>-$mob_id-";
								$media_obj = new ilObjMediaObject($mob_id);
								if($media_obj->hasFullscreenItem())
									$media_obj->exportMediaFullscreen($a_target_dir, $def_pg);
							}
							$file_ids = $def_pg->collectFileItems();
							foreach($file_ids as $file_id)
							{
								$this->file_ids[$file_id] = $file_id;
							}
						}
					}
				}
			}
//exit;
			// collect all file items
			$file_ids = $page_obj->getSCORM2004Page()->collectFileItems();
			foreach($file_ids as $file_id)
			{
				$this->file_ids[$file_id] = $file_id;
			}

			if($mode=='pdf')
			{
				$q_ids = ilSCORM2004Page::_getQuestionIdsForPage("sahs", $page["obj_id"]);
				foreach ($q_ids as $q_id)
				{
					include_once("./Modules/TestQuestionPool/classes/class.assQuestionGUI.php");
					$q_gui =& assQuestionGUI::_getQuestionGUI("", $q_id);
					$q_gui->outAdditionalOutput();
					$html = $q_gui->getPreview(TRUE);
					$page_output = preg_replace("/{{{{{Question;il__qst_".$q_id."}}}}}/i",$html,$page_output);
				}
			}

			if ($mode == 'pdf')
			{
				$sco_tpl->touchBlock("pdf_pg_break");
			}

			if ($a_asset_type != "entry_asset" && $a_asset_type != "final_asset")
			{
				$sco_tpl->setCurrentBlock("page");
				$sco_tpl->setVariable("PAGE", $page_output);
				$sco_tpl->parseCurrentBlock();
			}
			else
			{
				$sco_tpl->setVariable("ENTRY_PAGE", $page_output);
			}
		}

		// final sco success message
/*		if ($this->getSLMObject()->getFinalScoPage() && $mode != 'pdf' && $this->getType() == "sco")
		{
			$mtpl = new ilTemplate("tpl.final_message.html", true, true, "Modules/Scorm2004");
			$mtpl->setVariable("MESS", sprintf($lng->txt("sahs_sco_final_message"),
				$this->getTitle()));
			$sco_tpl->setCurrentBlock("page");
			$sco_tpl->setVariable("PAGE", $mtpl->get());
			$sco_tpl->setVariable("PAGE_ID_ATTR", "id='sco_succ_message'");
			$sco_tpl->parseCurrentBlock();
		}*/
		
		// glossary
		if ($mode!='pdf')
		{
			$sco_tpl->setVariable("GLOSSARY_HTML",
				ilSCORM2004PageGUI::getGlossaryHTML($this));
		}

		$output = $sco_tpl->get();

		if($mode=='pdf')
			$output = preg_replace("/<div class=\"ilc_page_title_PageTitle\">(.*?)<\/div>/i","<h2>$1</h2>",$output);

		//$output = preg_replace("/\.\/mobs\/mm_(\d+)\/([^\"]+)/i","./objects/il_".IL_INST_ID."_mob_$1/$2",$output);
		$output = preg_replace("/mobs\/mm_(\d+)\/([^\"]+)/i","./objects/il_".IL_INST_ID."_mob_$1/$2",$output);
		$output = preg_replace("/\.\/files\/file_(\d+)\/([^\"]+)/i","./objects/il_".IL_INST_ID."_file_$1/$2",$output);
		$output = preg_replace("/\.\/Services\/MediaObjects\/flash_mp3_player/i","./players",$output);
		$output = preg_replace("/\.\/Services\/MediaObjects\/flash_flv_player/i","./players",$output);
		$output = preg_replace("/file=..\/..\/..\/.\//i","file=../",$output);
		if($mode!='pdf')
		{
			$output = preg_replace_callback("/href=\"&mob_id=(\d+)&pg_id=(\d+)\"/",array(get_class($this), 'fixFullscreeenLink'),$output);
			// this one is for fullscreen in glossary entries
			$output = preg_replace_callback("/href=\"fullscreen_(\d+)\.html\"/",array(get_class($this), 'fixFullscreeenLink'),$output);
			$output = preg_replace_callback("/{{{{{(Question;)(il__qst_[0-9]+)}}}}}/",array(get_class($this), 'insertQuestion'),$output);
//			$output = preg_replace("/&#123;/","",$output);
//			$output = preg_replace("/&#125;/","",$output);
			$q_handling = file_get_contents('./Modules/Scorm2004/scripts/questions/question_handling.js');
			fputs(fopen($a_target_dir.'/js/questions_'.$this->getId().'.js','w+'),ilQuestionExporter::questionsJS().$q_handling);
			copy("./Modules/Scorm2004/templates/default/question_handling.css",
				$a_target_dir.'/css/question_handling.css');

			foreach(ilQuestionExporter::getMobs() as $mob_id)
			{
				$this->mob_ids[$mob_id] = $mob_id;
			}
		}
		$this->q_media = ilQuestionExporter::getFiles();
		//questions export end

		fputs(fopen($a_target_dir.'/index.html','w+'),$output);

		$this->exportFileItems($a_target_dir, $expLog);

	}
	
	/**
	 * Render navigation
	 *
	 * @param object $a_tpl template
	 * @param string $a_spacer_img path to spacer image
	 */
	static function renderNavigation($a_tpl, $a_spacer_img = "")
	{
		global $lng;
		
		if ($a_spacer_img == "")
		{
			$a_spacer_img = ilUtil::getImagePath("spacer.gif");
		}
		// previous/next navigation
		$a_tpl->setCurrentBlock("ilLMNavigation");
		$a_tpl->setVariable("TXT_PREVIOUS", $lng->txt('scplayer_previous'));
		$a_tpl->setVariable("SRC_SPACER", $a_spacer_img);
		$a_tpl->setVariable("TXT_NEXT", $lng->txt('scplayer_next'));
		$a_tpl->parseCurrentBlock();
		$a_tpl->setCurrentBlock("ilLMNavigation2");
		$a_tpl->setVariable("TXT_PREVIOUS", $lng->txt('scplayer_previous'));
		$a_tpl->setVariable("SRC_SPACER", $a_spacer_img);
		$a_tpl->setVariable("TXT_NEXT", $lng->txt('scplayer_next'));
		$a_tpl->parseCurrentBlock();
	}
	
	/**
	 * Render meta page (description/objectives at beginning)
	 *
	 * @param object $a_tpl template
	 * @param object $a_sco SCO
	 * @param string $a_asset_type asset type
	 * @param string $a_mode mode
	 */
	static function renderMetaPage($a_tpl, $a_sco, $a_asset_type = "", $mode = "")
	{
		global $lng;
		
		if ($a_sco->getType() != "sco" || $a_sco->getHideObjectivePage())
		{
			return;
		}
		
		if ($a_asset_type != "entry_asset" && $a_asset_type != "final_asset")
		{
			$meta = new ilMD($a_sco->getSLMId(), $a_sco->getId(), $a_sco->getType());
			$desc_ids = $meta->getGeneral()->getDescriptionIds();
			$sco_description = $meta->getGeneral()->getDescription($desc_ids[0])->getDescription();
		}
		
		if ($mode != 'pdf')
		{
			// title
			if ($a_asset_type != "entry_asset" && $a_asset_type != "final_asset")
			{
				$a_tpl->setCurrentBlock("title");
				$a_tpl->setVariable("SCO_TITLE", $a_sco->getTitle());
				$a_tpl->parseCurrentBlock();
			}
		}
		else
		{
			// title
			$a_tpl->setCurrentBlock("pdf_title");
			$a_tpl->setVariable("SCO_TITLE", $a_sco->getTitle());
			$a_tpl->parseCurrentBlock();
			$a_tpl->touchBlock("pdf_break");
		}

		// sco description
		if (trim($sco_description) != "")
		{
			$a_tpl->setCurrentBlock("sco_desc");
			$a_tpl->setVariable("TXT_DESC", $lng->txt("description"));
			include_once("./Services/COPage/classes/class.ilPCParagraph.php");
			$a_tpl->setVariable("VAL_DESC", self::convertLists($sco_description));
			$a_tpl->parseCurrentBlock();
		}

		if ($a_asset_type == "sco")
		{
			// sco objective(s)
			$objs = $a_sco->getObjectives();
			if (count($objs) > 0)
			{
				foreach ($objs as $objective)
				{
					$a_tpl->setCurrentBlock("sco_obj");
					$a_tpl->setVariable("VAL_OBJECTIVE", self::convertLists($objective->getObjectiveID()));
					$a_tpl->parseCurrentBlock();
				}
				$a_tpl->setCurrentBlock("sco_objs");
				$a_tpl->setVariable("TXT_OBJECTIVES", $lng->txt("sahs_objectives"));
				$a_tpl->parseCurrentBlock();
			}
		}
		$a_tpl->setCurrentBlock("meta_page");
		$a_tpl->parseCurrentBlock();
	}
	

	/**
	 * Convert * and # to lists
	 *
	 * @param string $a_text text
	 * @return string text
	 */
	static function convertLists($a_text)
	{
	 include_once("./Services/COPage/classes/class.ilPCParagraph.php");
		$a_text = nl2br($a_text);
		$a_text = str_replace(array("\n", "\r"), "", $a_text);
		$a_text = str_replace("<br>", "<br />", $a_text);
		$a_text = ilPCParagraph::input2xmlReplaceLists($a_text);
		$a_text = str_replace(
			array("<SimpleBulletList>", "</SimpleBulletList>",
				"<SimpleListItem>", "</SimpleListItem>",
				"<SimpleNumberedList>", "</SimpleNumberedList>"
				),
			array("<ul class='ilc_list_u_BulletedList'>", "</ul>",
				"<li class='ilc_list_item_StandardListItem'>", "</li>",
				"<ol class='ilc_list_o_NumberedList'>", "</ol>"
				),
			$a_text);
		return $a_text;
	}

		private function fixFullscreeenLink($matches)
	{
		$media_obj = new ilObjMediaObject($matches[1]);
		if($media_obj->hasFullscreenItem())
		{
			return "href=\"./objects/il_".IL_INST_ID."_mob_".$matches[1]."/fullscreen.html\"";
			//return "href=\"./objects/il_".IL_INST_ID."_mob_".$matches[1]."/".$media_obj->getMediaItem("Fullscreen")->getLocation()."\"";
		}
	}

	//callback function for question export
	private function insertQuestion($matches) {
		$q_exporter = new ilQuestionExporter();
		return $q_exporter->exportQuestion($matches[2], "./objects/");
	}

	function exportXMLPageObjects($a_target_dir, &$a_xml_writer, $a_inst, &$expLog)
	{
		global $ilBench;

		include_once "./Modules/Scorm2004/classes/class.ilSCORM2004PageNode.php";
		include_once "./Modules/Scorm2004/classes/class.ilSCORM2004Page.php";

		$tree = new ilTree($this->slm_id);
		$tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		$tree->setTreeTablePK("slm_id");

		$pages = $tree->getSubTree($tree->getNodeData($this->getId()),true,'page');
		/*if ($this->getSLMObject()->getFinalScoPage() > 0)
		{
			$pages[] = array("obj_id" => $this->getSLMObject()->getFinalScoPage());
		}*/
		foreach($pages as $page)
		{
			$ilBench->start("ContentObjectExport", "exportPageObject");
			$expLog->write(date("[y-m-d H:i:s] ")."Page Object ".$page["obj_id"]);

			// export xml to writer object
			$ilBench->start("ContentObjectExport", "exportPageObject_getLMPageObject");
			$page_obj = new ilSCORM2004Page($page["obj_id"]);
			$ilBench->stop("ContentObjectExport", "exportPageObject_getLMPageObject");
			$ilBench->start("ContentObjectExport", "exportPageObject_XML");
			//$page_obj->exportXMLMetaData($a_xml_writer);
			$page_obj->exportXML($a_xml_writer, "normal", $a_inst);
			$ilBench->stop("ContentObjectExport", "exportPageObject_XML");

			//collect media objects
			$ilBench->start("ContentObjectExport", "exportPageObject_CollectMedia");
			$mob_ids = $page_obj->getMediaObjectIds();
			foreach($mob_ids as $mob_id)
			{
				$this->mob_ids[$mob_id] = $mob_id;
			}
			$ilBench->stop("ContentObjectExport", "exportPageObject_CollectMedia");

			// collect all file items
			$ilBench->start("ContentObjectExport", "exportPageObject_CollectFileItems");
			$file_ids = $page_obj->getFileItemIds();
			foreach($file_ids as $file_id)
			{
				$this->file_ids[$file_id] = $file_id;
			}
			$ilBench->stop("ContentObjectExport", "exportPageObject_CollectFileItems");

			$q_ids = ilSCORM2004Page::_getQuestionIdsForPage("sahs", $page["obj_id"]);
			if (count($q_ids) > 0)
			{
				include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
				foreach ($q_ids as $q_id)
				{
					$q_obj =& assQuestion::_instanciateQuestion($q_id);
					$qti_file = fopen($a_target_dir."/qti_".$q_id.".xml", "w");
					fwrite($qti_file, $q_obj->toXML());
					fclose($qti_file);
				}
			}

			unset($page_obj);

			$ilBench->stop("ContentObjectExport", "exportPageObject");
		}
	}

	function exportXMLMediaObjects(&$a_xml_writer, $a_inst, $a_target_dir, &$expLog)
	{
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        include_once("./Modules/File/classes/class.ilObjFile.php");
		$linked_mobs = array();
		if(is_array($this->mob_ids ))
		{
			// mobs directly embedded into pages
			foreach ($this->mob_ids as $mob_id)
			{
				if ($mob_id > 0)
				{
					$expLog->write(date("[y-m-d H:i:s] ")."Media Object ".$mob_id);
					$media_obj = new ilObjMediaObject($mob_id);
					$media_obj->exportXML($a_xml_writer, $a_inst);
					$lmobs = $media_obj->getLinkedMediaObjects($this->mob_ids);
					$linked_mobs = array_merge($linked_mobs, $lmobs);
					unset($media_obj);
				}
			}

			// linked mobs (in map areas)
			foreach ($linked_mobs as $mob_id)
			{
				if ($mob_id > 0)
				{
					$expLog->write(date("[y-m-d H:i:s] ")."Media Object ".$mob_id);
					$media_obj = new ilObjMediaObject($mob_id);
					$media_obj->exportXML($a_xml_writer, $a_inst);
					unset($media_obj);
				}
			}
		}
        if(is_array($this->file_ids))
            foreach ($this->file_ids as $file_id)
            {
                $expLog->write(date("[y-m-d H:i:s] ")."File Item ".$file_id);
                $file_obj = new ilObjFile($file_id, false);
                $file_obj->export($a_target_dir);
                unset($file_obj);
            }

	}

	/**
	* export files of file itmes
	*
	*/
	function exportFileItems($a_target_dir, &$expLog)
	{
		include_once("./Modules/File/classes/class.ilObjFile.php");
		if(is_array($this->file_ids))
			foreach ($this->file_ids as $file_id)
			{
				$expLog->write(date("[y-m-d H:i:s] ")."File Item ".$file_id);
				$file_obj = new ilObjFile($file_id, false);
				$file_obj->export($a_target_dir);
				unset($file_obj);
			}

		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$linked_mobs = array();
		if(is_array($this->mob_ids ))
		{
			// mobs directly embedded into pages
			foreach ($this->mob_ids as $mob_id)
			{
				if ($mob_id > 0 && ilObject::_exists($mob_id))
				{
					$expLog->write(date("[y-m-d H:i:s] ")."Media Object ".$mob_id);
					$media_obj = new ilObjMediaObject($mob_id);
					$media_obj->exportFiles($a_target_dir, $expLog);
					$lmobs = $media_obj->getLinkedMediaObjects($this->mob_ids);
					$linked_mobs = array_merge($linked_mobs, $lmobs);

					unset($media_obj);
				}
			}

			// linked mobs (in map areas)
			foreach ($linked_mobs as $mob_id)
			{
				if ($mob_id > 0 && ilObject::_exists($mob_id))
				{
					$expLog->write(date("[y-m-d H:i:s] ")."Media Object ".$mob_id);
					$media_obj = new ilObjMediaObject($mob_id);
					$media_obj->exportFiles($a_target_dir);
					unset($media_obj);
				}
			}
		}

		//media files in questions
		foreach ($this->q_media as $media) {
			if ($media !="") {
				error_log($media);
				copy($media, $a_target_dir."/objects/".basename($media));
			}
		}
	}

 	/* export content objects meta data to xml (see ilias_co.dtd)
	 *
	 * @param	object		$a_xml_writer	ilXmlWriter object that receives the
	 *										xml data
	 */
	function exportXMLMetaData(&$a_xml_writer)
	{
		include_once("Services/MetaData/classes/class.ilMD2XML.php");
		$md2xml = new ilMD2XML($this->getSLMId(), $this->getId(), $this->getType());
		$md2xml->setExportMode(true);
		$md2xml->startExport();
		$a_xml_writer->appendXML($md2xml->getXML());
	}

	function getExportFiles()
	{
		$file = array();

		require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Export.php");

		$export = new ilSCORM2004Export($this);
		foreach ($export->getSupportedExportTypes() as $type)
		{
			$dir = $export->getExportDirectoryForType($type);
			// quit if import dir not available
			if (!@is_dir($dir) or !is_writeable($dir))
			{
				continue;
			}
			// open directory
			$cdir = dir($dir);

			// get files and save the in the array
			while ($entry = $cdir->read())
			{
				if ($entry != "." and
				$entry != ".." and
				(
					ereg("^[0-9]{10}_{2}[0-9]+_{2}(".$this->getType()."_)".$this->getId()."+\.zip\$", $entry) or
					ereg("^[0-9]{10}_{2}[0-9]+_{2}(".$this->getType()."_)".$this->getId()."+\.pdf\$", $entry) or
					ereg("^[0-9]{10}_{2}[0-9]+_{2}(".$this->getType()."_)".$this->getId()."+\.iso\$", $entry)
				))
				{
					$file[$entry.$type] = array("type" => $type, "file" => $entry,
						"size" => filesize($dir."/".$entry));
				}
			}

			// close import directory
			$cdir->close();
		}

		// sort files
		ksort ($file);
		reset ($file);
		return $file;
	}

	/**
	 * Get glossary term ids in sco
	 *
	 * @param
	 * @return
	 */
	function getGlossaryTermIds()
	{
		include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
		$childs = $this->tree->getChilds($this->getId());
		$ids = array();
		foreach ($childs as $c)
		{
			$links = ilInternalLink::_getTargetsOfSource("sahs".":pg",
				$c["child"]);
			foreach ($links as $l)
			{
				if ($l["type"] == "git" && (int) $l["inst"] == 0 && !isset($ids[$l["id"]]))
				{
					$ids[$l["id"]] = ilGlossaryTerm::_lookGlossaryTerm($l["id"]);
				}
			}

		}
		asort($ids);
		return $ids;
	}

}
?>