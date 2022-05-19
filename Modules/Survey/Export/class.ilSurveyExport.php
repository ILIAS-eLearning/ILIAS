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
 * Export class for surveys
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class ilSurveyExport
{
    public ilDBInterface $db;			// database object
    public ilObjSurvey $survey_obj;		// survey object
    public int $inst_id;		// installation id
    public string $mode;
    public string $subdir;
    public string $filename;
    public string $export_dir;

    public function __construct(
        ilObjSurvey $a_survey_obj,
        $a_mode = "xml"
    ) {
        global $DIC;

        $ilDB = $DIC->database();

        $this->survey_obj = $a_survey_obj;
    
        $this->db = $ilDB;
        $this->mode = $a_mode;
        $this->inst_id = (int) IL_INST_ID;

        $date = time();
        switch ($this->mode) {
            default:
                $this->export_dir = $this->survey_obj->getExportDirectory();
                $this->subdir = $date . "__" . $this->inst_id . "__" .
                    "svy" . "_" . $this->survey_obj->getId();
                $this->filename = $this->subdir . ".xml";
                break;
        }
    }

    public function getInstId() : int
    {
        return $this->inst_id;
    }


    /**
     * @return string export file name
     */
    public function buildExportFile() : string
    {
        switch ($this->mode) {
            default:
                return $this->buildExportFileXML();
        }
    }

    /**
     * build xml export file
     * @return string export file name
     * @throws ilLogException
     * @throws ilSurveyException
     */
    public function buildExportFileXML() : string
    {

        // create directories
        $this->survey_obj->createExportDirectory();
        ilFileUtils::makeDir($this->export_dir . "/" . $this->subdir);
        ilFileUtils::makeDir($this->export_dir . "/" . $this->subdir . "/objects");

        // get Log File
        $expDir = $this->survey_obj->getExportDirectory();
        $expLog = new ilLog($expDir, "export.log");
        $expLog->delete();
        $expLog->setLogFormat("");
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export");

        // write xml file
        $xmlFile = fopen($this->export_dir . "/" . $this->subdir . "/" . $this->filename, 'wb');
        fwrite($xmlFile, $this->survey_obj->toXML());
        fclose($xmlFile);

        // add media objects which were added with tiny mce
        $this->exportXHTMLMediaObjects($this->export_dir . "/" . $this->subdir);

        // zip the file
        ilFileUtils::zip($this->export_dir . "/" . $this->subdir, $this->export_dir . "/" . $this->subdir . ".zip");

        if (file_exists($this->export_dir . "/" . $this->subdir . ".zip")) {
            // remove export directory and contents
            if (is_dir($this->export_dir . "/" . $this->subdir)) {
                ilFileUtils::delDir($this->export_dir . "/" . $this->subdir);
            }
        }
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export");

        return $this->export_dir . "/" . $this->subdir . ".zip";
    }

    public function exportXHTMLMediaObjects(
        string $a_export_dir
    ) : void {
        $mobs = ilObjMediaObject::_getMobsOfObject("svy:html", $this->survey_obj->getId());
        foreach ($mobs as $mob) {
            $mob_obj = new ilObjMediaObject($mob);
            $mob_obj->exportFiles($a_export_dir);
            unset($mob_obj);
        }
        // #14850
        foreach ($this->survey_obj->questions as $question_id) {
            $mobs = ilObjMediaObject::_getMobsOfObject("spl:html", $question_id);
            foreach ($mobs as $mob) {
                $mob_obj = new ilObjMediaObject($mob);
                $mob_obj->exportFiles($a_export_dir);
                unset($mob_obj);
            }
        }
    }
}
