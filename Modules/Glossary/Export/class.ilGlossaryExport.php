<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Export class for content objects
 *
 * @author Alex Killing <alex.killing@gmx.de>
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

        // get html content
        $exp = new \ILIAS\Glossary\Export\GlossaryHtmlExport(
            $this->glo_obj,
            $this->export_dir,
            $this->subdir
        );
        $exp->exportHTML();
    }
}
