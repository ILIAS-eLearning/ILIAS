<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Export class for content objects
 *
 * @author Alexander Killing <killing@leifos.de>
 * @author Databay AG <ay@databay.de>
 */
class ilContObjectExport
{
    protected int $inst;
    protected ilXmlWriter $xml;
    protected string $filename;
    protected string $subdir;
    protected string $export_dir;
    protected string $lang;
    public ilDBInterface $db;
    public ilObjLearningModule $cont_obj;
    public int $inst_id;
    public string $mode;

    public function __construct(
        ilObjLearningModule $a_cont_obj,
        string $a_mode = "xml",
        string $a_lang = ""
    ) {
        global $DIC;

        $ilDB = $DIC->database();

        $this->cont_obj = $a_cont_obj;

        $this->db = $ilDB;
        $this->mode = $a_mode;
        $this->lang = $a_lang;

        $this->inst_id = (int) IL_INST_ID;

        $date = time();
        switch ($this->mode) {
            case "html":
                if ($this->lang == "") {
                    $this->export_dir = $this->cont_obj->getExportDirectory("html");
                } else {
                    $this->export_dir = $this->cont_obj->getExportDirectory("html_" . $this->lang);
                }
                $this->subdir = $this->cont_obj->getType() . "_" . $this->cont_obj->getId();
                $this->filename = $this->subdir . ".zip";
                break;

            default:
                $this->export_dir = $this->cont_obj->getExportDirectory();
                $this->subdir = $date . "__" . $this->inst_id . "__" .
                    $this->cont_obj->getType() . "_" . $this->cont_obj->getId();
                $this->filename = $this->subdir . ".xml";
                break;
        }
    }

    public function getInstId() : int
    {
        return $this->inst_id;
    }

    public function buildExportFile(
        string $a_mode = ""
    ) : void {
        switch ($this->mode) {
            case "html":
                $this->buildExportFileHTML();
                break;
                
            default:
                $this->buildExportFileXML($a_mode);
                break;
        }
    }

    /**
     * build xml export file
     */
    public function buildExportFileXML(
        string $a_mode = ""
    ) : string {
        if (in_array($a_mode, array("master", "masternomedia"))) {
            $exp = new ilExport();
            $conf = $exp->getConfig("Modules/LearningModule");
            $conf->setMasterLanguageOnly(true, ($a_mode == "master"));
            $exp->exportObject($this->cont_obj->getType(), $this->cont_obj->getId(), "5.1.0");
            return "";
        }

        $this->xml = new ilXmlWriter();

        // set dtd definition
        $this->xml->xmlSetDtdDef("<!DOCTYPE ContentObject SYSTEM \"https://www.ilias.de/download/dtd/ilias_co_3_7.dtd\">");

        // set generated comment
        $this->xml->xmlSetGenCmt("Export of ILIAS Content Module " .
            $this->cont_obj->getId() . " of installation " . $this->inst . ".");

        // set xml header
        $this->xml->xmlHeader();

        // create directories
        $this->cont_obj->createExportDirectory();
        ilFileUtils::makeDir($this->export_dir . "/" . $this->subdir);
        ilFileUtils::makeDir($this->export_dir . "/" . $this->subdir . "/objects");

        // get Log File
        $expDir = $this->cont_obj->getExportDirectory();
        $expLog = new ilLog($expDir, "export.log");
        $expLog->delete();
        $expLog->setLogFormat("");
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export");

        // get xml content
        $this->cont_obj->exportXML(
            $this->xml,
            $this->inst_id,
            $this->export_dir . "/" . $this->subdir,
            $expLog
        );

        // export style
        /*
        if ($this->cont_obj->getStyleSheetId() > 0) {
            $style_obj = new ilObjStyleSheet($this->cont_obj->getStyleSheetId(), false);
            $style_obj->setExportSubDir("style");
            $style_file = $style_obj->export();
            if (is_file($style_file)) {
                copy($style_file, $this->export_dir . "/" . $this->subdir . "/style.zip");
            }
        }*/

        // dump xml document to file
        $this->xml->xmlDumpFile($this->export_dir . "/" . $this->subdir . "/" . $this->filename, false);
        
        // help export (workaround to use ref id here)
        if (ilObjContentObject::isOnlineHelpModule(
            $this->cont_obj->getRefId()
        )) {
            $exp = new ilExport();
            $exp->exportEntity(
                "help",
                $this->cont_obj->getId(),
                "4.3.0",
                "Services/Help",
                "OnlineHelp",
                $this->export_dir . "/" . $this->subdir
            );
        }

        // zip the file
        ilFileUtils::zip(
            $this->export_dir . "/" . $this->subdir,
            $this->export_dir . "/" . $this->subdir . ".zip"
        );

        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export");

        return $this->export_dir . "/" . $this->subdir . ".zip";
    }

    public function buildExportFileHTML() : void
    {
        // create directories
        if ($this->lang == "") {
            $this->cont_obj->createExportDirectory("html");
        } else {
            $this->cont_obj->createExportDirectory("html_" . $this->lang);
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
}
