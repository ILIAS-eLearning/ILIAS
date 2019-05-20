<?php



/**
 * LdapRoleAssignments
 */
class LdapRoleAssignments
{
    /**
     * @var int
     */
    private $serverId = '0';

    /**
     * @var bool
     */
    private $ruleId = '0';

    /**
     * @var bool
     */
    private $type = '0';

    /**
     * @var string|null
     */
    private $dn;

    /**
     * @var string|null
     */
    private $attribute;

    /**
     * @var bool
     */
    private $isdn = '0';

    /**
     * @var string|null
     */
    private $attName;

    /**
     * @var string|null
     */
    private $attValue;

    /**
     * @var int
     */
    private $roleId = '0';

    /**
     * @var bool|null
     */
    private $addOnUpdate;

    /**
     * @var bool|null
     */
    private $removeOnUpdate;

    /**
     * @var int|null
     */
    private $pluginId;


    /**
     * Set serverId.
     *
     * @param int $serverId
     *
     * @return LdapRoleAssignments
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
     * Set ruleId.
     *
     * @param bool $ruleId
     *
     * @return LdapRoleAssignments
     */
    public function setRuleId($ruleId)
    {
        $this->ruleId = $ruleId;

        return $this;
    }

    /**
     * Get ruleId.
     *
     * @return bool
     */
    public function getRuleId()
    {
        return $this->ruleId;
    }

    /**
     * Set type.
     *
     * @param bool $type
     *
     * @return LdapRoleAssignments
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return bool
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set dn.
     *
     * @param string|null $dn
     *
     * @return LdapRoleAssignments
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
     * Set attribute.
     *
     * @param string|null $attribute
     *
     * @return LdapRoleAssignments
     */
    public function setAttribute($attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Get attribute.
     *
     * @return string|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set isdn.
     *
     * @param bool $isdn
     *
     * @return LdapRoleAssignments
     */
    public function setIsdn($isdn)
    {
        $this->isdn = $isdn;

        return $this;
    }

    /**
     * Get isdn.
     *
     * @return bool
     */
    public function getIsdn()
    {
        return $this->isdn;
    }

    /**
     * Set attName.
     *
     * @param string|null $attName
     *
     * @return LdapRoleAssignments
     */
    public function setAttName($attName = null)
    {
        $this->attName = $attName;

        return $this;
    }

    /**
     * Get attName.
     *
     * @return string|null
     */
    public function getAttName()
    {
        return $this->attName;
    }

    /**
     * Set attValue.
     *
     * @param string|null $attValue
     *
     * @return LdapRoleAssignments
     */
    public function setAttValue($attValue = null)
    {
        $this->attValue = $attValue;

        return $this;
    }

    /**
     * Get attValue.
     *
     * @return string|null
     */
    public function getAttValue()
    {
        return $this->attValue;
    }

    /**
     * Set roleId.
     *
     * @param int $roleId
     *
     * @return LdapRoleAssignments
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;

        return $this;
    }

    /**
     * Get roleId.
     *
     * @return int
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

    /**
     * Set addOnUpdate.
     *
     * @param bool|null $addOnUpdate
     *
     * @return LdapRoleAssignments
     */
    public function setAddOnUpdate($addOnUpdate = null)
    {
        $this->addOnUpdate = $addOnUpdate;

        return $this;
    }

    /**
     * Get addOnUpdate.
     *
     * @return bool|null
     */
    public function getAddOnUpdate()
    {
        return $this->addOnUpdate;
    }

    /**
     * Set removeOnUpdate.
     *
     * @param bool|null $removeOnUpdate
     *
     * @return LdapRoleAssignments
     */
    public function setRemoveOnUpdate($removeOnUpdate = null)
    {
        $this->removeOnUpdate = $removeOnUpdate;

        return $this;
    }

    /**
     * Get removeOnUpdate.
     *
     * @return bool|null
     */
    public function getRemoveOnUpdate()
    {
        return $this->removeOnUpdate;
    }

    /**
     * Set pluginId.
     *
     * @param int|null $pluginId
     *
     * @return LdapRoleAssignments
     */
    public function setPluginId($pluginId = null)
    {
        $this->pluginId = $pluginId;

        return $this;
    }

    /**
     * Get pluginId.
     *
     * @return int|null
     */
    public function getPluginId()
    {
        return $this->pluginId;
    }
}
