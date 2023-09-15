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
 */
class ilGlossaryExport
{
    protected ilXmlWriter $xml;
    protected string $export_dir;
    protected string $filename;
    protected string $subdir;
    protected string $mode;
    protected ilSetting $settings;
    public ilDBInterface $db;
    public ilObjGlossary $glo_obj;
    public int $inst_id;

    public function __construct(
        ilObjGlossary $a_glo_obj,
        string $a_mode = "xml"
    ) {
        global $DIC;

        $this->settings = $DIC->settings();
        $ilDB = $DIC->database();
        $ilSetting = $DIC->settings();
        $this->glo_obj = $a_glo_obj;
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

    public function getInstId(): int
    {
        return $this->inst_id;
    }

    /**
     * build export file (complete zip file)
     */
    public function buildExportFile(): string
    {
        switch ($this->mode) {
            case "html":
                return $this->buildExportFileHTML();

            default:
                return $this->buildExportFileXML();
        }
    }

    /**
     * build export file (complete zip file)
     */
    public function buildExportFileXML(): string
    {
        $this->xml = new ilXmlWriter();

        // set dtd definition
        $this->xml->xmlSetDtdDef("<!DOCTYPE ContentObject SYSTEM \"https://www.ilias.uni-koeln.de/download/dtd/ilias_co_3_7.dtd\">");

        // set generated comment
        $this->xml->xmlSetGenCmt("Export of ILIAS Glossary " .
            $this->glo_obj->getId() . " of installation " . $this->inst_id . ".");

        // set xml header
        $this->xml->xmlHeader();

        // create directories
        $this->glo_obj->createExportDirectory();
        ilFileUtils::makeDir($this->export_dir . "/" . $this->subdir);
        ilFileUtils::makeDir($this->export_dir . "/" . $this->subdir . "/objects");

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
        ilFileUtils::zip(
            $this->export_dir . "/" . $this->subdir,
            $this->export_dir . "/" . $this->subdir . ".zip"
        );

        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export");

        return $this->export_dir . "/" . $this->subdir . ".zip";
    }

    /**
     * build html export file
     */
    public function buildExportFileHTML(): string
    {
        // create directories
        $this->glo_obj->createExportDirectory("html");

        // get html content
        $exp = new \ILIAS\Glossary\Export\GlossaryHtmlExport(
            $this->glo_obj,
            $this->export_dir,
            $this->subdir
        );
        return $exp->exportHTML();
    }
}
