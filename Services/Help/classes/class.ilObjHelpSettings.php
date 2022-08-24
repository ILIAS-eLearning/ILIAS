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
 * Help settings application class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjHelpSettings extends ilObject2
{
    protected ilSetting $settings;

    public function __construct()
    {
        parent::__construct();
        global $DIC;

        $this->db = $DIC->database();
        $this->settings = $DIC->settings();
    }

    protected function initType(): void
    {
        $this->type = "hlps";
    }

    public static function createHelpModule(): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $id = $ilDB->nextId("help_module");

        $ilDB->manipulate("INSERT INTO help_module " .
            "(id) VALUES (" .
            $ilDB->quote($id, "integer") .
            ")");

        return $id;
    }

    public static function writeHelpModuleLmId(
        int $a_id,
        int $a_lm_id
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate(
            "UPDATE help_module SET " .
            " lm_id = " . $ilDB->quote($a_lm_id, "integer") .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
        );
    }


    public function uploadHelpModule(
        array $a_file
    ): void {
        $id = self::createHelpModule();

        try {
            $imp = new ilImport();
            $conf = $imp->getConfig("Services/Help");
            $conf->setModuleId($id);
            $new_id = $imp->importObject(null, $a_file["tmp_name"], $a_file["name"], "lm", "Modules/LearningModule"); //
            $newObj = new ilObjLearningModule($new_id, false);

            self::writeHelpModuleLmId($id, $newObj->getId());
        } catch (ilManifestFileNotFoundImportException $e) {
            throw new ilLMOldExportFileException("This file seems to be from ILIAS version 5.0.x or lower. Import is not supported anymore.");
        }


        $GLOBALS['ilAppEventHandler']->raise(
            'Services/Help',
            'create',
            array(
                'obj_id' => $id,
                'obj_type' => 'lm'
            )
        );
    }

    public function getHelpModules(): array
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT * FROM help_module");

        $mods = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (ilObject::_lookupType((int) $rec["lm_id"]) === "lm") {
                $rec["title"] = ilObject::_lookupTitle($rec["lm_id"]);
                $rec["create_date"] = ilObject::_lookupCreationDate((int) $rec["lm_id"]);
            }

            $mods[] = $rec;
        }

        return $mods;
    }

    public static function lookupModuleTitle(
        int $a_id
    ): string {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM help_module " .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        if (ilObject::_lookupType((int) $rec["lm_id"]) === "lm") {
            return ilObject::_lookupTitle((int) $rec["lm_id"]);
        }
        return "";
    }

    public static function lookupModuleLmId(
        int $a_id
    ): int {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT lm_id FROM help_module " .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["lm_id"];
    }

    public function deleteModule(
        int $a_id
    ): void {
        $ilDB = $this->db;
        $ilSetting = $this->settings;

        // if this is the currently activated one, deactivate it first
        if ($a_id === (int) $ilSetting->get("help_module")) {
            $ilSetting->set("help_module", "");
        }

        $set = $ilDB->query(
            "SELECT * FROM help_module " .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);

        // delete learning module
        if (ilObject::_lookupType($rec["lm_id"]) === "lm") {
            $lm = new ilObjLearningModule((int) $rec["lm_id"], false);
            $lm->delete();
        }

        // delete mappings
        ilHelpMapping::deleteEntriesOfModule($a_id);

        // delete tooltips
        ilHelp::deleteTooltipsOfModule($a_id);

        // delete help module record
        $ilDB->manipulate("DELETE FROM help_module WHERE " .
            " id = " . $ilDB->quote($a_id, "integer"));
    }

    /**
     * Check if LM is a help LM
     */
    public static function isHelpLM(
        int $a_lm_id
    ): bool {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT id FROM help_module " .
            " WHERE lm_id = " . $ilDB->quote($a_lm_id, "integer")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }
}
