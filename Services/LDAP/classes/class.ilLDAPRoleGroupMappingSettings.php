<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesLDAP
*/

class ilLDAPRoleGroupMappingSettings
{
    private static $instances = array();
    private $server_id = null;
    private $db = null;
    private $mappings = null;
    
    const MAPPING_INFO_ALL = 1;
    const MAPPING_INFO_INFO_ONLY = 0;
    
    /**
     * Private constructor (Singleton for each server_id)
     *
     * @access private
     *
     */
    private function __construct($a_server_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        
        $this->db = $ilDB;
        $this->lng = $lng;
        $this->server_id = $a_server_id;
        $this->read();
    }
    
    /**
     * Get instance of class
     *
     * @access public
     * @param int server_id
     * @return ilLDAPRoleGroupMappingSettings
     */
    public static function _getInstanceByServerId($a_server_id)
    {
        if (array_key_exists($a_server_id, self::$instances) and is_object(self::$instances[$a_server_id])) {
            return self::$instances[$a_server_id];
        }
        return self::$instances[$a_server_id] = new ilLDAPRoleGroupMappingSettings($a_server_id);
    }
    
    public static function _deleteByRole($a_role_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM ldap_rg_mapping " .
            "WHERE role = " . $ilDB->quote($a_role_id, 'integer');
        $res = $ilDB->manipulate($query);
        
        return true;
    }
    
    public static function _deleteByServerId($a_server_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM ldap_rg_mapping " .
            "WHERE server_id = " . $ilDB->quote($a_server_id, 'integer');
        $res = $ilDB->manipulate($query);

        return true;
    }
    
    public static function _getAllActiveMappings()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $rbacreview = $DIC['rbacreview'];
        
