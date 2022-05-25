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

use ILIAS\Notes\InternalService;
use ILIAS\Notes\Note;

/**
 * Notes Data set class. Entities
 * - user_notes: All personal notes of a user (do not use this for object
 *               related queries. Add a new entity for this purpose.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNotesDataSet extends ilDataSet
{
    protected \ILIAS\Notes\InternalDataService $notes_data;
    protected \ILIAS\Notes\NotesManager $notes_manager;

    public function __construct()
    {
        global $DIC;

        parent::__construct();
        $this->notes_manager = $DIC->notes()->internal()->domain()->notes();
        $this->notes_data = $DIC->notes()->internal()->data();
    }

    public function getSupportedVersions() : array
    {
        return array("4.3.0");
    }
    
    protected function getXmlNamespace(string $a_entity, string $a_schema_version) : string
    {
        return "https://www.ilias.de/xml/Services/Notes/" . $a_entity;
    }
    
    protected function getTypes(
        string $a_entity,
        string $a_version
    ) : array {
        // user notes
        if ($a_entity === "user_notes") {
            switch ($a_version) {
                case "4.3.0":
                    return array(
                        "Id" => "integer",
                        "RepObjId" => "integer",
                        "ObjId" => "integer",
                        "ObjType" => "text",
                        "Type" => "integer",
                        "Author" => "integer",
                        "CreationDate" => "timestamp",
                        "NoteText" => "text",
                        "Label" => "integer",
                        "Subject" => "text",
                        "NoRepository" => "integer"
                    );
            }
        }
        return [];
    }

    public function readData(
        string $a_entity,
        string $a_version,
        array $a_ids
    ) : void {
        $ilDB = $this->db;

        // user notes
        if ($a_entity === "user_notes") {
            switch ($a_version) {
                case "4.3.0":
                    $this->getDirectDataFromQuery("SELECT id, rep_obj_id, obj_id, obj_type, type, " .
                        " author, note_text, creation_date, label, subject, no_repository " .
                        " FROM note " .
                        " WHERE " .
                        $ilDB->in("author", $a_ids, false, "integer") .
                        " AND obj_type = " . $ilDB->quote("pd", "text"));
                    break;
            }
        }
    }

    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ) : void {
        switch ($a_entity) {
            case "user_notes":
                $usr_id = $a_mapping->getMapping("Services/User", "usr", $a_rec["Author"]);
                if ($usr_id > 0) {
                    // only import real user (assigned to personal desktop) notes
                    // here.
                    if ((int) $a_rec["RepObjId"] === 0 &&
                        $a_rec["ObjId"] == $a_rec["Author"] &&
                        $a_rec["Type"] === Note::PRIVATE &&
                        $a_rec["ObjType"] === "pd") {
                        $context = $this->notes_data->context(
                            0,
                            (int) $usr_id,
                            "pd"
                        );
                        $note = $this->notes_data->note(
                            0,
                            $context,
                            $a_rec["NoteText"],
                            (int) $usr_id,
                            Note::PRIVATE,
                            $a_rec["CreationDate"]
                        );
                        $this->notes_manager->createNote(
                            $note,
                            [],
                            true
                        );
                    }
                }
                break;
        }
    }
}
