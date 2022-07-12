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
 * Export class for survey questionpools
 *
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class ilSurveyQuestionpoolExport
{
    protected string $subdir;
    protected string $filename;
    protected string $export_dir;
    public ilDBInterface $db;
    public ilObjSurveyQuestionPool $spl_obj;
    public int $inst_id;
    public string $mode;

    public function __construct(
        ilObjSurveyQuestionPool $a_spl_obj,
        string $a_mode = "xml"
    ) {
        global $DIC;

        $ilDB = $DIC->database();

        $this->spl_obj = $a_spl_obj;
    
        $this->db = $ilDB;
        $this->mode = $a_mode;
    
        $this->inst_id = (int) IL_INST_ID;

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

    public function getInstId() : int
    {
        return $this->inst_id;
    }


    /**
     * build export file (complete zip file)
     */
    public function buildExportFile(
        array $questions = null
    ) : string {
        switch ($this->mode) {
            default:
                return $this->buildExportFileXML($questions);
        }
    }

    /**
     * build xml export file
     */
    public function buildExportFileXML(
        array $questions = null
    ) : string {
        // create directories
        $this->spl_obj->createExportDirectory();
        ilFileUtils::makeDir($this->export_dir . "/" . $this->subdir);

        // get Log File
        $expLog = new ilLog($this->spl_obj->getExportDirectory(), "export.log");
        $expLog->delete();
        $expLog->setLogFormat("");
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export");
        // write qti file
        $qti_file = fopen($this->export_dir . "/" . $this->subdir . "/" . $this->filename, 'wb');
        fwrite($qti_file, $this->spl_obj->toXML($questions));
        fclose($qti_file);

        ilFileUtils::zip(
            $this->export_dir . "/" . $this->subdir,
            $this->export_dir . "/" . $this->subdir . ".zip"
        );

        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export");

        return $this->export_dir . "/" . $this->subdir . ".zip";
    }
}