        $query = "SELECT rgm.* FROM ldap_rg_mapping rgm JOIN ldap_server_settings lss " .
            "ON rgm.server_id = lss.server_id " .
            "WHERE lss.active = 1 " .
            "AND lss.role_sync_active = 1 ";
        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchObject($res)) {
            $data['server_id']		= $row->server_id;
            $data['url']			= $row->url;
            $data['mapping_id']		= $row->mapping_id;
            $data['dn']				= $row->dn;
            $data['member']			= $row->member_attribute;
            $data['isdn']			= $row->member_isdn;
            $data['info']			= $row->mapping_info;
            $data['info_type']		= $row->mapping_info_type;
            // read assigned object
            $data['object_id'] 		= $rbacreview->getObjectOfRole($row->role);
            
            
            $active[$row->role][] = $data;
        }
        return $active ? $active : array();
    }
    
    public function getServerId()
    {
        return $this->server_id;
    }
    
    /**
     * Get already configured mappings
     *
     * @access public
     *
     */
    public function getMappings()
    {
        return $this->mappings ? $this->mappings : array();
    }
    
    public function loadFromPost($a_mappings)
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        
        if (!$a_mappings) {
            return;
        }
        
        $this->mappings = array();
        foreach ($a_mappings as $mapping_id => $data) {
            if ($mapping_id == 0) {
                if (!$data['dn'] and !$data['member'] and !$data['memberisdn'] and !$data['role']) {
                    continue;
                }
            }
            $this->mappings[$mapping_id]['dn'] = ilUtil::stripSlashes($data['dn']);
            $this->mappings[$mapping_id]['url'] = ilUtil::stripSlashes($data['url']);
            $this->mappings[$mapping_id]['member_attribute'] = ilUtil::stripSlashes($data['member']);
            $this->mappings[$mapping_id]['member_isdn'] = ilUtil::stripSlashes($data['memberisdn']);
            $this->mappings[$mapping_id]['role_name'] = ilUtil::stripSlashes($data['role']);
            $this->mappings[$mapping_id]['role'] = $rbacreview->roleExists(ilUtil::stripSlashes($data['role']));
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
    public function validate()
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $rbacreview = $DIC['rbacreview'];
        
        $ilErr->setMessage('');
        $found_missing = false;
        foreach ($this->mappings as $mapping_id => $data) {
            // Check if all required fields are available
            if (!strlen($data['dn']) || !strlen($data['member_attribute']) || !strlen($data['role_name'])) {
                if (!$found_missing) {
                    $found_missing = true;
                    $ilErr->appendMessage($this->lng->txt('fill_out_all_required_fields'));
                }
            }
            // Check role valid
            if (strlen($data['role_name']) and !$rbacreview->roleExists($data['role_name'])) {
                $ilErr->appendMessage($this->lng->txt('ldap_role_not_exists') . ' ' . $data['role_name']);
            }
        }
        return strlen($ilErr->getMessage()) ? false : true;
    }
    
    /**
     * Save mappings
     *
     * @access public
     * @param
     *
     */
    public function save()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        foreach ($this->mappings as $mapping_id => $data) {
            if (!$mapping_id) {
                $next_id = $ilDB->nextId('ldap_rg_mapping');
                $query = "INSERT INTO ldap_rg_mapping (mapping_id,server_id,url,dn,member_attribute,member_isdn,role,mapping_info,mapping_info_type) " .
                    "VALUES ( " .
                    $ilDB->quote($next_id, 'integer') . ", " .
                    $this->db->quote($this->getServerId(), 'integer') . ", " .
                    $this->db->quote($data['url'], 'text') . ", " .
                    $this->db->quote($data['dn'], 'text') . ", " .
                    $this->db->quote($data['member_attribute'], 'text') . ", " .
                    $this->db->quote($data['member_isdn'], 'integer') . ", " .
                    $this->db->quote($data['role'], 'integer') . ", " .
                    $this->db->quote($data['info'], 'text') . ", " .
                    $this->db->quote($data['info_type'], 'integer') .
                    ")";
                $res = $ilDB->manipulate($query);
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
                $res = $ilDB->manipulate($query);
            }
        }
        $this->read();
    }
    
    
    /**
     * Delete a mapping
     *
     * @access public
     * @param int mapping_id
     *
     */
    public function delete($a_mapping_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM ldap_rg_mapping " .
            "WHERE server_id = " . $this->db->quote($this->getServerId(), 'integer') . " " .
            "AND mapping_id = " . $this->db->quote($a_mapping_id, 'integer');
        $res = $ilDB->manipulate($query);
        $this->read();
    }
    
    
    /**
     * Create an info string for a role group mapping
     *
     * @access public
     * @param int mapping_id
     */
    public function getMappingInfoString($a_mapping_id)
    {
        $role = $this->mappings[$a_mapping_id]['role_name'];
        $dn_parts = explode(',', $this->mappings[$a_mapping_id]['dn']);
        
        return (array_key_exists(0, $dn_parts) ? $dn_parts[0] : "''");
    }
    
    
    /**
     * Read mappings
     *
     * @access private
     *
     */
    private function read()
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        $rbacreview = $DIC['rbacreview'];
        $tree = $DIC['tree'];
        
        $this->mappings = array();
        $query = "SELECT * FROM ldap_rg_mapping LEFT JOIN object_data " .
            "ON role = obj_id " .
            "WHERE server_id =" . $this->db->quote($this->getServerId(), 'integer') . ' ' .
            "ORDER BY title,dn";
            
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->mappings[$row->mapping_id]['mapping_id'] 			= $row->mapping_id;
            $this->mappings[$row->mapping_id]['dn'] 					= $row->dn;
            $this->mappings[$row->mapping_id]['url']					= $row->url;
            $this->mappings[$row->mapping_id]['member_attribute'] 		= $row->member_attribute;
            $this->mappings[$row->mapping_id]['member_isdn'] 			= $row->member_isdn;
            $this->mappings[$row->mapping_id]['role']					= $row->role;
            $this->mappings[$row->mapping_id]['info']					= $row->mapping_info;
            $this->mappings[$row->mapping_id]['info_type']				= $row->mapping_info_type;
            if ($ilObjDataCache->lookupType($row->role) == 'role') {
                $this->mappings[$row->mapping_id]['role_name']			= $ilObjDataCache->lookupTitle($row->role);
            } else {
                $this->mappings[$row->mapping_id]['role_name']			= $row->role;
            }
        }
    }
}
