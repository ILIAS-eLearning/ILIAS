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

require_once("./Modules/Glossary/classes/class.ilObjGlossary.php");

/**
* Export class for content objects
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @version $Id$
*
* @ingroup ModulesGlossary
*/
class ilGlossaryExport
{
    /**
     * @var ilSetting
     */
    protected $settings;

    public $err;			// error object
    public $db;			// database object
    public $glo_obj;		// glossary
    public $inst_id;		// installation id

    /**
    * Constructor
    * @access	public
    */
    public function __construct(&$a_glo_obj, $a_mode = "xml")
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $ilErr = $DIC["ilErr"];
        $ilDB = $DIC->database();
        $ilSetting = $DIC->settings();

        $this->glo_obj = $a_glo_obj;

        $this->err = $ilErr;
        $this->db = $ilDB;
        $this->mode = $a_mode;

        $settings = $ilSetting->getAll();
        // The default '0' is required for the directory structure (smeyer)
        $this->inst_id = $settings["inst_id"] ? $settings['inst_id'] : 0;

        $date = time();
        switch ($this->mode) {
            case "xml":
                $this->export_dir = $this->glo_obj->getExportDirectory();
                $this->subdir = $date . "__" . $this->inst_id . "__" .
                    $this->glo_obj->getType() . "_" . $this->glo_obj->getId();
                $this->filename = $this->subdir . ".xml";
                break;
        
            case "html":
                $this->export_dir = $this->glo_obj->getExportDirectory("html");
                $this->subdir = $this->glo_obj->getType() . "_" . $this->glo_obj->getId();
                $this->filename = $this->subdir . ".zip";
                break;

        }
    }

    public function getInstId()
    {
        return $this->inst_id;
    }
    
    /**
    *   build export file (complete zip file)
    *
    *   @access public
    *   @return
    */
    public function buildExportFile()
    {
        switch ($this->mode) {
            case "html":
                return $this->buildExportFileHTML();
                break;

            default:
                return $this->buildExportFileXML();
                break;
        }
    }

    /**
    * build export file (complete zip file)
    */
    public function buildExportFileXML()
    {
        require_once("./Services/Xml/classes/class.ilXmlWriter.php");

        $this->xml = new ilXmlWriter;

        // set dtd definition
        $this->xml->xmlSetDtdDef("<!DOCTYPE ContentObject SYSTEM \"http://www.ilias.uni-koeln.de/download/dtd/ilias_co_3_7.dtd\">");

        // set generated comment
        $this->xml->xmlSetGenCmt("Export of ILIAS Glossary " .
            $this->glo_obj->getId() . " of installation " . $this->inst . ".");

        // set xml header
        $this->xml->xmlHeader();

        // create directories
        $this->glo_obj->createExportDirectory();
        ilUtil::makeDir($this->export_dir . "/" . $this->subdir);
        ilUtil::makeDir($this->export_dir . "/" . $this->subdir . "/objects");

        // get Log File
        $expDir = $this->glo_obj->getExportDirectory();
        include_once './Services/Logging/classes/class.ilLog.php';
        $expLog = new ilLog($expDir, "export.log");
        $expLog->delete();
        $expLog->setLogFormat("");
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export");

        // get xml content
        $this->glo_obj->exportXML(
            $this->xml,
            $this->inst_id,
            $this->export_dir . "/" . $this->subdir,
            $expLog
        );



        // dump xml document to file
        $this->xml->xmlDumpFile($this->export_dir . "/" . $this->subdir . "/" . $this->filename, false);

        // zip the file
        ilUtil::zip(
            $this->export_dir . "/" . $this->subdir,
            $this->export_dir . "/" . $this->subdir . ".zip"
        );

        // destroy writer object
        $this->xml->_XmlWriter;

        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export");

        return $this->export_dir . "/" . $this->subdir . ".zip";
    }

    /**
    * build html export file
    */
    public function buildExportFileHTML()
    {
        // create directories
        $this->glo_obj->createExportDirectory("html");

        // get Log File
        $expDir = $this->glo_obj->getExportDirectory();

        // get xml content
        $this->glo_obj->exportHTML($this->export_dir . "/" . $this->subdir, $expLog);
    }
}
