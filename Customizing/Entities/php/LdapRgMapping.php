<?php



/**
 * LdapRgMapping
 */
class LdapRgMapping
{
    /**
     * @var int
     */
    private $mappingId = '0';

    /**
     * @var int
     */
    private $serverId = '0';

    /**
     * @var string|null
     */
    private $url;

    /**
     * @var string|null
     */
    private $dn;

    /**
     * @var string|null
     */
    private $memberAttribute;

    /**
     * @var bool
     */
    private $memberIsdn = '0';

    /**
     * @var int
     */
    private $role = '0';

    /**
     * @var string|null
     */
    private $mappingInfo;

    /**
     * @var bool
     */
    private $mappingInfoType = '1';


    /**
     * Get mappingId.
     *
     * @return int
     */
    public function getMappingId()
    {
        return $this->mappingId;
    }

    /**
     * Set serverId.
     *
     * @param int $serverId
     *
     * @return LdapRgMapping
     */
    public function setServerId($serverId)
    {
        $this->serverId = $serverId;

        return $this;
    }

    /**
     * Get serverId.
     *
     * @return int
     */
    public function getServerId()
    {
        return $this->serverId;
    }

    /**
     * Set url.
     *
     * @param string|null $url
     *
     * @return LdapRgMapping
     */
    public function setUrl($url = null)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set dn.
     *
     * @param string|null $dn
     *
     * @return LdapRgMapping
     */
    public function setDn($dn = null)
    {
        $this->dn = $dn;

        return $this;
    }

    /**
     * Get dn.
     *
     * @return string|null
     */
    public function getDn()
    {
        return $this->dn;
    }

    /**
     * Set memberAttribute.
     *
     * @param string|null $memberAttribute
     *
     * @return LdapRgMapping
     */
    public function setMemberAttribute($memberAttribute = null)
    {
        $this->memberAttribute = $memberAttribute;

        return $this;
    }

    /**
     * Get memberAttribute.
     *
     * @return string|null
     */
    public function getMemberAttribute()
    {
        return $this->memberAttribute;
    }

    /**
     * Set memberIsdn.
     *
     * @param bool $memberIsdn
     *
     * @return LdapRgMapping
     */
    public function setMemberIsdn($memberIsdn)
    {
        $this->memberIsdn = $memberIsdn;

        return $this;
    }

    /**
     * Get memberIsdn.
     *
     * @return bool
     */
    public function getMemberIsdn()
    {
        return $this->memberIsdn;
    }

    /**
     * Set role.
     *
     * @param int $role
     *
     * @return LdapRgMapping
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role.
     *
     * @return int
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set mappingInfo.
     *
     * @param string|null $mappingInfo
     *
     * @return LdapRgMapping
     */
    public function setMappingInfo($mappingInfo = null)
    {
        $this->mappingInfo = $mappingInfo;

        return $this;
    }

    /**
     * Get mappingInfo.
     *
     * @return string|null
     */
    public function getMappingInfo()
    {
        return $this->mappingInfo;
    }

    /**
     * Set mappingInfoType.
     *
     * @param bool $mappingInfoType
     *
     * @return LdapRgMapping
     */
    public function setMappingInfoType($mappingInfoType)
    {
        $this->mappingInfoType = $mappingInfoType;

        return $this;
    }

    /**
     * Get mappingInfoType.
     *
     * @return bool
     */
    public function getMappingInfoType()
    {
        return $this->mappingInfoType;
    }
}
