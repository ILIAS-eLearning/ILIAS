<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

include_once "./Modules/Survey/classes/inc.SurveyConstants.php";

/**
* Export class for surveys
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
* @ingroup ModulesSurvey
*/
class ilSurveyExport
{
	var $err;			// error object
	var $db;			// database object
	var $ilias;			// ilias object
	var $survey_obj;		// survey object
	var $inst_id;		// installation id
	var $mode;
	var $subdir;
	var $filename;
	var $export_dir;

	/**
	* Constructor
	* @access	public
	*/
	function ilSurveyExport(&$a_survey_obj, $a_mode = "xml")
	{
		global $ilErr, $ilDB, $ilias;

		$this->survey_obj =& $a_survey_obj;

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
			default:
				$this->export_dir = $this->survey_obj->getExportDirectory();
				$this->subdir = $date."__".$this->inst_id."__".
					"survey"."__".$this->survey_obj->getId();
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

		$ilBench->start("SurveyExport", "buildExportFile");

		// create directories
		$this->survey_obj->createExportDirectory();
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::makeDir($this->export_dir."/".$this->subdir);
		ilUtil::makeDir($this->export_dir."/".$this->subdir."/objects");

		// get Log File
		$expDir = $this->survey_obj->getExportDirectory();
		include_once "./Services/Logging/classes/class.ilLog.php";
		$expLog = new ilLog($expDir, "export.log");
		$expLog->delete();
		$expLog->setLogFormat("");
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export");

		// write xml file
		$xmlFile = fopen($this->export_dir."/".$this->subdir."/".$this->filename, "w");
		fwrite($xmlFile, $this->survey_obj->toXML());
		fclose($xmlFile);

		// add media objects which were added with tiny mce
		$this->exportXHTMLMediaObjects($this->export_dir."/".$this->subdir);

		// zip the file
		$ilBench->start("SurveyExport", "buildExportFileXML_zipFile");
		ilUtil::zip($this->export_dir."/".$this->subdir, $this->export_dir."/".$this->subdir.".zip");
		$ilBench->stop("SurveyExport", "buildExportFileXML_zipFile");

		if (@file_exists($this->export_dir."/".$this->subdir.".zip"))
		{
			// remove export directory and contents
			if (@is_dir($this->export_dir."/".$this->subdir))
			{
				ilUtil::delDir($this->export_dir."/".$this->subdir);
			}
		}
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export");
		$ilBench->stop("SurveyExport", "buildExportFile");

		return $this->export_dir."/".$this->subdir.".zip";
	}

	function exportXHTMLMediaObjects($a_export_dir)
	{
		global $ilBench;
		$ilBench->start("SurveyExport", "exportXHTMLMediaObjects");
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");

		$mobs = ilObjMediaObject::_getMobsOfObject("svy:html", $this->survey_obj->getId());
		foreach ($mobs as $mob)
		{
			$mob_obj =& new ilObjMediaObject($mob);
			$mob_obj->exportFiles($a_export_dir);
			unset($mob_obj);
		}
		/* maybe this will be used later 
		foreach ($this->survey_obj->questions as $question_id)
		{
			$mobs = ilObjMediaObject::_getMobsOfObject("spl:html", $question_id);
			foreach ($mobs as $mob)
			{
				$mob_obj =& new ilObjMediaObject($mob);
				$mob_obj->exportFiles($a_export_dir);
				unset($mob_obj);
			}
		}*/
		$ilBench->stop("SurveyExport", "exportXHTMLMediaObjects");
	}

}

?>
