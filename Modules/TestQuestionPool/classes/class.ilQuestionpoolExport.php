<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Export class for questionpools
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
*
* @version $Id$
*
* @ingroup ModulesTestQuestionPool
*/
class ilQuestionpoolExport
{
	var $err;			// error object
	var $db;			// database object
	var $ilias;			// ilias object
	var $qpl_obj;		// questionpool object
	var $questions; // array with question ids to export
	var $inst_id;		// installation id
	var $mode;
	var $lng;

	/**
	* Constructor
	* @access	public
	*/
	function ilQuestionpoolExport(&$a_qpl_obj, $a_mode = "xml", $array_questions = null)
	{
		global $ilErr, $ilDB, $ilias, $lng;

		$this->qpl_obj =& $a_qpl_obj;
		if (!is_array($array_questions))
		{
			$array_questions =& $a_qpl_obj->getAllQuestionIds();
		}
		
		$this->err =& $ilErr;
		$this->ilias =& $ilias;
		$this->db =& $ilDB;
		$this->mode = $a_mode;
		$this->lng =& $lng;
		
		$settings = $this->ilias->getAllSettings();
		$this->inst_id = IL_INST_ID;
		$this->questions = $array_questions;
		$date = time();
		$this->qpl_obj->createExportDirectory();
		switch($this->mode)
		{
			case "xls":
				$this->export_dir = $this->qpl_obj->getExportDirectory('xls');
				$this->filename = $date."__".$this->inst_id."__".
					"qpl"."_".$this->qpl_obj->getId() . ".xls";
				$this->zipfilename = $date."__".$this->inst_id."__".
					"qpl"."_".$this->qpl_obj->getId() . ".zip";
				break;
			case "xml":
			default:
				$this->export_dir = $this->qpl_obj->getExportDirectory('zip');
				$this->subdir = $date."__".$this->inst_id."__".
					"qpl"."_".$this->qpl_obj->getId();
				$this->filename = $this->subdir.".xml";
				$this->qti_filename = $date."__".$this->inst_id."__".
					"qti"."_".$this->qpl_obj->getId().".xml";
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
			case "xls":
				return $this->buildExportFileXLS();
				break;
			case "xml":
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

		$ilBench->start("QuestionpoolExport", "buildExportFile");

		include_once("./Services/Xml/classes/class.ilXmlWriter.php");
		$this->xml = new ilXmlWriter;

		// set dtd definition
		$this->xml->xmlSetDtdDef("<!DOCTYPE Test SYSTEM \"http://www.ilias.uni-koeln.de/download/dtd/ilias_co.dtd\">");

		// set generated comment
		$this->xml->xmlSetGenCmt("Export of ILIAS Test Questionpool ".
			$this->qpl_obj->getId()." of installation ".$this->inst.".");

		// set xml header
		$this->xml->xmlHeader();

		// create directories
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::makeDir($this->export_dir."/".$this->subdir);
		ilUtil::makeDir($this->export_dir."/".$this->subdir."/objects");

		// get Log File
		$expDir = $this->qpl_obj->getExportDirectory();
		ilUtil::makeDirParents($expDir);

		include_once "./Services/Logging/classes/class.ilLog.php";
		$expLog = new ilLog($expDir, "export.log");
		$expLog->delete();
		$expLog->setLogFormat("");
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export");
		
		// write qti file
		$qti_file = fopen($this->export_dir."/".$this->subdir."/".$this->qti_filename, "w");
		fwrite($qti_file, $this->qpl_obj->toXML($this->questions));
		fclose($qti_file);

		// get xml content
		$ilBench->start("QuestionpoolExport", "buildExportFile_getXML");
		$this->qpl_obj->exportPagesXML($this->xml, $this->inst_id,
			$this->export_dir."/".$this->subdir, $expLog, $this->questions);
		$ilBench->stop("QuestionpoolExport", "buildExportFile_getXML");

		// dump xml document to screen (only for debugging reasons)
		/*
		echo "<PRE>";
		echo htmlentities($this->xml->xmlDumpMem($format));
		echo "</PRE>";
		*/

		// dump xml document to file
		$ilBench->start("QuestionpoolExport", "buildExportFile_dumpToFile");
		$this->xml->xmlDumpFile($this->export_dir."/".$this->subdir."/".$this->filename
			, false);
		$ilBench->stop("QuestionpoolExport", "buildExportFile_dumpToFile");
		
		// add media objects which were added with tiny mce
		$ilBench->start("QuestionpoolExport", "buildExportFile_saveAdditionalMobs");
		$this->exportXHTMLMediaObjects($this->export_dir."/".$this->subdir);
		$ilBench->stop("QuestionpoolExport", "buildExportFile_saveAdditionalMobs");

		// zip the file
		$ilBench->start("QuestionpoolExport", "buildExportFile_zipFile");
		ilUtil::zip($this->export_dir."/".$this->subdir, $this->export_dir."/".$this->subdir.".zip");
		if (@is_dir($this->export_dir."/".$this->subdir))
		{
			// Do not delete this dir, since it is required for container exports
			#ilUtil::delDir($this->export_dir."/".$this->subdir);
		}

		$ilBench->stop("QuestionpoolExport", "buildExportFile_zipFile");

		// destroy writer object
		$this->xml->_XmlWriter;

		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export");
		$ilBench->stop("QuestionpoolExport", "buildExportFile");

		return $this->export_dir."/".$this->subdir.".zip";
	}

	function exportXHTMLMediaObjects($a_export_dir)
	{
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		
		foreach ($this->questions as $question_id)
		{
			$mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $question_id);
			foreach ($mobs as $mob)
			{
				if (ilObjMediaObject::_exists($mob))
				{
					$mob_obj =& new ilObjMediaObject($mob);
					$mob_obj->exportFiles($a_export_dir);
					unset($mob_obj);
				}
			}
		}
	}
	
