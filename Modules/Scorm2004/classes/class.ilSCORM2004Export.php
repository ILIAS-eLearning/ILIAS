<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

//require_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");

/**
* Export class for SCORM 2004 object
*/
class ilScorm2004Export
{
	private $err;			// error object
	private $db;			// database object
	private $ilias;			// ilias object
	private $cont_obj;		// content object (learning module or sco)
	private $cont_obj_id;	// content object id (learning module or sco)
	private $inst_id;		// installation id
	private $mode;			//current export mode
	private $export_types; // list of supported export types
	private $module_id;
	
	private $date;
	private $settings;
	private $export_dir;
	private $subdir;
	private $filename;
	
	/**
	* Constructor
	* @access	public
	*/
	function ilScorm2004Export(&$a_cont_obj, $a_mode = "SCORM 2004")
	{
		global $ilErr, $ilDB, $ilias;

		$this->export_types = array("SCORM 2004","SCORM 1.2","HTML","ISO","PDF");
		
		if(!in_array($a_mode,$this->export_types))
			die("Unsupported format");
		
		$this->cont_obj =& $a_cont_obj;

		$this->err =& $ilErr;
		$this->ilias =& $ilias;
		$this->db =& $ilDB;
		$this->mode = $a_mode;

		$settings = $this->ilias->getAllSettings();

		$this->inst_id = IL_INST_ID;

		switch ($this->cont_obj->getType())
		{
			case 'sahs': 
				$this->module_id = $this->cont_obj->getId();
				$this->cont_obj_id = $this->cont_obj->getId();
				break;
			case 'sco':
				$this->module_id = $this->cont_obj->slm_id;
				$this->cont_obj_id = $this->cont_obj->getId();
				break;
		}
		
		$this->date = time();
		
		$this->export_dir = $this->getExportDirectory();
		$this->subdir = $this->getExportSubDirectory();
		$this->filename = $this->getExportFileName();
	}

	function getExportDirectory()
	{
		return $this->getExportDirectoryForType($this->mode);
	}
	
	function getExportDirectoryForType($type)
	{
		$ret = ilUtil::getDataDir()."/lm_data"."/lm_".$this->module_id."/export_";
		switch($type)
		{
			case "ISO":
				return $ret."_iso";
			case "PDF":
				return $ret."_pdf";
			case "SCORM 2004":
				return $ret."_scorm2004";
			case "HTML":
				return $ret."_html";
			case "SCORM 1.2":		
				return $ret."_scorm12";
		}
		
	}
	
	function getExportSubDirectory()
	{
		return $this->date."__".$this->inst_id."__".$this->cont_obj->getType()."_".$this->cont_obj_id;
	}
	
	function getExportFileName()
	{
		switch($this->mode)
		{
			case "ISO":
				return $this->subdir.".iso";
			case "PDF":
				return $this->subdir.".pdf";
			default:
				return $this->subdir.".zip";
		}
	}
	
