<?php declare(strict_types=1);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Abstract parent class for all advanced md claiming plugin classes.
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 */
abstract class ilAdvancedMDClaimingPlugin extends ilPlugin
{
    abstract public function checkPermission(
        int $a_user_id,
        int $a_context_type,
        int $a_context_id,
        int $a_action_id,
        int $a_action_sub_id
    ) : bool;

    public static function hasDBRecord(int $a_record_id) : bool
    {
        global $DIC;
        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT record_id FROM adv_md_record" .
            " WHERE record_id = " . $ilDB->quote($a_record_id, "integer"));
        return (bool) $ilDB->numRows($set);
    }

    /**
     * @param string   $a_title
     * @param string   $a_description
     * @param bool     $a_active
     * @param string[] $a_obj_types
     * @return int
     */
    public static function createDBRecord(
        string $a_title,
        string $a_description,
        bool $a_active,
        array $a_obj_types
    ) : int {
        global $DIC;

        $ilDB = $DIC->database();

        $record_id = $ilDB->nextId("adv_md_record");

        $fields = array(
            "record_id" => array("integer", $record_id),
            "import_id" => array("text", 'il_' . IL_INST_ID . '_adv_md_record_' . $record_id),
            "title" => array("text", trim($a_title)),
            "description" => array("text", trim($a_description)),
            "active" => array("integer", (int) $a_active)
        );
        $ilDB->insert("adv_md_record", $fields);
        self::saveRecordObjTypes($record_id, $a_obj_types);
        return $record_id;
    }

    /**
     * Validate object type
     * @todo support ecs type
     */
    protected static function isValidObjType(string $a_obj_type, bool $a_is_substitution = false) : bool
    {
        // ecs not supported yet
        $valid = ["crs", "cat", "book", "wiki", "glo", "orgu", "prg", 'grp', 'iass'];

        if (!$a_is_substitution) {
            $valid[] = "orgu";
            $valid[] = "prg";
        }

        return in_array($a_obj_type, $valid);
    }

    /**
     * Save object type assignments for record
     * @param int      $a_record_id
     * @param string[] $a_obj_types
     */
    protected static function saveRecordObjTypes(int $a_record_id, array $a_obj_types) : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        foreach ($a_obj_types as $type) {
            if (!is_array($type)) {
                $type = strtolower(trim($type));
                $subtype = "-";
            } else {
                $subtype = strtolower(trim($type[1]));
                $type = strtolower(trim($type[0]));
            }

            if (self::isValidObjType($type)) {
                $fields = array(
                    "record_id" => array("integer", $a_record_id),
                    "obj_type" => array("text", $type),
                    "sub_type" => array("text", $subtype)
                );
                $ilDB->insert("adv_md_record_objs", $fields);
            }
        }
    }

    /**
     * Update record db entry
     * @param int      $a_record_id
     * @param string   $a_title
     * @param string   $a_description
     * @param bool     $a_active
     * @param string[] $a_obj_types
     * @return bool
     */
    public static function updateDBRecord(
        int $a_record_id,
        string $a_title,
        string $a_description,
        bool $a_active,
        array $a_obj_types
    ) : bool {
        global $DIC;

        $ilDB = $DIC->database();

        if (self::hasDBRecord($a_record_id)) {
            $fields = array(
                "title" => array("text", trim($a_title)),
                "description" => array("text", trim($a_description)),
                "active" => array("integer", (int) $a_active)
            );
            $ilDB->update(
                "adv_md_record",
                $fields,
                array("record_id" => array("integer", $a_record_id))
            );

            $ilDB->manipulate("DELETE FROM adv_md_record_objs" .
                " WHERE record_id = " . $ilDB->quote($a_record_id, "integer"));

            self::saveRecordObjTypes($a_record_id, $a_obj_types);

            return true;
        }
        return false;
    }

    public static function deleteDBRecord(int $a_record_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (self::hasDBRecord($a_record_id)) {
            $ilDB->manipulate("DELETE FROM adv_md_record" .
                " WHERE record_id = " . $ilDB->quote($a_record_id, "integer"));
            return true;
        }

        return false;
    }

    public static function hasDBField(int $a_field_id) : bool
    {
        global $DIC;
        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT field_id FROM adv_mdf_definition" .
            " WHERE field_id = " . $ilDB->quote($a_field_id, "integer"));
        return (bool) $ilDB->numRows($set);
    }

    /**
     * @see ilAdvancedMDFieldDefinition::getLastPosition()
     */
    protected static function getDBFieldLastPosition(int $a_record_id) : int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $sql = "SELECT max(position) pos" .
            " FROM adv_mdf_definition" .
            " WHERE record_id = " . $ilDB->quote($a_record_id, "integer");
        $set = $ilDB->query($sql);
        if ($ilDB->numRows($set)) {
            $pos = $ilDB->fetchAssoc($set);
            return (int) $pos["pos"];
        }
        return 0;
    }

    public static function createDBField(
        int $a_record_id,
        int $a_type,
        string $a_title,
        ?string $a_description = null,
        bool $a_searchable = false,
        array $a_definition = null
    ) : ?int {
        global $DIC;

        $ilDB = $DIC->database();

        if (!self::hasDBRecord($a_record_id)) {
            return null;
        }

        $field_id = $ilDB->nextId("adv_mdf_definition");

        // validating type
        $a_type = $a_type;
        if ($a_type < 1 || $a_type > 8) {
            return null;
        }

        $pos = self::getDBFieldLastPosition($a_record_id) + 1;

        $fields = array(
            "record_id" => array("integer", $a_record_id),
            "field_id" => array("integer", $field_id),
            "import_id" => array("text", "il_" . IL_INST_ID . "_adv_md_field_" . $field_id),
            "field_type" => array("integer", $a_type),
            "position" => array("integer", $pos),
            "title" => array("text", trim($a_title)),
            "description" => array("text", trim($a_description)),
            "searchable" => array("integer", (int) $a_searchable)
        );
        if ($a_definition) {
            $fields["field_values"] = array("text", serialize($a_definition));
        }
        $ilDB->insert("adv_mdf_definition", $fields);

        return $field_id;
    }

    public static function updateDBField(
        int $a_field_id,
        string $a_title,
        ?string $a_description = null,
        bool $a_searchable = false,
        ?array $a_definition = null
    ) : bool {
        global $DIC;

        $ilDB = $DIC->database();

        if (self::hasDBField($a_field_id)) {
            $fields = array(
                "field_id" => array("integer", $a_field_id),
                "title" => array("text", trim($a_title)),
                "description" => array("text", trim($a_description)),
                "searchable" => array("integer", (int) $a_searchable)
            );
            if ($a_definition) {
                $fields["field_values"] = array("text", serialize($a_definition));
            }
            $ilDB->update(
                "adv_mdf_definition",
                $fields,
                array("field_id" => array("integer", $a_field_id))
            );
            return true;
        }

        return false;
    }

    public static function deleteDBField(int $a_field_id) : bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (self::hasDBField($a_field_id)) {
            $ilDB->manipulate("DELETE FROM adv_mdf_definition" .
                " WHERE field_id = " . $ilDB->quote($a_field_id, "integer"));
            return true;
        }

        return false;
    }

    protected static function getDBSubstitution(string $a_obj_type, bool $a_include_field_data = false) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT * FROM adv_md_substitutions" .
            " WHERE obj_type = " . $ilDB->quote($a_obj_type, "text"));
        if ($ilDB->numRows($set)) {
            $res = $ilDB->fetchAssoc($set);
            $res["hide_description"] = array("integer", (bool) $res["hide_description"]);
            $res["hide_field_names"] = array("integer", (bool) $res["hide_field_names"]);

            if ($a_include_field_data) {
                $res["substitution"] = array("text", (array) unserialize($res["substitution"]));
            } else {
                unset($res["substitution"]);
            }
            unset($res["obj_type"]);

            return $res;
        }
        return [];
    }

    public static function setDBSubstitution(
        string $a_obj_type,
        bool $a_show_description,
        bool $a_show_field_names
    ) : bool {
        global $DIC;

        $ilDB = $DIC->database();

        if (self::isValidObjType($a_obj_type, true)) {
            $fields = self::getDBSubstitution($a_obj_type);

            $create = false;
            if (!$fields) {
                $create = true;
                $fields = array("obj_type" => array("text", $a_obj_type));
            }

            $fields["hide_description"] = array("integer", !$a_show_description);
            $fields["hide_field_names"] = array("integer", !$a_show_field_names);

            if ($create) {
                $ilDB->insert("adv_md_substitutions", $fields);
            } else {
                $ilDB->update(
                    "adv_md_substitutions",
                    $fields,
                    array("obj_type" => array("text", $a_obj_type))
                );
            }

            return true;
        }
        return false;
    }

    public static function hasDBFieldSubstitution(string $a_obj_type, int $a_field_id) : bool
    {
        if (self::isValidObjType($a_obj_type, true)) {
            $fields = self::getDBSubstitution($a_obj_type, true);
            $fields = $fields["substitution"][1];
            foreach ($fields as $field) {
                if ($field["field_id"] == $a_field_id) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function setDBFieldSubstitution(
        string $a_obj_type,
        int $a_field_id,
        bool $a_bold = false,
        bool $a_newline = false
    ) : bool {
        global $DIC;

        $ilDB = $DIC->database();

        if (self::isValidObjType($a_obj_type, true)) {
            $fields = self::getDBSubstitution($a_obj_type, true);
            if (!$fields) {
                self::setDBSubstitution($a_obj_type, true, true);
                $fields = array();
            } else {
                $fields = $fields["substitution"][1];
            }

            $found = false;
            foreach ($fields as $idx => $field) {
                if ($field["field_id"] == $a_field_id) {
                    $fields[$idx]["bold"] = $a_bold;
                    $fields[$idx]["newline"] = $a_newline;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $fields[] = array(
                    "field_id" => $a_field_id
                    ,
                    "bold" => $a_bold
                    ,
                    "newline" => $a_newline
                );
            }

            $fields = array("substitution" => array("text", serialize($fields)));
            $ilDB->update(
                "adv_md_substitutions",
                $fields,
                array("obj_type" => array("text", $a_obj_type))
            );
        }
        return false;
    }

    public static function removeDBFieldSubstitution(string $a_obj_type, int $a_field_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (self::isValidObjType($a_obj_type, true)) {
            $fields = self::getDBSubstitution($a_obj_type, true);
            if (!$fields) {
                return true;
            } else {
                $fields = $fields["substitution"][1];
            }

            $found = false;
            foreach ($fields as $idx => $field) {
                if ($field["field_id"] == $a_field_id) {
                    unset($fields[$idx]);
                    $found = true;
                    break;
                }
            }
            if ($found) {
                $fields = array("substitution" => array("text", serialize($fields)));
                $ilDB->update(
                    "adv_md_substitutions",
                    $fields,
                    array("obj_type" => array("text", $a_obj_type))
                );
            }
            return true;
        }
        return false;
    }
}
