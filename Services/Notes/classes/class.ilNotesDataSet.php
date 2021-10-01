<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Notes Data set class. Entities
 * - user_notes: All personal notes of a user (do not use this for object
 *               related queries. Add a new entity for this purpose.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ServicesNotes
 */
class ilNotesDataSet extends ilDataSet
{
    /**
     * Get supported versions
     * @param
     * @return array
     */
    public function getSupportedVersions() : array
    {
        return array("4.3.0");
    }
    
    /**
     * Get xml namespace
     * @param
     * @return string
     */
    public function getXmlNamespace(string $a_entity, string $a_schema_version) : string
    {
        return "http://www.ilias.de/xml/Services/Notes/" . $a_entity;
    }
    
    /**
     * Get field types for entity
     * @param
     * @return array
     */
    protected function getTypes(string $a_entity, string $a_version) : array
    {
        // user notes
        if ($a_entity == "user_notes") {
            switch ($a_version) {
                case "4.3.0":
                    return array(
                        "Id" => "integer",
                        "RepObjId" => "integer",
                        "ObjId" => "integer",
                        "ObjType" => "text",
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
    }

    /**
     * Read data
     * @param
     * @return void
     */
    public function readData(string $a_entity, string $a_version, array $a_ids) : void
    {
        $ilDB = $this->db;

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }

        // user notes
        if ($a_entity == "user_notes") {
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
    
    /**
     * Determine the dependent sets of data
     */
    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ) : array {
        return [];
    }
    
    ////
    //// Needs abstraction (interface?) and version handling
    ////
    
    
    /**
     * Import record
     * @param
     * @return void
     */
    public function importRecord(string $a_entity, array $a_types, array $a_rec, ilImportMapping $a_mapping, string $a_schema_version) : void
    {
        switch ($a_entity) {
            case "user_notes":
                $usr_id = $a_mapping->getMapping("Services/User", "usr", $a_rec["Author"]);
                if ($usr_id > 0) {
                    include_once("./Services/Notes/classes/class.ilNote.php");
                    
                    // only import real user (assigned to personal desktop) notes
                    // here.
                    if ((int) $a_rec["RepObjId"] == 0 &&
                        $a_rec["ObjId"] == $a_rec["Author"] &&
                        $a_rec["Type"] == IL_NOTE_PRIVATE &&
                        $a_rec["ObjType"] == "pd") {
                        $note = new ilNote();
                        $note->setObject("pd", 0, $usr_id);
                        $note->setType(IL_NOTE_PRIVATE);
                        $note->setAuthor($usr_id);
                        $note->setText($a_rec["NoteText"]);
                        $note->setSubject($a_rec["Subject"]);
                        $note->setCreationDate($a_rec["CreationDate"]);
                        $note->setLabel($a_rec["Label"]);
                        $note->create(true);
                    }
                }
                break;
        }
    }
}
