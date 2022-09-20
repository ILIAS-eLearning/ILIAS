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
 * Class ilUserDefinedData
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilUserDefinedData
{
    public ?ilDBInterface $db = null;
    public array $user_data = array(); // Missing array type.
    public ?int $usr_id = null;

    public function __construct(int $a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->db = $ilDB;
        $this->usr_id = $a_usr_id;

        $this->__read();
    }

    /**
     * Lookup data
     */
    public static function lookupData(array $a_user_ids, array $a_field_ids): array // Missing array type.
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM udf_text " .
            "WHERE " . $ilDB->in('usr_id', $a_user_ids, false, 'integer') . ' ' .
            'AND ' . $ilDB->in('field_id', $a_field_ids, false, 'integer');
        $res = $ilDB->query($query);

        $udfd = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $udfd[$row['usr_id']][$row['field_id']] = $row['value'];
        }

        $def_helper = ilCustomUserFieldsHelper::getInstance();
        foreach ($def_helper->getActivePlugins() as $plugin) {
            foreach ($plugin->lookupUserData($a_user_ids, $a_field_ids) as $user_id => $usr_data) {
                foreach ($usr_data as $field_id => $value) {
                    $udfd[$user_id][$field_id] = $value;
                }
            }
        }

        return $udfd;
    }

    public function getUserId(): int
    {
        return $this->usr_id;
    }

    public function set(string $a_field, string $a_value): void
    {
        $this->user_data[$a_field] = $a_value;
    }

    public function get(string $a_field): string
    {
        return $this->user_data[$a_field] ?? '';
    }

    public function getAll(): array // Missing array type.
    {
        return $this->user_data;
    }

    public function update(): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $udf_obj = ilUserDefinedFields::_getInstance();

        foreach ($udf_obj->getDefinitions() as $definition) {
            if ($definition["field_type"] == UDF_TYPE_WYSIWYG) {
                $ilDB->replace(
                    "udf_clob",
                    array(
                        "usr_id" => array("integer", $this->getUserId()),
                        "field_id" => array("integer", $definition['field_id'])),
                    array(
                        "value" => array("clob", $this->get("f_" . $definition['field_id']))
                        )
                );
            } else {
                $ilDB->replace(
                    "udf_text",
                    array(
                        "usr_id" => array("integer", $this->getUserId()),
                        "field_id" => array("integer", $definition['field_id'])),
                    array(
                        "value" => array("text", $this->get("f_" . $definition['field_id']))
                        )
                );
            }
        }
    }

    public static function deleteEntriesOfUser(int $a_user_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->manipulate(
            "DELETE FROM udf_text WHERE "
            . " usr_id = " . $ilDB->quote($a_user_id, "integer")
        );
        $ilDB->manipulate(
            "DELETE FROM udf_clob WHERE "
            . " usr_id = " . $ilDB->quote($a_user_id, "integer")
        );
    }

    /**
     * Delete data of particular field
     */
    public static function deleteEntriesOfField(int $a_field_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->manipulate(
            "DELETE FROM udf_text WHERE "
            . " field_id = " . $ilDB->quote($a_field_id, "integer")
        );
        $ilDB->manipulate(
            "DELETE FROM udf_clob WHERE "
            . " field_id = " . $ilDB->quote($a_field_id, "integer")
        );
    }

    /**
     * Delete data of particular value of a (selection) field
     */
    public static function deleteFieldValue(
        int $a_field_id,
        string $a_value
    ): void {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->manipulate(
            "UPDATE udf_text SET value = " . $ilDB->quote("", "text") . " WHERE "
            . " field_id = " . $ilDB->quote($a_field_id, "integer")
            . " AND value = " . $ilDB->quote($a_value, "text")
        );
    }

    public function toXML(): string
    {
        $xml_writer = new ilXmlWriter();

        $this->addToXML($xml_writer);

        return $xml_writer->xmlDumpMem(false);
    }

    /**
     * add user defined field data to xml (using usr dtd)
     */
    public function addToXML(ilXmlWriter $xml_writer): void
    {
        $udf_obj = ilUserDefinedFields::_getInstance();

        foreach ($udf_obj->getDefinitions() as $definition) {
            if ($definition["export"] != false) {
                $xml_writer->xmlElement(
                    'UserDefinedField',
                    array('Id' => $definition['il_id'],
                                          'Name' => $definition['field_name']),
                    (string) $this->user_data['f_' . (int) $definition['field_id']]
                );
            }
        }
    }

    // Private
    public function __read(): void
    {
        $this->user_data = array();
        $query = "SELECT * FROM udf_text " .
            "WHERE usr_id = " . $this->db->quote($this->usr_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $this->user_data["f_" . $row["field_id"]] = $row["value"];
        }
        $query = "SELECT * FROM udf_clob " .
            "WHERE usr_id = " . $this->db->quote($this->usr_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $this->user_data["f_" . $row["field_id"]] = $row["value"];
        }
    }
}
