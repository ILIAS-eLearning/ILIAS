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
 * @author Fabian Wolf <wolf@leifos.de>
 */
class ilLDAPRoleGroupMappingSetting
{
    private ilDBInterface $db;
    private ilObjectDataCache $ilObjDataCache;
    private ilRbacReview $rbacreview;

    private int $mapping_id;
    private int $server_id;

    private ?string $url;
    private ?string $dn;
    private ?string $member_attribute;
    private ?bool $mapping_info_type;
    private ?bool $member_isdn;
    private ?int $role;
    private ?string $mapping_info;

    public function __construct(int $a_mapping_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->ilObjDataCache = $DIC['ilObjDataCache'];
        $this->rbacreview = $DIC['rbacreview'];
        $this->mapping_id = $a_mapping_id;
    }
    
    /**
     * read data from db
     */
    public function read() : void
    {
        $query = "SELECT * FROM ldap_rg_mapping "
                . "WHERE mapping_id = " . $this->db->quote($this->getMappingId(), 'integer');
        $set = $this->db->query($query);
        $rec = $this->db->fetchAssoc($set);
        $this->setMappingId((int) $rec["mapping_id"]);
        $this->setServerId((int) $rec["server_id"]);
        $this->setURL($rec["url"]);
        $this->setDN($rec["dn"]);
        $this->setMemberAttribute($rec["member_attribute"]);
        $this->setMemberISDN($rec["member_isdn"]);
        $this->setRole((int) $rec["role"]);
        $this->setMappingInfo($rec["mapping_info"]);
        $this->setMappingInfoType((bool) $rec["mapping_info_type"]);
    }
    
    /**
     * delete mapping by id
     */
    public function delete() : void
    {
        $query = "DELETE FROM ldap_rg_mapping " .
            "WHERE mapping_id = " . $this->db->quote($this->getMappingId(), 'integer');
        $this->db->manipulate($query);
    }
    
    /**
     * update mapping by id
     */
    public function update() : void
    {
        $query = "UPDATE ldap_rg_mapping " .
                    "SET server_id = " . $this->db->quote($this->getServerId(), 'integer') . ", " .
                    "url = " . $this->db->quote($this->getURL(), 'text') . ", " .
                    "dn =" . $this->db->quote($this->getDN(), 'text') . ", " .
                    "member_attribute = " . $this->db->quote($this->getMemberAttribute(), 'text') . ", " .
                    "member_isdn = " . $this->db->quote($this->getMemberISDN(), 'integer') . ", " .
                    "role = " . $this->db->quote($this->getRole(), 'integer') . ", " .
                    "mapping_info = " . $this->db->quote($this->getMappingInfo(), 'text') . ", " .
                    "mapping_info_type = " . $this->db->quote($this->getMappingInfoType(), 'integer') . " " .
                    "WHERE mapping_id = " . $this->db->quote($this->getMappingId(), 'integer');
        $this->db->manipulate($query);
    }
    
    /**
     * create new mapping
     */
    public function save() : void
    {
        $this->setMappingId($this->db->nextId('ldap_rg_mapping'));
        $query = "INSERT INTO ldap_rg_mapping (mapping_id,server_id,url,dn,member_attribute,member_isdn,role,mapping_info,mapping_info_type) " .
                    "VALUES ( " .
                    $this->db->quote($this->getMappingId(), 'integer') . ", " .
                    $this->db->quote($this->getServerId(), 'integer') . ", " .
                    $this->db->quote($this->getURL(), 'text') . ", " .
                    $this->db->quote($this->getDN(), 'text') . ", " .
                    $this->db->quote($this->getMemberAttribute(), 'text') . ", " .
                    $this->db->quote($this->getMemberISDN(), 'integer') . ", " .
                    $this->db->quote($this->getRole(), 'integer') . ", " .
                    $this->db->quote($this->getMappingInfo(), 'text') . ", " .
                    $this->db->quote($this->getMappingInfoType(), 'integer') .
                    ")";
        $this->db->manipulate($query);
    }
    
    /**
     * get mapping id
     * @return int mapping id
     */
    public function getMappingId() : int
    {
        return $this->mapping_id;
    }
    
    /**
     * set mapping id
     * @param int $a_value mapping id
     */
    public function setMappingId(int $a_value) : void
    {
        $this->mapping_id = $a_value;
    }
    
    /**
     * get server id
     * @return int server id id
     */
    public function getServerId() : int
    {
        return $this->server_id;
    }
    
    /**
     * set server id
     * @param int $a_value server id
     */
    public function setServerId(int $a_value) : void
    {
        $this->server_id = $a_value;
    }
    
    /**
     * get url
     * @return string url
     */
    public function getURL() : string
    {
        return $this->url;
    }
    
    /**
     * set url
     * @param string $a_value url
     */
    public function setURL(string $a_value) : void
    {
        $this->url = $a_value;
    }
    
    /**
     * get group dn
     */
    public function getDN() : string
    {
        return $this->dn;
    }
    
    /**
     * set group dn
     */
    public function setDN(string $a_value) : void
    {
        $this->dn = $a_value;
    }
    
    /**
     * get Group Member Attribute
     */
    public function getMemberAttribute() : string
    {
        return $this->member_attribute;
    }
    
    /**
     * set Group Member Attribute
     */
    public function setMemberAttribute(string $a_value) : void
    {
        $this->member_attribute = $a_value;
    }
    
    /**
     * get Member Attribute Value is DN
     */
    public function getMemberISDN() : bool
    {
        return $this->member_isdn;
    }
    
    /**
     * set Member Attribute Value is DN
     * @param bool $a_value
     */
    public function setMemberISDN(bool $a_value) : void
    {
        $this->member_isdn = $a_value;
    }
    
    /**
     * get ILIAS Role Name id
     */
    public function getRole() : int
    {
        return $this->role;
    }
    
    /**
     * set ILIAS Role Name id
     */
    public function setRole(int $a_value) : void
    {
        $this->role = $a_value;
    }
    
    /**
     * get ILIAS Role Name
     */
    public function getRoleName() : string
    {
        return $this->ilObjDataCache->lookupTitle($this->role);
    }
    
    /**
     * set ILIAS Role Name
     */
    public function setRoleByName(string $a_value) : void
    {
        $this->role = $this->rbacreview->roleExists(ilUtil::stripSlashes($a_value));
    }
    
    /**
     * get Information Text
     */
    public function getMappingInfo() : string
    {
        return $this->mapping_info;
    }
    
    /**
     * set Information Text
     */
    public function setMappingInfo(string $a_value) : void
    {
        $this->mapping_info = $a_value;
    }
    
    /**
     * get Show Information also in the Repository/Personal Desktop
     */
    public function getMappingInfoType() : bool
    {
        return $this->mapping_info_type;
    }
    
    /**
     * set Show Information also in the Repository/Personal Desktop
     */
    public function setMappingInfoType(bool $a_value) : void
    {
        $this->mapping_info_type = $a_value;
    }
}
