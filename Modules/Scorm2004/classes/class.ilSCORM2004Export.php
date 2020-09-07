<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

//require_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");

/**
* Export class for SCORM 2004 object
*/
class ilScorm2004Export
{
    /**
     * @var Logger
     */
    protected $log;

    private $err;			// error object
    private $db;			// database object
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
    public function __construct(&$a_cont_obj, $a_mode = "SCORM 2004 3rd")
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $this->log = $DIC["ilLog"];
        $ilErr = $DIC["ilErr"];
        $ilDB = $DIC->database();
        $ilSetting = $DIC->settings();

        $this->export_types = array("SCORM 2004 3rd","SCORM 2004 4th","SCORM 1.2","HTML","ISO","PDF",
            "HTMLOne");

        if (!in_array($a_mode, $this->export_types)) {
            die("Unsupported format");
        }
        
        $this->cont_obj = $a_cont_obj;

        $this->err = $ilErr;
        $this->db = $ilDB;
        $this->mode = $a_mode;

        $settings = $ilSetting->getAll();

        $this->inst_id = IL_INST_ID;

        switch ($this->cont_obj->getType()) {
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

    public function getExportDirectory()
    {
        return $this->getExportDirectoryForType($this->mode);
    }
    
    public function getExportDirectoryForType($type)
    {
        $ret = ilUtil::getDataDir() . "/lm_data" . "/lm_" . $this->module_id . "/export_";
        switch ($type) {
            case "ISO":
                return $ret . "_iso";
            case "PDF":
                return $ret . "_pdf";
            case "SCORM 2004 3rd":
                return $ret . "_scorm2004";
            case "SCORM 2004 4th":
                return $ret . "_scorm2004_4th";
            case "HTML":
                return $ret . "_html";
            case "HTMLOne":
                return $ret . "_html_one";
            case "SCORM 1.2":
                return $ret . "_scorm12";
        }
    }
    
    public function getExportSubDirectory()
    {
        return $this->date . "__" . $this->inst_id . "__" . $this->cont_obj->getType() . "_" . $this->cont_obj_id;
    }
    
    public function getExportFileName()
    {
        switch ($this->mode) {
            case "ISO":
                return $this->subdir . ".iso";
            case "PDF":
                return $this->subdir . ".pdf";
            default:
                return $this->subdir . ".zip";
        }
    }
    
    public function getSupportedExportTypes()
    {
        return $this->export_types;
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
            case "SCORM 2004 3rd":
                return $this->buildExportFileSCORM("2004 3rd");
            case "SCORM 2004 4th":
                return $this->buildExportFileSCORM("2004 4th");
            case "SCORM 1.2":
                return $this->buildExportFileSCORM("12");
            case "HTML":
                return $this->buildExportFileHTML();
            case "HTMLOne":
                return $this->buildExportFileHTMLOne();
            case "ISO":
                return $this->buildExportFileISO();
            case "PDF":
                return $this->buildExportFilePDF();
        }
    }

    /**
    * build xml export file
    */
    public function buildExportFileSCORM($ver)
    {

        // init the mathjax rendering for HTML export
        include_once './Services/MathJax/classes/class.ilMathJax.php';
        ilMathJax::getInstance()->init(ilMathJax::PURPOSE_EXPORT);

        require_once("./Services/Xml/classes/class.ilXmlWriter.php");

        // create directories
        $this->createExportDirectory();
        ilUtil::makeDir($this->export_dir . "/" . $this->subdir);

        // get Log File
        $expDir = $this->export_dir;
        include_once './Services/Logging/classes/class.ilLog.php';
        $expLog = new ilLog($expDir, "export.log");
        $expLog->delete();
        $expLog->setLogFormat("");
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export");

        // get xml content
        
        $this->cont_obj->exportScorm($this->inst_id, $this->export_dir . "/" . $this->subdir, $ver, $expLog);

        // zip the file
        ilUtil::zip($this->export_dir . "/" . $this->subdir, $this->export_dir . "/" . $this->subdir . ".zip", true);

        ilUtil::delDir($this->export_dir . "/" . $this->subdir);
        
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export");

        return $this->export_dir . "/" . $this->subdir . ".zip";
    }
    
    /**
    * build xml export file
    */
    public function buildExportFileHTML()
    {
        require_once("./Services/Xml/classes/class.ilXmlWriter.php");

        // init the mathjax rendering for HTML export
        include_once './Services/MathJax/classes/class.ilMathJax.php';
        ilMathJax::getInstance()->init(ilMathJax::PURPOSE_EXPORT);

        // create directories
        $this->createExportDirectory();
        ilUtil::makeDir($this->export_dir . "/" . $this->subdir);

        // get Log File
        $expDir = $this->export_dir;
        include_once './Services/Logging/classes/class.ilLog.php';
        $expLog = new ilLog($expDir, "export.log");
        $expLog->delete();
        $expLog->setLogFormat("");
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export");

        // get xml content
        $this->cont_obj->exportHTML($this->inst_id, $this->export_dir . "/" . $this->subdir, $expLog);

        // zip the file
        ilUtil::zip($this->export_dir . "/" . $this->subdir, $this->export_dir . "/" . $this->subdir . ".zip", true);
        
        ilUtil::delDir($this->export_dir . "/" . $this->subdir);
        
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export");

        return $this->export_dir . "/" . $this->subdir . ".zip";
    }
    
