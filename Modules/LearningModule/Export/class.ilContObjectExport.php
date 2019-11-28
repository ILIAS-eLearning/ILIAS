<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Export class for content objects
*
* @author Alex Killing <alex.killing@gmx.de>
* @author Databay AG <ay@databay.de>
*
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilContObjectExport
{
	var $err;			// error object
	var $db;			// database object
	var $cont_obj;		// content object (learning module | digilib book)
	var $inst_id;		// installation id
	var $mode;

	/**
	* Constructor
	* @access	public
	*/
	function __construct(&$a_cont_obj, $a_mode = "xml", $a_lang = "")
	{
		global $DIC;

		$ilErr = $DIC["ilErr"];
		$ilDB = $DIC->database();

		$this->cont_obj = $a_cont_obj;

		$this->err = $ilErr;
		$this->db = $ilDB;
		$this->mode = $a_mode;
		$this->lang = $a_lang;

		$this->inst_id = IL_INST_ID;

		$date = time();
		switch($this->mode)
		{
			case "html":
				if ($this->lang == "")
				{
					$this->export_dir = $this->cont_obj->getExportDirectory("html");
				}
				else
				{
					$this->export_dir = $this->cont_obj->getExportDirectory("html_".$this->lang);
				}
				$this->subdir = $this->cont_obj->getType()."_".$this->cont_obj->getId();
				$this->filename = $this->subdir.".zip";
				break;

			case "scorm":
				$this->export_dir = $this->cont_obj->getExportDirectory("scorm");
				$this->subdir = $this->cont_obj->getType()."_".$this->cont_obj->getId();
				$this->filename = $this->subdir.".zip";
				break;
			
			case "pdf":
				$this->export_dir = $this->cont_obj->getOfflineDirectory();
				$this->subdir = $date."__".$this->inst_id."__".
					$this->cont_obj->getType()."_".$this->cont_obj->getId();
				$this->filename = $this->subdir.".fo";
				break;

			default:
				$this->export_dir = $this->cont_obj->getExportDirectory();
				$this->subdir = $date."__".$this->inst_id."__".
					$this->cont_obj->getType()."_".$this->cont_obj->getId();
				$this->filename = $this->subdir.".xml";
				break;
		}
	}

	function getInstId()
	{
		return $this->inst_id;
	}

	/**
	*   build export file (complete zip file)
	*
	*   @access public
	*   @return
	*/
	function buildExportFile($a_mode = "")
	{
		switch ($this->mode)
		{
			case "html":
				$this->buildExportFileHTML();
				break;
				
			case "scorm":
				$this->buildExportFileSCORM();
				break;

			case "pdf":
				$this->buildExportFilePDF();
				break;

			default:
				return $this->buildExportFileXML($a_mode);
				break;
		}
	}

	/**
	* build xml export file
	*/
	function buildExportFileXML($a_mode = "")
	{
		if (in_array($a_mode, array("master", "masternomedia")))
		{
			$exp = new ilExport();
			$conf = $exp->getConfig("Modules/LearningModule");
			$conf->setMasterLanguageOnly(true, ($a_mode == "master"));
			$exp->exportObject($this->cont_obj->getType(),$this->cont_obj->getId(), "5.1.0");
			return;
		}

		$this->xml = new ilXmlWriter;

		// set dtd definition
		$this->xml->xmlSetDtdDef("<!DOCTYPE ContentObject SYSTEM \"http://www.ilias.de/download/dtd/ilias_co_3_7.dtd\">");

		// set generated comment
		$this->xml->xmlSetGenCmt("Export of ILIAS Content Module ".
			$this->cont_obj->getId()." of installation ".$this->inst.".");

		// set xml header
		$this->xml->xmlHeader();

		// create directories
		$this->cont_obj->createExportDirectory();
		ilUtil::makeDir($this->export_dir."/".$this->subdir);
		ilUtil::makeDir($this->export_dir."/".$this->subdir."/objects");

		// get Log File
		$expDir = $this->cont_obj->getExportDirectory();
		$expLog = new ilLog($expDir, "export.log");
		$expLog->delete();
		$expLog->setLogFormat("");
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export");

		// get xml content
		$this->cont_obj->exportXML($this->xml, $this->inst_id,
			$this->export_dir."/".$this->subdir, $expLog);

		// export style
		if ($this->cont_obj->getStyleSheetId() > 0)
		{
			$style_obj = new ilObjStyleSheet($this->cont_obj->getStyleSheetId(), false);
			//$style_obj->exportXML($this->export_dir."/".$this->subdir);
			$style_obj->setExportSubDir("style");
			$style_file = $style_obj->export();
			if (is_file($style_file))
			{
				copy($style_file, $this->export_dir."/".$this->subdir."/style.zip");
			}
		}

		// dump xml document to screen (only for debugging reasons)
		/*
		echo "<PRE>";
		echo htmlentities($this->xml->xmlDumpMem($format));
		echo "</PRE>";
		*/

		// dump xml document to file
		$this->xml->xmlDumpFile($this->export_dir."/".$this->subdir."/".$this->filename
			, false);
		
		// help export (workaround to use ref id here)
		if (ilObjContentObject::isOnlineHelpModule((int) $_GET["ref_id"]))
		{
			$exp = new ilExport();
			$exp->exportEntity("help", $this->cont_obj->getId(), "4.3.0", "Services/Help",
				"OnlineHelp", $this->export_dir."/".$this->subdir);
		}

		// zip the file
		ilUtil::zip($this->export_dir."/".$this->subdir,
			$this->export_dir."/".$this->subdir.".zip");

		// destroy writer object
		$this->xml->_XmlWriter;

		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export");

		return $this->export_dir."/".$this->subdir.".zip";
	}

	/**
	* build pdf offline file
	*/
	function buildExportFilePDF()
	{
		die("deprecated.");		
	}

	/**
	* build html package
	*/
	function buildExportFileHTML()
	{
		// create directories
		if ($this->lang == "")
		{
			$this->cont_obj->createExportDirectory("html");
		}
		else
		{
			$this->cont_obj->createExportDirectory("html_".$this->lang);
		}


		// get html content
		$exp = new \ILIAS\LearningModule\Export\LMHtmlExport(
			$this->cont_obj,
			$this->export_dir,
			$this->subdir,
			"html",
			$this->lang
		);
		$exp->exportHTML(true);
	}

	/**
	* build scorm package
	*/
	function buildExportFileSCORM()
	{
		// create directories
		$this->cont_obj->createExportDirectory("scorm");

		// get html content
		$this->cont_obj->exportSCORM($this->export_dir."/".$this->subdir, $expLog);
	}

}

?>
