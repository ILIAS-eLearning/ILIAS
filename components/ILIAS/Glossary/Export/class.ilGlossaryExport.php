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
    protected string $export_dir = "";
    protected string $filename = "";
    protected string $subdir = "";
    protected string $mode;
    protected ilSetting $settings;
    protected ilDBInterface $db;
    public ilObjGlossary $glo_obj;
    public int $inst_id;

    public function __construct(
        ilObjGlossary $a_glo_obj,
        string $a_mode
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
        if ($this->mode == "html") {
            $this->export_dir = $this->glo_obj->getExportDirectory("html");
            $this->subdir = $this->glo_obj->getType() . "_" . $this->glo_obj->getId();
            $this->filename = $this->subdir . ".zip";
        }
    }

    public function getInstId(): int
    {
        return $this->inst_id;
    }

    /**
     * build html export file
     */
    public function buildExportFileHTML(): void
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
