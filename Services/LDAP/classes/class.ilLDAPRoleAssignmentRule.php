<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilLDAPRoleAssignmentRule
{
    public const TYPE_GROUP = 1;
    public const TYPE_ATTRIBUTE = 2;
    public const TYPE_PLUGIN = 3;
    
    private static array $instances = [];
    
    private ilLogger $logger;
    private ilDBInterface $db;
    private ilErrorHandling $ilErr;
    private ilLanguage $lng;

    private int $rule_id;

    private int $server_id = 0;
    private bool$add_on_update = false;
    private bool$remove_on_update = false;
    private int $plugin_id = 0;
    private string $attribute_value = '';
    private string $attribute_name = '';
    private bool $member_is_dn = false;
    private string $member_attribute = '';
    private string $dn = '';
    private int $type = 0;
    private int $role_id = 0;

    private function __construct(int $a_rule_id = 0)
    {
        global $DIC;
        $this->db = $DIC->database();
        $this->logger = $DIC->logger()->auth();
        $this->ilErr = $DIC['ilErr'];
        $this->lng = $DIC->language();

        $this->rule_id = $a_rule_id;
        $this->read();
    }
    
    public static function _getInstanceByRuleId(int $a_rule_id) : ilLDAPRoleAssignmentRule
    {
        return self::$instances[$a_rule_id] ?? (self::$instances[$a_rule_id] = new ilLDAPRoleAssignmentRule($a_rule_id));
    }
    
    /**
     * Check if there any rule for updates
     */
    public static function hasRulesForUpdate() : bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT COUNT(*) num FROM ldap_role_assignments ' .
            'WHERE add_on_update = 1 ' .
            'OR remove_on_update = 1 ';
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

        return $row->num > 0;
    }
    
    /**
     * Check if a rule matches
     */
    public function matches(array $a_user_data) : bool
    {
        switch ($this->getType()) {
            case self::TYPE_PLUGIN:
                return ilLDAPRoleAssignmentRules::callPlugin($this->getPluginId(), $a_user_data);
                
            case self::TYPE_ATTRIBUTE:
                
                $attn = strtolower($this->getAttributeName());
                
                if (!isset($a_user_data[$attn])) {
                    return false;
                }

                if (!is_array($a_user_data[$attn])) {
                    $attribute_val = array(0 => $a_user_data[$attn]);
                } else {
                    $attribute_val = $a_user_data[$attn];
                }
                
                foreach ($attribute_val as $value) {
                    if ($this->wildcardCompare(trim($this->getAttributeValue()), trim($value))) {
                        $this->logger->debug(': Found role mapping: ' . ilObject::_lookupTitle($this->getRoleId()));
                        return true;
                    }
                }
                return false;

            case self::TYPE_GROUP:
                return $this->isGroupMember($a_user_data);
                
        }

        return false;
    }
    
    protected function wildcardCompare(string $a_str1, string $a_str2) : bool
    {
        $pattern = str_replace('*', '.*?', $a_str1);
        $this->logger->debug(': Replace pattern:' . $pattern . ' => ' . $a_str2);
        return preg_match('/^' . $pattern . '$/i', $a_str2) === 1;
    }
    
    /**
     * Check if user is member of specific group
     *
     * @param array $a_user_data user_data
     *
     */
    private function isGroupMember(array $a_user_data) : bool
    {
        $server = ilLDAPServer::getInstanceByServerId($this->getServerId());

        if ($this->isMemberAttributeDN()) {
            if ($server->enabledEscapeDN()) {
                $user_cmp = ldap_escape($a_user_data['dn'], "", LDAP_ESCAPE_FILTER);
            } else {
                $user_cmp = $a_user_data['dn'];
            }
        } else {
            $user_cmp = $a_user_data['ilExternalAccount'];
        }
        
        try {
            $query = new ilLDAPQuery($server);
            $query->bind();
            $res = $query->query(
                $this->getDN(),
                sprintf(
                    '(%s=%s)',
                    $this->getMemberAttribute(),
                    $user_cmp
                ),
                ilLDAPServer::LDAP_SCOPE_BASE,
                array('dn')
            );
            return (bool) $res->numRows();
        } catch (ilLDAPQueryException $e) {
            $this->logger->warning(': Caught Exception: ' . $e->getMessage());
            return false;
        }
    }
    
    
    
    /**
     * Get all rules
     *
     * @return ilLDAPRoleAssignmentRule[]
     */
    public static function _getRules($a_server_id) : array
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $rules = [];

        $query = "SELECT rule_id FROM ldap_role_assignments " .
                "WHERE server_id = " . $ilDB->quote($a_server_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $rules[] = self::_getInstanceByRuleId((int) $row->rule_id);
        }

        return $rules;
    }
    
    /**
     * set role id
     *
     * @param int $a_role_id role id of global role
     */
    public function setRoleId(int $a_role_id) : void
    {
        $this->role_id = $a_role_id;
    }
    
    /**
     * get role id
     */
    public function getRoleId() : int
    {
        return $this->role_id;
    }
    
    /**
     * get id
     */
    public function getRuleId() : int
    {
        return $this->rule_id;
    }
    
    /**
     * set server id
     */
    public function setServerId(int $a_id) : void
    {
        $this->server_id = $a_id;
    }
    
    /**
     * get server id
     */
    public function getServerId() : int
    {
        return $this->server_id;
    }
    
    /**
     * set type
     */
    public function setType(int $a_type) : void
    {
        $this->type = $a_type;
    }
    
    /**
     * getType
     */
    public function getType() : int
    {
        return $this->type;
    }
    
    /**
     * set dn
     */
    public function setDN(string $a_dn) : void
    {
        $this->dn = $a_dn;
    }
    
    /**
     * get dn
     */
    public function getDN() : string
    {
        return $this->dn;
    }
    
    public function setMemberAttribute(string $a_attribute) : void
    {
        $this->member_attribute = $a_attribute;
    }
    
    /**
     * get attribute
     */
    public function getMemberAttribute() : string
    {
        return $this->member_attribute;
    }
    
    /**
     * set member attribute is dn
     */
    public function setMemberIsDN(bool $a_status) : void
    {
        $this->member_is_dn = $a_status;
    }
    
    /**
     * is member attribute dn
     */
    public function isMemberAttributeDN() : bool
    {
        return $this->member_is_dn;
    }
    
    /**
     * set attribute name
     */
    public function setAttributeName(string $a_name) : void
    {
        $this->attribute_name = $a_name;
    }
    
    /**
     * get attribute name
     */
    public function getAttributeName() : string
    {
        return $this->attribute_name;
    }
    
    /**
     * set attribute value
     */
    public function setAttributeValue(string $a_value) : void
    {
        $this->attribute_value = $a_value;
    }
    
    /**
     * get atrtibute value
     */
    public function getAttributeValue() : string
    {
        return $this->attribute_value;
    }
    
    public function enableAddOnUpdate(bool $a_status) : void
    {
        $this->add_on_update = $a_status;
    }
    
    public function isAddOnUpdateEnabled() : bool
    {
        return $this->add_on_update;
    }
    
    public function enableRemoveOnUpdate(bool $a_status) : void
    {
        $this->remove_on_update = $a_status;
    }
    
    public function isRemoveOnUpdateEnabled() : bool
    {
        return $this->remove_on_update;
    }
    
    public function setPluginId(int $a_id) : void
    {
        $this->plugin_id = $a_id;
    }
    
    public function getPluginId() : int
    {
        return $this->plugin_id;
    }
    
    public function isPluginActive() : bool
    {
        return $this->getType() === self::TYPE_PLUGIN;
    }

    public function conditionToString() : string
    {
        switch ($this->getType()) {
            case self::TYPE_PLUGIN:
                return $this->lng->txt('ldap_plugin_id') . ': ' . $this->getPluginId();
            
            case self::TYPE_GROUP:
                $dn_arr = explode(',', $this->getDN());
                return $dn_arr[0];
                
            case self::TYPE_ATTRIBUTE:
                return $this->getAttributeName() . '=' . $this->getAttributeValue();
                
            default:
                throw new RuntimeException(sprintf('Unknown type: %s', var_export($this->getType(), true)));
        }
    }

    public function create() : bool
    {
        $next_id = $this->db->nextId('ldap_role_assignments');

        $query = "INSERT INTO ldap_role_assignments (server_id,rule_id,type,dn,attribute,isdn,att_name,att_value,role_id, " .
            "add_on_update, remove_on_update, plugin_id ) " .
            "VALUES( " .
            $this->db->quote($this->getServerId(), 'integer') . ", " .
            $this->db->quote($next_id, 'integer') . ", " .
            $this->db->quote($this->getType(), 'integer') . ", " .
            $this->db->quote($this->getDN(), 'text') . ", " .
            $this->db->quote($this->getMemberAttribute(), 'text') . ", " .
            $this->db->quote($this->isMemberAttributeDN(), 'integer') . ", " .
            $this->db->quote($this->getAttributeName(), 'text') . ", " .
            $this->db->quote($this->getAttributeValue(), 'text') . ", " .
            $this->db->quote($this->getRoleId(), 'integer') . ", " .
            $this->db->quote($this->isAddOnUpdateEnabled(), 'integer') . ', ' .
            $this->db->quote($this->isRemoveOnUpdateEnabled(), 'integer') . ', ' .
            $this->db->quote($this->getPluginId(), 'integer') . ' ' .
            ")";
        $this->db->manipulate($query);
        $this->rule_id = $next_id;
                
        return true;
    }

    public function update() : bool
    {
        $query = "UPDATE ldap_role_assignments " .
            "SET server_id = " . $this->db->quote($this->getServerId(), 'integer') . ", " .
            "type = " . $this->db->quote($this->getType(), 'integer') . ", " .
            "dn = " . $this->db->quote($this->getDN(), 'text') . ", " .
            "attribute = " . $this->db->quote($this->getMemberAttribute(), 'text') . ", " .
            "isdn = " . $this->db->quote($this->isMemberAttributeDN(), 'integer') . ", " .
            "att_name = " . $this->db->quote($this->getAttributeName(), 'text') . ", " .
            "att_value = " . $this->db->quote($this->getAttributeValue(), 'text') . ", " .
            "role_id = " . $this->db->quote($this->getRoleId(), 'integer') . ", " .
            "add_on_update = " . $this->db->quote($this->isAddOnUpdateEnabled(), 'integer') . ', ' .
            'remove_on_update = ' . $this->db->quote($this->isRemoveOnUpdateEnabled(), 'integer') . ', ' .
            'plugin_id = ' . $this->db->quote($this->getPluginId(), 'integer') . ' ' .
            "WHERE rule_id = " . $this->db->quote($this->getRuleId(), 'integer') . " ";
        $this->db->manipulate($query);

        return true;
    }

    public function validate() : bool
    {
        $this->ilErr->setMessage('');
        
        if (!$this->getRoleId()) {
            $this->ilErr->setMessage('fill_out_all_required_fields');
            return false;
        }
        switch ($this->getType()) {
            case self::TYPE_GROUP:
                if ($this->getDN() === '' || $this->getMemberAttribute() === '') {
                    $this->ilErr->setMessage('fill_out_all_required_fields');
                    return false;
                }
                break;
            case self::TYPE_ATTRIBUTE:
                if ($this->getAttributeName() === '' || $this->getAttributeValue() === '') {
                    $this->ilErr->setMessage('fill_out_all_required_fields');
                    return false;
                }
                break;
                
            case self::TYPE_PLUGIN:
                if (!$this->getPluginId()) {
                    $this->ilErr->setMessage('ldap_err_missing_plugin_id');
                    return false;
                }
                break;
                
            default:
                $this->ilErr->setMessage('ldap_no_type_given');
                return false;
        }

        return true;
    }
        
    public function delete() : bool
    {
        $query = "DELETE FROM ldap_role_assignments " .
            "WHERE rule_id = " . $this->db->quote($this->getRuleId(), 'integer') . " ";
        $this->db->manipulate($query);

        return true;
    }

    private function read() : void
    {
        $query = "SELECT * FROM ldap_role_assignments " .
            "WHERE rule_id = " . $this->db->quote($this->getRuleId(), 'integer') . " ";
        
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setServerId((int) $row->server_id);
            $this->setType((int) $row->type);
            $this->setDN($row->dn);
            $this->setMemberAttribute($row->attribute);
            $this->setMemberIsDN((bool) $row->isdn);
            $this->setAttributeName($row->att_name);
            $this->setAttributeValue($row->att_value);
            $this->setRoleId((int) $row->role_id);
            $this->enableAddOnUpdate((bool) $row->add_on_update);
            $this->enableRemoveOnUpdate((bool) $row->remove_on_update);
            $this->setPluginId((int) $row->plugin_id);
        }
    }
}
