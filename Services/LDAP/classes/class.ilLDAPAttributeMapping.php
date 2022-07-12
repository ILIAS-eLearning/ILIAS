<?php declare(strict_types=1);

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
 * This class stores the settings that define the mapping between LDAP attribute and user profile fields.
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilLDAPAttributeMapping
{
    private static array $instances = [];
    private int $server_id;
    private ilDBInterface $db;

    private array $mapping_rules = [];
    private array $rules_for_update = [];

    private function __construct(int $a_server_id)
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->server_id = $a_server_id;
        $this->read();
    }

    public static function _getInstanceByServerId(int $a_server_id) : ilLDAPAttributeMapping
    {
        if (array_key_exists($a_server_id, self::$instances) && is_object(self::$instances[$a_server_id])) {
            return self::$instances[$a_server_id];
        }

        return self::$instances[$a_server_id] = new ilLDAPAttributeMapping($a_server_id);
    }

    public static function _delete(int $a_server_id) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM ldap_attribute_mapping " .
            "WHERE server_id =" . $ilDB->quote($a_server_id, 'integer');
        $ilDB->manipulate($query);
    }

    public static function _lookupGlobalRole(int $a_server_id) : int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT value FROM ldap_attribute_mapping " .
            "WHERE server_id =" . $ilDB->quote($a_server_id, 'integer') . " " .
            "AND keyword = " . $ilDB->quote('global_role', 'text');

        $res = $ilDB->query($query);
        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->value;
        }

        return 0;
    }

    /**
     * Check if there is ldap attribute -> user data mapping which
     * which is updated on login
     */
    public static function hasRulesForUpdate(int $a_server_id) : bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT perform_update FROM ldap_attribute_mapping ' .
            'WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer') . ' ' .
            'AND perform_update = 1';
        $res = $ilDB->query($query);
        return $res->numRows() > 0;
    }

    public function setRule(string $a_field_name, string $a_ldap_attribute, bool $a_perform_update) : void
    {
        $this->mapping_rules[$a_field_name]['value'] = $a_ldap_attribute;
        $this->mapping_rules[$a_field_name]['performUpdate'] = $a_perform_update;
    }

    /**
     * Get all mapping rules with option 'update'
     * @return array mapping rules. E.g. array('firstname' => 'name',...)
     */
    public function getRulesForUpdate() : array
    {
        return $this->rules_for_update;
    }

    /**
     * Get field names of all mapping rules with option 'update'
     *
     */
    public function getFieldsForUpdate() : array
    {
        return self::getMappedFields($this->rules_for_update);
    }

    /**
     * Get all mapping fields
     */
    public function getFields() : array
    {
        return self::getMappedFields($this->mapping_rules);
    }

    private static function getMappedFields(array $rules) : array
    {
        $fields = [];
        foreach ($rules as $rule) {
            if (!$rule['value']) {
                continue;
            }
            if (strpos($rule['value'], ',') === false) {
                $fields[] = strtolower($rule['value']);
                continue;
            }
            $tmp_fields = explode(',', $rule['value']);
            foreach ($tmp_fields as $tmp_field) {
                $fields[] = strtolower(trim($tmp_field));
            }
        }
        return $fields;
    }

    /**
     * Get all rules
     *
     * @return array mapping rules. E.g. array('firstname' => 'name',...)
     */
    public function getRules(bool $onlyApplicable = false) : array
    {
        if (!$onlyApplicable) {
            return $this->mapping_rules;
        }
        return array_filter($this->mapping_rules, static function (array $rule) : bool {
            return $rule['value'] !== '';
        });
    }

    /**
     * Clear rules => Does not perform an update
     */
    public function clearRules() : void
    {
        $this->mapping_rules = array();
    }

    /**
     * Save mapping rules to db
     */
    public function save() : void
    {
        $this->delete();

        foreach ($this->mapping_rules as $keyword => $options) {
            $query = "INSERT INTO ldap_attribute_mapping (server_id,keyword,value,perform_update) " .
                "VALUES( " .
                $this->db->quote($this->server_id, 'integer') . ", " .
                $this->db->quote($keyword, 'text') . ", " .
                $this->db->quote($options['value'], 'text') . ", " .
                $this->db->quote($options['performUpdate'], 'integer') .
                ')';
            $this->db->manipulate($query);
        }
    }

    /**
     * Delete all entries
     */
    public function delete() : void
    {
        self::_delete($this->server_id);
    }

    /**
     * Check whether an update should be performed on a specific user attribute or not
     * @param string ILIAS user attribute
     *
     */
    public function enabledUpdate(string $a_field_name) : bool
    {
        if (array_key_exists($a_field_name, $this->mapping_rules)) {
            return (bool) $this->mapping_rules[$a_field_name]['performUpdate'];
        }

        return false;
    }

    /**
     * Get LDAP attribute name by given ILIAS profile field
     *
     * @param string ILIAS user attribute
     * @return string LDAP attribute name
     */
    public function getValue(string $a_field_name) : string
    {
        if (array_key_exists($a_field_name, $this->mapping_rules)) {
            return $this->mapping_rules[$a_field_name]['value'];
        }

        return '';
    }

    /**
     * Read mapping settings from db
     */
    private function read() : void
    {
        $query = "SELECT * FROM ldap_attribute_mapping " .
            "WHERE server_id =" . $this->db->quote($this->server_id, 'integer');

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->mapping_rules[$row->keyword]['value'] = $row->value;
            $this->mapping_rules[$row->keyword]['performUpdate'] = (bool) $row->perform_update;

            if ($row->perform_update) {
                $this->rules_for_update[$row->keyword]['value'] = $row->value;
            }
        }
    }
}
