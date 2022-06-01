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
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilLDAPRoleGroupMappingSettings
{
    private static array $instances = [];

    private ilDBInterface $db;
    private ilLanguage $lng;
    private ilRbacReview $rbacreview;
    private ilErrorHandling $ilErr;
    private ilObjectDataCache $ilObjDataCache;

    private int $server_id;
    private array $mappings = [];

    public const MAPPING_INFO_ALL = 1;
    public const MAPPING_INFO_INFO_ONLY = 0;
    
    /**
     * Private constructor (Singleton for each server_id)
     */
    private function __construct($a_server_id)
    {
        global $DIC;
        
        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->rbacreview = $DIC->rbac()->review();
        $this->ilErr = $DIC['ilErr'];
        $this->ilObjDataCache = $DIC['ilObjDataCache'];
        $this->server_id = $a_server_id;
        $this->read();
    }
    
    /**
     * Get instance of class
     */
    public static function _getInstanceByServerId(int $a_server_id) : ilLDAPRoleGroupMappingSettings
    {
        if (array_key_exists($a_server_id, self::$instances) && is_object(self::$instances[$a_server_id])) {
            return self::$instances[$a_server_id];
        }
        return self::$instances[$a_server_id] = new ilLDAPRoleGroupMappingSettings($a_server_id);
    }
    
    public static function _deleteByRole(int $a_role_id) : bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM ldap_rg_mapping " .
            "WHERE role = " . $ilDB->quote($a_role_id, 'integer');
        $ilDB->manipulate($query);
        
        return true;
    }
    
    public static function _deleteByServerId(int $a_server_id) : bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM ldap_rg_mapping " .
            "WHERE server_id = " . $ilDB->quote($a_server_id, 'integer');
        $ilDB->manipulate($query);

        return true;
    }
    
    public static function _getAllActiveMappings() : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $rbacreview = $DIC['rbacreview'];
        
        $query = "SELECT rgm.* FROM ldap_rg_mapping rgm JOIN ldap_server_settings lss " .
            "ON rgm.server_id = lss.server_id " .
            "WHERE lss.active = 1 " .
            "AND lss.role_sync_active = 1 ";
        $res = $ilDB->query($query);
        $active = [];
        while ($row = $ilDB->fetchObject($res)) {
            $data['server_id'] = $row->server_id;
            $data['url'] = $row->url;
            $data['mapping_id'] = $row->mapping_id;
            $data['dn'] = $row->dn;
            $data['member'] = $row->member_attribute;
            $data['isdn'] = $row->member_isdn;
            $data['info'] = $row->mapping_info;
            $data['info_type'] = $row->mapping_info_type;
            // read assigned object
            $data['object_id'] = $rbacreview->getObjectOfRole($row->role);
            
            $active[$row->role][] = $data;
        }
        return $active;
    }
    
    public function getServerId() : int
    {
        return $this->server_id;
    }
    
    /**
     * Get already configured mappings
     */
    public function getMappings() : array
    {
        return $this->mappings;
    }
    
    public function loadFromPost(array $a_mappings) : void
    {
        if (!$a_mappings) {
            return;
        }
        
        $this->mappings = [];
        foreach ($a_mappings as $mapping_id => $data) {
            if ($mapping_id === 0 && !$data['dn'] && !$data['member'] && !$data['memberisdn'] && !$data['role']) {
                continue;
            }
            $this->mappings[$mapping_id]['dn'] = ilUtil::stripSlashes($data['dn']);
            $this->mappings[$mapping_id]['url'] = ilUtil::stripSlashes($data['url']);
            $this->mappings[$mapping_id]['member_attribute'] = ilUtil::stripSlashes($data['member']);
            $this->mappings[$mapping_id]['member_isdn'] = ilUtil::stripSlashes($data['memberisdn']);
            $this->mappings[$mapping_id]['role_name'] = ilUtil::stripSlashes($data['role']);
            $this->mappings[$mapping_id]['role'] = $this->rbacreview->roleExists(ilUtil::stripSlashes($data['role']));
            $this->mappings[$mapping_id]['info'] = ilUtil::stripSlashes($data['info']);
            $this->mappings[$mapping_id]['info_type'] = ilUtil::stripSlashes($data['info_type']);
        }
    }
    
    /**
     * Validate mappings
     *
     * @access public
     *
     */
    public function validate() : bool
    {
        $this->ilErr->setMessage('');
        $found_missing = false;
        foreach ($this->mappings as $data) {
            // Check if all required fields are available
            if ($data['dn'] === '' || $data['member_attribute'] === '' || $data['role_name'] === '') {
                if (!$found_missing) {
                    $found_missing = true;
                    $this->ilErr->appendMessage($this->lng->txt('fill_out_all_required_fields'));
                }
            }
            // Check role valid
            if ($data['role_name'] !== '' && !$this->rbacreview->roleExists($data['role_name'])) {
                $this->ilErr->appendMessage($this->lng->txt('ldap_role_not_exists') . ' ' . $data['role_name']);
            }
        }

        return $this->ilErr->getMessage() === '';
    }
    
    /**
     * Save mappings
     *
     * @access public
     * @param
     *
     */
    public function save() : void
    {
        foreach ($this->mappings as $mapping_id => $data) {
            if (!$mapping_id) {
                $next_id = $this->db->nextId('ldap_rg_mapping');
                $query = "INSERT INTO ldap_rg_mapping (mapping_id,server_id,url,dn,member_attribute,member_isdn,role,mapping_info,mapping_info_type) " .
                    "VALUES ( " .
                    $this->db->quote($next_id, 'integer') . ", " .
                    $this->db->quote($this->getServerId(), 'integer') . ", " .
                    $this->db->quote($data['url'], 'text') . ", " .
                    $this->db->quote($data['dn'], 'text') . ", " .
                    $this->db->quote($data['member_attribute'], 'text') . ", " .
                    $this->db->quote($data['member_isdn'], 'integer') . ", " .
                    $this->db->quote($data['role'], 'integer') . ", " .
                    $this->db->quote($data['info'], 'text') . ", " .
                    $this->db->quote($data['info_type'], 'integer') .
                    ")";
            } else {
                $query = "UPDATE ldap_rg_mapping " .
                    "SET server_id = " . $this->db->quote($this->getServerId(), 'integer') . ", " .
                    "url = " . $this->db->quote($data['url'], 'text') . ", " .
                    "dn =" . $this->db->quote($data['dn'], 'text') . ", " .
                    "member_attribute = " . $this->db->quote($data['member_attribute'], 'text') . ", " .
                    "member_isdn = " . $this->db->quote($data['member_isdn'], 'integer') . ", " .
                    "role = " . $this->db->quote($data['role'], 'integer') . ", " .
                    "mapping_info = " . $this->db->quote($data['info'], 'text') . ", " .
                    "mapping_info_type = " . $this->db->quote($data['info_type'], 'integer') . " " .
                    "WHERE mapping_id = " . $this->db->quote($mapping_id, 'integer');
            }
            $this->db->manipulate($query);
        }
        $this->read();
    }
    
    
    /**
     * Delete a mapping

     * @param int mapping_id
     *
     */
    public function delete($a_mapping_id) : void
    {
        $query = "DELETE FROM ldap_rg_mapping " .
            "WHERE server_id = " . $this->db->quote($this->getServerId(), 'integer') . " " .
            "AND mapping_id = " . $this->db->quote($a_mapping_id, 'integer');
        $this->db->manipulate($query);
        $this->read();
    }
    
    
    /**
     * Create an info string for a role group mapping
     *
     * @param int $a_mapping_id mapping_id
     */
    //TODO check if method gets called somewhere
    public function getMappingInfoString(int $a_mapping_id) : string
    {
        $dn_parts = explode(',', $this->mappings[$a_mapping_id]['dn']);
        
        return $dn_parts ? $dn_parts[0] : "''";
    }
    
    
    /**
     * Read mappings
     */
    private function read() : void
    {
        $this->mappings = array();
        $query = "SELECT * FROM ldap_rg_mapping LEFT JOIN object_data " .
            "ON role = obj_id " .
            "WHERE server_id =" . $this->db->quote($this->getServerId(), 'integer') . ' ' .
            "ORDER BY title,dn";
            
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->mappings[$row->mapping_id]['mapping_id'] = $row->mapping_id;
            $this->mappings[$row->mapping_id]['dn'] = $row->dn;
            $this->mappings[$row->mapping_id]['url'] = $row->url;
            $this->mappings[$row->mapping_id]['member_attribute'] = $row->member_attribute;
            $this->mappings[$row->mapping_id]['member_isdn'] = $row->member_isdn;
            $this->mappings[$row->mapping_id]['role'] = $row->role;
            $this->mappings[$row->mapping_id]['info'] = $row->mapping_info;
            $this->mappings[$row->mapping_id]['info_type'] = $row->mapping_info_type;
            if ($this->ilObjDataCache->lookupType((int) $row->role) === 'role') {
                $this->mappings[$row->mapping_id]['role_name'] = $this->ilObjDataCache->lookupTitle((int) $row->role);
            } else {
                $this->mappings[$row->mapping_id]['role_name'] = $row->role;
            }
        }
    }
}