	function getSupportedExportTypes()
	{
		return $this->export_types;
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
			case "SCORM 2004":
				return $this->buildExportFileSCORM("2004");
			case "SCORM 1.2":
				return $this->buildExportFileSCORM("12");
			case "HTML":
				return $this->buildExportFileHTML();
			case "ISO":
				return $this->buildExportFileISO();	
			case "PDF":
				return $this->buildExportFilePDF();	
		}
	}

	/**
	* build xml export file
	*/
	function buildExportFileSCORM($ver)
	{
		global $ilBench;

		$ilBench->start("ContentObjectExport", "buildExportFile");

		require_once("classes/class.ilXmlWriter.php");

		// create directories
		$this->createExportDirectory();
		ilUtil::makeDir($this->export_dir."/".$this->subdir);

		// get Log File
		$expDir = $this->export_dir;
		$expLog = new ilLog($expDir, "export.log");
		$expLog->delete();
		$expLog->setLogFormat("");
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export");

		// get xml content
		
		$ilBench->start("ContentObjectExport", "buildExportFile_getXML");
		$this->cont_obj->exportScorm($this->inst_id, $this->export_dir."/".$this->subdir, $ver, $expLog);
		$ilBench->stop("ContentObjectExport", "buildExportFile_getXML");

		// zip the file
		$ilBench->start("ContentObjectExport", "buildExportFile_zipFile");
//echo "-".$this->export_dir."/".$this->subdir."---".$this->export_dir."/".$this->subdir.".zip"."-";
		ilUtil::zip($this->export_dir."/".$this->subdir, $this->export_dir."/".$this->subdir.".zip", true);
		$ilBench->stop("ContentObjectExport", "buildExportFile_zipFile");
		
		ilUtil::delDir($this->export_dir."/".$this->subdir);
		
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export");
		$ilBench->stop("ContentObjectExport", "buildExportFile");

		return $this->export_dir."/".$this->subdir.".zip";
	}
	
	/**
	* build xml export file
	*/
	function buildExportFileHTML()
	{
		global $ilBench;

		$ilBench->start("ContentObjectExport", "buildExportFile");

		require_once("classes/class.ilXmlWriter.php");

		// create directories
		$this->createExportDirectory();
		ilUtil::makeDir($this->export_dir."/".$this->subdir);

		// get Log File
		$expDir = $this->export_dir;
		$expLog = new ilLog($expDir, "export.log");
		$expLog->delete();
		$expLog->setLogFormat("");
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export");

		// get xml content
		
		$ilBench->start("ContentObjectExport", "buildExportFile_getXML");
		$this->cont_obj->exportHTML($this->inst_id, $this->export_dir."/".$this->subdir, $expLog);
		$ilBench->stop("ContentObjectExport", "buildExportFile_getXML");

		// zip the file
		$ilBench->start("ContentObjectExport", "buildExportFile_zipFile");
		ilUtil::zip($this->export_dir."/".$this->subdir, $this->export_dir."/".$this->subdir.".zip", true);
		$ilBench->stop("ContentObjectExport", "buildExportFile_zipFile");
		
		ilUtil::delDir($this->export_dir."/".$this->subdir);
		
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export");
		$ilBench->stop("ContentObjectExport", "buildExportFile");

		return $this->export_dir."/".$this->subdir.".zip";
	}
	
	function buildExportFileISO()
	{
		global $ilBench;

		$ilBench->start("ContentObjectExport", "buildExportFile");

		require_once("classes/class.ilXmlWriter.php");

		// create directories
		$this->createExportDirectory();
		ilUtil::makeDir($this->export_dir."/".$this->subdir);

		// get Log File
		$expDir = $this->export_dir;
		$expLog = new ilLog($expDir, "export.log");
		$expLog->delete();
		$expLog->setLogFormat("");
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export");

		// get xml content
		
		$ilBench->start("ContentObjectExport", "buildExportFile_getXML");
		$this->cont_obj->exportHTML($this->inst_id, $this->export_dir."/".$this->subdir, $expLog);
		$ilBench->stop("ContentObjectExport", "buildExportFile_getXML");

		// zip the file
		$ilBench->start("ContentObjectExport", "buildExportFile_zipFile");
		ilUtil::CreateIsoFromFolder($this->export_dir."/".$this->subdir, $this->export_dir."/".$this->subdir.".iso");
		$ilBench->stop("ContentObjectExport", "buildExportFile_zipFile");
		
		ilUtil::delDir($this->export_dir."/".$this->subdir);
		
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export");
		$ilBench->stop("ContentObjectExport", "buildExportFile");

		return $this->export_dir."/".$this->subdir.".iso";
	}
	
	function buildExportFilePDF()
	{
		global $ilBench;

		$ilBench->start("ContentObjectExport", "buildExportFile");

		require_once("classes/class.ilXmlWriter.php");

		// create directories
		$this->createExportDirectory();
		ilUtil::makeDir($this->export_dir."/".$this->subdir);

		// get Log File
		$expDir = $this->export_dir;
		$expLog = new ilLog($expDir, "export.log");
		$expLog->delete();
		$expLog->setLogFormat("");
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export");

		// get xml content
		
		$ilBench->start("ContentObjectExport", "buildExportFile_getXML");
		$this->cont_obj->exportHTML4PDF($this->inst_id, $this->export_dir."/".$this->subdir, $expLog);
		$ilBench->stop("ContentObjectExport", "buildExportFile_getXML");

		$ilBench->start("ContentObjectExport", "buildExportFile_pdfFile");
		$files = $this->export_dir."/".$this->subdir."/index.html";
		if($this->cont_obj->getType()=='sahs')
		{
			$tree = new ilTree($this->cont_obj_id);
			$tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
			$tree->setTreeTablePK("slm_id");
			foreach($tree->getSubTree($tree->getNodeData($tree->getRootId()),true,'sco') as $sco)
				$files .= ' '.$this->export_dir.'/'.$this->subdir.'/'.$sco['obj_id'].'/index.html';		
		}
		ilUtil::htmlfile2pdf($files ,$this->export_dir."/".$this->subdir.".pdf");
		$ilBench->stop("ContentObjectExport", "buildExportFile_pdfFile");
		
		ilUtil::delDir($this->export_dir."/".$this->subdir);
		
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export");
		$ilBench->stop("ContentObjectExport", "buildExportFile");

		return $this->export_dir."/".$this->subdir.".pdf";
	}
	
	function createExportDirectory()
	{
		$lm_data_dir = ilUtil::getDataDir()."/lm_data";
		if(!is_writable($lm_data_dir))
		{
			$this->ilias->raiseError("Content object Data Directory (".$lm_data_dir.") not writeable.",$this->ilias->error_obj->FATAL);
		}
		// create learning module directory (data_dir/lm_data/lm_<id>)
		$lm_dir = $lm_data_dir."/lm_".$this->module_id;
		ilUtil::makeDir($lm_dir);
		if(!@is_dir($lm_dir))
		{
			$this->ilias->raiseError("Creation of Learning Module Directory failed.",$this->ilias->error_obj->FATAL);
		}
		
		//$export_dir = $lm_dir."/export_".$this->mode;
		ilUtil::makeDir($this->export_dir);

		if(!@is_dir($this->export_dir))
		{
			$this->ilias->raiseError("Creation of Export Directory failed.",$this->ilias->error_obj->FATAL);
		}
	}
	
}

?>
