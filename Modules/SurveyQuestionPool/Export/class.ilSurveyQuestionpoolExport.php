<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Export class for survey questionpools
 *
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class ilSurveyQuestionpoolExport
{
    public $db;			// database object
    public $spl_obj;		// survey questionpool object
    public $inst_id;		// installation id
    public $mode;

    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_spl_obj, $a_mode = "xml")
    {
        global $DIC;

        $ilDB = $DIC->database();

        $this->spl_obj = $a_spl_obj;
    
        $this->db = $ilDB;
        $this->mode = $a_mode;
    
        $this->inst_id = IL_INST_ID;

        $date = time();
        switch ($this->mode) {
            default:
                $this->export_dir = $this->spl_obj->getExportDirectory();
                $this->subdir = $date . "__" . $this->inst_id . "__" .
                    "spl" . "_" . $this->spl_obj->getId();
                $this->filename = $this->subdir . ".xml";
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
    public function buildExportFile($questions = null)
    {
        switch ($this->mode) {
            default:
                return $this->buildExportFileXML($questions);
                break;
        }
    }

    /**
    * build xml export file
    */
    public function buildExportFileXML($questions = null)
    {
        // create directories
        $this->spl_obj->createExportDirectory();
        ilUtil::makeDir($this->export_dir . "/" . $this->subdir);

        // get Log File
        $expLog = new ilLog($this->spl_obj->getExportDirectory(), "export.log");
        $expLog->delete();
        $expLog->setLogFormat("");
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export");
        // write qti file
        $qti_file = fopen($this->export_dir . "/" . $this->subdir . "/" . $this->filename, "w");
        fwrite($qti_file, $this->spl_obj->toXML($questions));
        fclose($qti_file);
        // destroy writer object
        $this->xml->_XmlWriter;

        ilUtil::zip(
            $this->export_dir . "/" . $this->subdir,
            $this->export_dir . "/" . $this->subdir . ".zip"
        );

        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export");

        return $this->export_dir . "/" . $this->subdir . ".zip";
    }
}
