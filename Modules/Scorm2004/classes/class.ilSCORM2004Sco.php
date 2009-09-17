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

require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");

/**
* Class ilSCORM2004Sco
*
* SCO class for SCORM 2004 Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScorm2004
*/
class ilSCORM2004Sco extends ilSCORM2004Node
{

	var $q_media = null;		//media files in questions
	/**
	* Constructor
	* @access	public
	*/
	
	function ilSCORM2004Sco($a_slm_object, $a_id = 0)
	{
		parent::ilSCORM2004Node($a_slm_object, $a_id);
		$this->setType("sco");
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
	* overwritten method
	*/
	function create($a_upload = false,$a_template = false)
	{
		include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Item.php");
		include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Objective.php");
		parent::create($a_upload);
		if (!$a_template) {
			$seq_item = new ilSCORM2004Item($this->getId());
			$seq_item->insert();
			$obj = new ilSCORM2004Objective($this->getId());
			$obj->setObjectiveID("Objective SCO ".$this->getId());
			$obj->setId("local_obj_".$this->getID()."_0");
			$obj->update();
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
		$sco = new ilSCORM2004Sco($a_target_slm);
		$sco->setTitle($this->getTitle());
		if ($this->getSLMId() != $a_target_slm->getId())
		{
			$sco->setImportId("il__sco_".$this->getId());
		}
		$sco->setSLMId($a_target_slm->getId());
		$sco->setType($this->getType());
		$sco->setDescription($this->getDescription());
		$sco->create(true);
		$a_copied_nodes[$this->getId()] = $sco->getId();
		
		// copy meta data
		include_once("Services/MetaData/classes/class.ilMD.php");
		$md = new ilMD($this->getSLMId(), $this->getId(), $this->getType());
		$new_md =& $md->cloneMD($a_target_slm->getId(), $sco->getId(), $this->getType());
		
		return $sco;
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

	}
	
	function exportHTML($a_inst, $a_target_dir, &$expLog)
	{
		
		ilUtil::makeDir($a_target_dir.'/css');
		ilUtil::makeDir($a_target_dir.'/objects');
		ilUtil::makeDir($a_target_dir.'/images');
		ilUtil::makeDir($a_target_dir.'/js');
		ilUtil::makeDir($a_target_dir.'/players');
		
		copy('./Services/MediaObjects/flash_flv_player/flvplayer.swf', $a_target_dir.'/players/flvplayer.swf');
		copy('./Services/MediaObjects/flash_mp3_player/mp3player.swf', $a_target_dir.'/players/mp3player.swf');
		copy('./Modules/Scorm2004/scripts/scorm_2004.js',$a_target_dir.'/js/scorm.js');
		copy('./Modules/Scorm2004/scripts/pager.js',$a_target_dir.'/js/pager.js');
		copy('./Modules/Scorm2004/scripts/questions/pure.js',$a_target_dir.'/js/pure.js');
		copy('./Modules/Scorm2004/scripts/questions/jquery.js',$a_target_dir.'/js/jquery.js');
		copy('./Modules/Scorm2004/scripts/questions/jquery-ui-min.js',$a_target_dir.'/js/jquery-ui-min.js');
		
		// accordion stuff
		ilUtil::makeDir($a_target_dir.'/js/yahoo');
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		copy(ilYuiUtil::getLocalPath('yahoo/yahoo-min.js'), $a_target_dir.'/js/yahoo/yahoo-min.js');
		copy(ilYuiUtil::getLocalPath('yahoo-dom-event/yahoo-dom-event.js'), $a_target_dir.'/js/yahoo/yahoo-dom-event.js');
		copy(ilYuiUtil::getLocalPath('animation/animation-min.js'), $a_target_dir.'/js/yahoo/animation-min.js');
		copy('./Services/Accordion/js/accordion.js',$a_target_dir.'/js/accordion.js');
		copy('./Services/Accordion/css/accordion.css',$a_target_dir.'/css/accordion.css');

		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$active_css = ilObjStyleSheet::getContentStylePath($this->slm_object->getStyleSheetId());
		$active_css = split(@'\?',$active_css,2);
		$css = fread(fopen($active_css[0],'r'),filesize($active_css[0]));
		preg_match_all("/url\(([^\)]*)\)/",$css,$files);
		$currdir = getcwd();
		chdir(dirname($active_css[0]));
		foreach (array_unique($files[1]) as $fileref)
		{
			if (is_file($fileref))
			{
				copy($fileref,$a_target_dir."/images/".basename($fileref));
			}
			$css = str_replace($fileref,"../images/".basename($fileref),$css);
		}	
		chdir($currdir);
		fwrite(fopen($a_target_dir.'/css/style.css','w'),$css);
		
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
		chdir($currdir);
		fwrite(fopen($a_target_dir.'/css/system.css','w'),$css);
		//copy(ilUtil::getStyleSheetLocation("filesystem"), $a_target_dir.'/css/system.css');
		
		global $ilBench;
		
		$this->exportHTMLPageObjects($a_inst, $a_target_dir, $expLog, 'full');
		
	}
	
	function exportHTML4PDF($a_inst, $a_target_dir, &$expLog)
	{
		ilUtil::makeDir($a_target_dir.'/css');
		ilUtil::makeDir($a_target_dir.'/objects');
		ilUtil::makeDir($a_target_dir.'/images');
		$this->exportHTMLPageObjects($a_inst, $a_target_dir, &$expLog, 'pdf');
	}
	
	function exportHTMLPageObjects($a_inst, $a_target_dir, &$expLog, $mode)
	{
		global $tpl, $ilCtrl, $ilBench,$ilLog, $lng;
		
		include_once "./Modules/Scorm2004/classes/class.ilSCORM2004PageGUI.php";
		include_once "./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModuleGUI.php";
		include_once "./Services/MetaData/classes/class.ilMD.php";
		
		$output = "";
		$tree = new ilTree($this->slm_id);
		$tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		$tree->setTreeTablePK("slm_id");
		
		$meta = new ilMD($this->getSLMId(), $this->getId(), $this->getType());
				$desc_ids = $meta->getGeneral()->getDescriptionIds();
				$sco_description = $meta->getGeneral()->getDescription($desc_ids[0])->getDescription();
		
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
		
		$output = '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<link rel="stylesheet" type="text/css" href="./css/system.css" />
				<link rel="stylesheet" type="text/css" href="./css/style.css" />
				<link rel="stylesheet" type="text/css" href="./css/accordion.css" />
				<script src="./js/scorm.js" type="text/javascript" language="JavaScript1.2"></script>
				<script src="./js/jquery.js" type="text/javascript" language="JavaScript1.2"></script>
				<script src="./js/jquery-ui-min.js" type="text/javascript" language="JavaScript1.2"></script>
				<script src="./js/pager.js" type="text/javascript" language="JavaScript1.2"></script>
				<script src="./js/pure.js" type="text/javascript" language="JavaScript1.2"></script>
				<script src="./js/yahoo/yahoo-min.js" type="text/javascript" language="JavaScript1.2"></script>
				<script src="./js/yahoo/yahoo-dom-event.js" type="text/javascript" language="JavaScript1.2"></script>
				<script src="./js/yahoo/animation-min.js" type="text/javascript" language="JavaScript1.2"></script>
				<script src="./js/accordion.js" type="text/javascript" language="JavaScript1.2"></script>
				<script src="./js/questions_'. $this->getId().'.js" type="text/javascript" language="JavaScript1.2"></script>
				<title>'.$this->getTitle().'</title>
			</head>
			<body onLoad="init(0);" onunload="finish();">';
			
		if($mode!='pdf')
		$output .=	'<!-- BEGIN ilLMNavigation -->
					<div class="ilc_page_tnav_TopNavigation">
					<!-- BEGIN ilLMNavigation_Prev -->
					<div class="ilc_page_lnav_LeftNavigation">
					<a class="ilc_page_lnavlink_LeftNavigationLink">
					<img class="ilc_page_lnavimage_LeftNavigationImage" border="0" src="./images/spacer.gif" alt="" title="" class="ilc_page_rnavimage_RightNavigationImage" />&nbsp;Prev</a>
					</div>
					<!-- END ilLMNavigation_Prev -->
					<!-- BEGIN ilLMNavigation_Next -->
					<div class="ilc_page_rnav_RightNavigation">
					<a class="ilc_page_rnavlink_RightNavigationLink">Next&nbsp;<img class="ilc_page_rnavimage_RightNavigationImage" border="0" src="./images/spacer.gif" alt="" title="" class="ilc_page_rnavimage_RightNavigationImage" /></a>
					</div>
					<!-- END ilLMNavigation_Next -->
					<div style="clear:both;"></div>
					</div>
					<!-- END ilLMNavigation -->';
		
		$output .='<table class="ilc_page_cont_PageContainer" width="100%" cellspacing="0" cellpadding="0" style="display: table;">
				   <tbody><tr><td><div class="ilc_page_Page">';
		if($mode!='pdf')
			$output .='<div class="ilc_sco_title_Title">'.$this->getTitle().'</div>';
		else
			$output .='<h1>'.$this->getTitle().'</h1>';
			
		// sco description
		if (trim($sco_description) != "")
		{
			$output .='<div class="ilc_sco_desct_DescriptionTop">'.$lng->txt("description").'</div>';
			$output .='<div class="ilc_sco_desc_Description">'.$sco_description.'</div>';
		}
		
		// sco objective(s)
		$objs = $this->getObjectives();
		if (count($objs) > 0)
		{
			$output .='<div class="ilc_sco_objt_ObjectiveTop">'.$lng->txt("sahs_objectives").'</div>';
			foreach ($objs as $objective)
			{
				$output .= '<div class="ilc_sco_obj_Objective">'.nl2br($objective->getObjectiveID()).'</div>';
			}
			$output .= "</div>";
		}
		$output .='</td><tr></table>';
		if($mode=='pdf') $output .='<!-- PAGE BREAK -->';
		foreach($tree->getSubTree($tree->getNodeData($this->getId()),true,'page') as $page)
		{
			//echo(print_r($page));
			$page_obj = new ilSCORM2004PageGUI($this->getType(),$page["obj_id"]);
			$page_obj->setPresentationTitle($page["title"]);
			$page_obj->setOutputMode(IL_PAGE_OFFLINE);
			$page_obj->setStyleId($this->slm_object->getStyleSheetId());
			$output .= '<table class="ilc_page_cont_PageContainer" width="100%" cellspacing="0" cellpadding="0" style="display: table;"><tbody><tr><td><div class="ilc_page_Page">'.$page_obj->showPage("export")."</div></td></tr></table>";
			if($mode=='pdf') $output .='<!-- PAGE BREAK -->';
			// collect media objects
			$ilBench->start("ContentObjectExport", "exportPageObject_CollectMedia");
			$mob_ids = $page_obj->getSCORM2004Page()->collectMediaObjects(false);
			foreach($mob_ids as $mob_id)
			{
				$this->mob_ids[$mob_id] = $mob_id;
			}
			$ilBench->stop("ContentObjectExport", "exportPageObject_CollectMedia");

			// collect all file items
			$ilBench->start("ContentObjectExport", "exportPageObject_CollectFileItems");
			$file_ids = $page_obj->getSCORM2004Page()->collectFileItems();
			foreach($file_ids as $file_id)
			{
				$this->file_ids[$file_id] = $file_id;
			}
			$ilBench->stop("ContentObjectExport", "exportPageObject_CollectFileItems");
			
			if($mode=='pdf') 
			{
				$q_ids = ilSCORM2004Page::_getQuestionIdsForPage("sahs", $page["obj_id"]);
				foreach ($q_ids as $q_id)
				{
					include_once("./Modules/TestQuestionPool/classes/class.assQuestionGUI.php");
					$q_gui =& assQuestionGUI::_getQuestionGUI("", $q_id);
					$q_gui->outAdditionalOutput();
					$html = $q_gui->getPreview(TRUE);
					$output = preg_replace("/&#123;&#123;&#123;&#123;&#123;Question;il__qst_".$q_id."&#125;&#125;&#125;&#125;&#125;/i",$html,$output);				
				}
			}
		}
		
		if($mode!='pdf') 
		$output .=	'<!-- BEGIN ilLMNavigation2 -->
					<div class="ilc_page_bnav_BottomNavigation">
					<!-- BEGIN ilLMNavigation_Prev -->
					<div class="ilc_page_lnav_LeftNavigation">
					<a class="ilc_page_lnavlink_LeftNavigationLink">
					<img class="ilc_page_lnavimage_LeftNavigationImage" border="0" src="./images/spacer.gif" alt="" title="" class="ilc_page_rnavimage_RightNavigationImage" />&nbsp;Prev</a>
					</div>
					<!-- END ilLMNavigation_Prev -->
					<!-- BEGIN ilLMNavigation_Next -->
					<div class="ilc_page_rnav_RightNavigation">
					<a class="ilc_page_rnavlink_RightNavigationLink">Next&nbsp;<img class="ilc_page_rnavimage_RightNavigationImage" border="0" src="./images/spacer.gif" alt="" title="" class="ilc_page_rnavimage_RightNavigationImage" /></a>
					</div>
					<!-- END ilLMNavigation_Next -->
					<div style="clear:both;"></div>
					</div>
					<!-- END ilLMNavigation2 -->';
		
		$output .= '</body></html>';
		
		if($mode=='pdf')
			$output = preg_replace("/<div class=\"ilc_page_title_PageTitle\">(.*?)<\/div>/i","<h2>$1</h2>",$output);
		
		$output = preg_replace("/\.\/mobs\/mm_(\d+)\/([^\"]+)/i","./objects/il_".IL_INST_ID."_mob_$1/$2",$output);
		$output = preg_replace("/\.\/files\/file_(\d+)\/([^\"]+)/i","./objects/il_".IL_INST_ID."_file_$1/$2",$output);
		$output = preg_replace("/\.\/Services\/MediaObjects\/flash_mp3_player/i","./players",$output);
		$output = preg_replace("/\.\/Services\/MediaObjects\/flash_flv_player/i","./players",$output);
		$output = preg_replace("/file=..\/..\/..\/.\//i","file=../",$output);

		//export questions
		require_once './Modules/Scorm2004/classes/class.ilQuestionExporter.php';
		if($mode!='pdf')
		{
		$output = preg_replace_callback("/(Question;)(il__qst_[0-9]+)/",array(get_class($this), 'insertQuestion'),$output);
		$output = preg_replace("/&#123;/","",$output);
		$output = preg_replace("/&#125;/","",$output);
		$q_handling = file_get_contents('./Modules/Scorm2004/scripts/questions/question_handling.js');
		fputs(fopen($a_target_dir.'/js/questions_'.$this->getId().'.js','w+'),ilQuestionExporter::questionsJS().$q_handling);
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
	
	
	//callback function for question export
	private function insertQuestion($matches) {
		$q_exporter = new ilQuestionExporter();
		return $q_exporter->exportQuestion($matches[2]);
	}
	
	function exportXMLPageObjects($a_target_dir, &$a_xml_writer, $a_inst, &$expLog)
	{
		global $ilBench;

		include_once "./Modules/Scorm2004/classes/class.ilSCORM2004PageNode.php";
		include_once "./Modules/Scorm2004/classes/class.ilSCORM2004Page.php";
		
		$tree = new ilTree($this->slm_id);
		$tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		$tree->setTreeTablePK("slm_id");
		foreach($tree->getSubTree($tree->getNodeData($this->getId()),true,'page') as $page)
		{
			$ilBench->start("ContentObjectExport", "exportPageObject");
			$expLog->write(date("[y-m-d H:i:s] ")."Page Object ".$page["obj_id"]);
			
			// export xml to writer object
			$ilBench->start("ContentObjectExport", "exportPageObject_getLMPageObject");
			$page_obj = new ilSCORM2004Page($page["obj_id"]);
			$ilBench->stop("ContentObjectExport", "exportPageObject_getLMPageObject");
			$ilBench->start("ContentObjectExport", "exportPageObject_XML");
			$page_obj->exportXMLMetaData($a_xml_writer);
			$page_obj->exportXML($a_xml_writer, "normal", $a_inst);
			$ilBench->stop("ContentObjectExport", "exportPageObject_XML");

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
}
?>