    /**
    * build xml export file
    */
    public function buildExportFileHTMLOne()
    {
        require_once("./Services/Xml/classes/class.ilXmlWriter.php");

        // init the mathjax rendering for HTML export
        include_once './Services/MathJax/classes/class.ilMathJax.php';
        ilMathJax::getInstance()->init(ilMathJax::PURPOSE_EXPORT);

        // create directories
        $this->createExportDirectory();
        ilUtil::makeDir($this->export_dir . "/" . $this->subdir);

        // get Log File
        $expDir = $this->export_dir;
        include_once './Services/Logging/classes/class.ilLog.php';
        $expLog = new ilLog($expDir, "export.log");
        $expLog->delete();
        $expLog->setLogFormat("");
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export");

        // get xml content
        $this->cont_obj->exportHTMLOne($this->inst_id, $this->export_dir . "/" . $this->subdir, $expLog);

        // zip the file
        ilUtil::zip($this->export_dir . "/" . $this->subdir, $this->export_dir . "/" . $this->subdir . ".zip", true);
        
        ilUtil::delDir($this->export_dir . "/" . $this->subdir);
        
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export");

        return $this->export_dir . "/" . $this->subdir . ".zip";
    }
    
    public function buildExportFileISO()
    {
        $result = "";

        // init the mathjax rendering for HTML export
        include_once './Services/MathJax/classes/class.ilMathJax.php';
        ilMathJax::getInstance()->init(ilMathJax::PURPOSE_EXPORT);

        require_once("./Services/Xml/classes/class.ilXmlWriter.php");

        // create directories
        $this->createExportDirectory();
        ilUtil::makeDir($this->export_dir . "/" . $this->subdir);

        // get Log File
        $expDir = $this->export_dir;
        include_once './Services/Logging/classes/class.ilLog.php';
        $expLog = new ilLog($expDir, "export.log");
        $expLog->delete();
        $expLog->setLogFormat("");
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export");

        // get xml content
        
        $this->cont_obj->exportHTML($this->inst_id, $this->export_dir . "/" . $this->subdir, $expLog);

        // zip the file
        if (ilUtil::CreateIsoFromFolder($this->export_dir . "/" . $this->subdir, $this->export_dir . "/" . $this->subdir . ".iso")) {
            $result = $this->export_dir . "/" . $this->subdir . ".iso";
        }

        ilUtil::delDir($this->export_dir . "/" . $this->subdir);
        
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export");

        return $result;
    }
    
    public function buildExportFilePDF()
    {

        // don't render mathjax before fo code is generated
        include_once './Services/MathJax/classes/class.ilMathJax.php';
        ilMathJax::getInstance()->init(ilMathJax::PURPOSE_DEFERRED_PDF);

        require_once("./Services/Xml/classes/class.ilXmlWriter.php");

        // create directories
        $this->createExportDirectory();
        ilUtil::makeDir($this->export_dir . "/" . $this->subdir);

        // get Log File
        $expDir = $this->export_dir;
        include_once './Services/Logging/classes/class.ilLog.php';
        $expLog = new ilLog($expDir, "export.log");
        $expLog->delete();
        $expLog->setLogFormat("");
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export");

        $fo_string = $this->cont_obj->exportPDF($this->inst_id, $this->export_dir . "/" . $this->subdir, $expLog);
        
        // now render mathjax for pdf generation
        $fo_string = ilMathJax::getInstance()
            ->init(ilMathJax::PURPOSE_PDF)
            ->setRendering(ilMathJax::RENDER_PNG_AS_FO_FILE)
            ->insertLatexImages($fo_string);


        fputs(fopen($this->export_dir . "/" . $this->subdir . '/temp.fo', 'w+'), $fo_string);

        $ilLog = $this->log;
        include_once './Services/WebServices/RPC/classes/class.ilRpcClientFactory.php';
        try {
            $pdf_base64 = ilRpcClientFactory::factory('RPCTransformationHandler')->ilFO2PDF($fo_string);
            //ilUtil::deliverData($pdf_base64->scalar,'learning_progress.pdf','application/pdf');
            fputs(fopen($this->export_dir . '/' . $this->subdir . '.pdf', 'w+'), $pdf_base64->scalar);
        } catch (Exception $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            return false;
        }

        ilUtil::delDir($this->export_dir . "/" . $this->subdir);
        
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export");

        return $this->export_dir . "/" . $this->subdir . ".pdf";
    }
    
    public function createExportDirectory()
    {
        $ilErr = $this->err;

        $lm_data_dir = ilUtil::getDataDir() . "/lm_data";
        if (!is_writable($lm_data_dir)) {
            $ilErr->raiseError("Content object Data Directory (" . $lm_data_dir . ") not writeable.", $ilErr->FATAL);
        }
        // create learning module directory (data_dir/lm_data/lm_<id>)
        $lm_dir = $lm_data_dir . "/lm_" . $this->module_id;
        ilUtil::makeDir($lm_dir);
        if (!@is_dir($lm_dir)) {
            $ilErr->raiseError("Creation of Learning Module Directory failed.", $ilErr->FATAL);
        }
        
        //$export_dir = $lm_dir."/export_".$this->mode;
        ilUtil::makeDir($this->export_dir);

        if (!@is_dir($this->export_dir)) {
            $ilErr->raiseError("Creation of Export Directory failed.", $ilErr->FATAL);
        }
    }
}
