<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");

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
	var $ilias;			// ilias object
	var $cont_obj;		// content object (learning module | digilib book)
	var $inst_id;		// installation id
	var $mode;

	/**
	* Constructor
	* @access	public
	*/
	function ilContObjectExport(&$a_cont_obj, $a_mode = "xml")
	{
		global $ilErr, $ilDB, $ilias;

		$this->cont_obj =& $a_cont_obj;

		$this->err =& $ilErr;
		$this->ilias =& $ilias;
		$this->db =& $ilDB;
		$this->mode = $a_mode;

		$settings = $this->ilias->getAllSettings();
		//$this->inst_id = $settings["inst_id"];
		$this->inst_id = IL_INST_ID;

		$date = time();
		switch($this->mode)
		{
			case "html":
				$this->export_dir = $this->cont_obj->getExportDirectory("html");
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
	function buildExportFile()
	{
		switch ($this->mode)
		{
			case "html":
				return $this->buildExportFileHTML();
				break;
				
			case "scorm":
				return $this->buildExportFileSCORM();
				break;

			case "pdf":
				return $this->buildExportFilePDF();
				break;

			default:
				return $this->buildExportFileXML();
				break;
		}
	}

	/**
	* build xml export file
	*/
	function buildExportFileXML()
	{
		global $ilBench;

		$ilBench->start("ContentObjectExport", "buildExportFile");

		require_once("classes/class.ilXmlWriter.php");

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
//echo "ContObjExport:".$this->inst_id.":<br>";
		$ilBench->start("ContentObjectExport", "buildExportFile_getXML");
		$this->cont_obj->exportXML($this->xml, $this->inst_id,
			$this->export_dir."/".$this->subdir, $expLog);
		$ilBench->stop("ContentObjectExport", "buildExportFile_getXML");

		// export style
		if ($this->cont_obj->getStyleSheetId() > 0)
		{
			include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
			$style_obj = new ilObjStyleSheet($this->cont_obj->getStyleSheetId(), false);
			$style_obj->exportXML($this->export_dir."/".$this->subdir);
		}

		// dump xml document to screen (only for debugging reasons)
		/*
		echo "<PRE>";
		echo htmlentities($this->xml->xmlDumpMem($format));
		echo "</PRE>";
		*/

		// dump xml document to file
		$ilBench->start("ContentObjectExport", "buildExportFile_dumpToFile");
		$this->xml->xmlDumpFile($this->export_dir."/".$this->subdir."/".$this->filename
			, false);
		$ilBench->stop("ContentObjectExport", "buildExportFile_dumpToFile");

		// zip the file
		$ilBench->start("ContentObjectExport", "buildExportFile_zipFile");
//echo "-".$this->export_dir."/".$this->subdir."-";
		ilUtil::zip($this->export_dir."/".$this->subdir,
			$this->export_dir."/".$this->subdir.".zip");
		$ilBench->stop("ContentObjectExport", "buildExportFile_zipFile");

		// destroy writer object
		$this->xml->_XmlWriter;

		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export");
		$ilBench->stop("ContentObjectExport", "buildExportFile");

		return $this->export_dir."/".$this->subdir.".zip";
	}

	/**
	* build pdf offline file
	*/
	function buildExportFilePDF()
	{
		global $ilBench;

		$ilBench->start("ContentObjectExport", "buildPDFFile");

		require_once("classes/class.ilXmlWriter.php");

		$this->xml = new ilXmlWriter;

		// set dtd definition
		//$this->xml->xmlSetDtdDef("<!DOCTYPE LearningModule SYSTEM \"http://www.ilias.uni-koeln.de/download/dtd/ilias_co.dtd\">");

		// set generated comment
		//$this->xml->xmlSetGenCmt("Export of ILIAS Content Module ".
		//	$this->cont_obj->getId()." of installation ".$this->inst.".");

		// set xml header
		$this->xml->xmlHeader();

		// create directories
	//$this->cont_obj->createExportDirectory("pdf");   //not implemened!
		//ilUtil::makeDir($this->export_dir."/".$this->subdir);
		//ilUtil::makeDir($this->export_dir."/".$this->subdir."/objects");

		// get Log File
		/*
		$expDir = $this->cont_obj->getExportDirectory();
		$expLog = new ilLog($expDir, "export.log");
		$expLog->delete();
		$expLog->setLogFormat("");
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export");*/

		// get xml content
		$ilBench->start("ContentObjectExport", "buildPDFFile_getFO");
		$this->cont_obj->exportFO($this->xml,
			$this->export_dir."/".$this->subdir, $expLog);
		$ilBench->stop("ContentObjectExport", "buildPDFFile_getFO");


		// dump fo document to file
		$ilBench->start("ContentObjectExport", "buildPDFFile_dumpToFile");
//echo "dumping:".$this->export_dir."/".$this->filename;
		$this->xml->xmlDumpFile($this->export_dir."/".$this->filename
			, false);
		$ilBench->stop("ContentObjectExport", "buildPDFFile_dumpToFile");

		// convert fo to pdf file
		//$ilBench->start("ContentObjectExport", "buildExportFile_zipFile");
		include_once("classes/class.ilFOPUtil.php");
		ilFOPUtil::makePDF($this->export_dir."/".$this->filename,
			$this->export_dir."/".$this->subdir.".pdf");
		//$ilBench->stop("ContentObjectExport", "buildExportFile_zipFile");

		// destroy writer object
		$this->xml->_XmlWriter;

		//$expLog->write(date("[y-m-d H:i:s] ")."Finished Export");
		$ilBench->stop("ContentObjectExport", "buildPDFFile");
	}

	/**
	* build html package
	*/
	function buildExportFileHTML()
	{
		global $ilBench;

		$ilBench->start("ContentObjectExport", "buildHTMLPackage");

		// create directories
		$this->cont_obj->createExportDirectory("html");

		// get html content
		$ilBench->start("ContentObjectExport", "buildHTMLPackage_getHTML");
		$this->cont_obj->exportHTML($this->export_dir."/".$this->subdir, $expLog);
		$ilBench->stop("ContentObjectExport", "buildHTMLPackage_getHTML");

		//$expLog->write(date("[y-m-d H:i:s] ")."Finished Export");
		$ilBench->stop("ContentObjectExport", "buildHTMLPackage");
	}

	/**
	* build scorm package
	*/
	function buildExportFileSCORM()
	{
		global $ilBench;

		$ilBench->start("ContentObjectExport", "buildSCORMPackage");

		// create directories
		$this->cont_obj->createExportDirectory("scorm");

		// get html content
		$ilBench->start("ContentObjectExport", "buildSCORMPackage_getSCORM");
		$this->cont_obj->exportSCORM($this->export_dir."/".$this->subdir, $expLog);
		$ilBench->stop("ContentObjectExport", "buildSCORMPackage_getSCORM");

		//$expLog->write(date("[y-m-d H:i:s] ")."Finished Export");
		$ilBench->stop("ContentObjectExport", "buildSCORMPackage");
	}

}

?>