	/**
	* build xml export file
	*/
	function buildExportFileXLS()
	{
		global $ilBench;

		$ilBench->start("QuestionpoolExport", "buildExportFile");
		include_once "./Services/Excel/classes/class.ilExcelWriterAdapter.php";
		$adapter = new ilExcelWriterAdapter($this->export_dir . "/" . $this->filename, FALSE);
		$workbook = $adapter->getWorkbook();
		$workbook->setVersion(8); // Use Excel97/2000 Format
		$format_bold =& $workbook->addFormat();
		$format_bold->setBold();
		$format_percent =& $workbook->addFormat();
		$format_percent->setNumFormat("0.00%");
		$format_datetime =& $workbook->addFormat();
		$format_datetime->setNumFormat("DD/MM/YYYY hh:mm:ss");
		$format_title =& $workbook->addFormat();
		$format_title->setBold();
		$format_title->setColor('black');
		$format_title->setPattern(1);
		$format_title->setFgColor('silver');
		$worksheet =& $workbook->addWorksheet();
		$row = 0;
		$col = 0;
		// title row
		include_once "./Services/Excel/classes/class.ilExcelUtils.php";
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$worksheet->write($row, $col, ilExcelUtils::_convert_text($this->lng->txt("title"), "latin1"), $format_title);
		$col++;
		$worksheet->write($row, $col, ilExcelUtils::_convert_text($this->lng->txt("description"), "latin1"), $format_title);
		$col++;
		$worksheet->write($row, $col, ilExcelUtils::_convert_text($this->lng->txt("question_type"), "latin1"), $format_title);
		$col++;
		$worksheet->write($row, $col, ilExcelUtils::_convert_text($this->lng->txt("author"), "latin1"), $format_title);
		$col++;
		$worksheet->write($row, $col, ilExcelUtils::_convert_text($this->lng->txt("create_date"), "latin1"), $format_title);
		$col++;
		$worksheet->write($row, $col, ilExcelUtils::_convert_text($this->lng->txt("last_update"), "latin1"), $format_title);
		$col = 0;
		$row++;
		$questions = $this->qpl_obj->getQuestionList();
		foreach ($questions as $question)
		{
			$worksheet->write($row, $col, ilExcelUtils::_convert_text($question["title"], "latin1"));
			$col++;
			$worksheet->write($row, $col, ilExcelUtils::_convert_text($question["description"], "latin1"));
			$col++;
			$worksheet->write($row, $col, ilExcelUtils::_convert_text($this->lng->txt($question["type_tag"]), "latin1"));
			$col++;
			$worksheet->write($row, $col, ilExcelUtils::_convert_text($question["author"], "latin1"));
			$col++;
			
//			ilDatePresentation::formatDate(new ilDateTime($question["created"],IL_CAL_UNIX))
			$worksheet->write($row, $col, ilExcelUtils::_convert_text(ilFormat::formatDate(date('Y-m-d', $question["created"]), "date", false, false), "latin1"));
			$col++;
			$worksheet->write($row, $col, ilExcelUtils::_convert_text(ilFormat::formatDate(date('Y-m-d', $question["tstamp"]), "date", false, false), "latin1"));
			$col = 0;
			$row++;
		}
		$workbook->close();
		ilUtil::zip($this->export_dir . "/" . $this->filename, $this->export_dir . "/" . $this->zipfilename);
		if (@file_exists($this->export_dir . "/" . $this->filename)) @unlink($this->export_dir . "/" . $this->filename);
	}
}

?>
