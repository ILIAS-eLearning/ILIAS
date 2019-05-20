<?php



/**
 * ShibRoleAssignment
 */
class ShibRoleAssignment
{
    /**
     * @var int
     */
    private $ruleId = '0';

    /**
     * @var int
     */
    private $roleId = '0';

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $value;

    /**
     * @var bool
     */
    private $plugin = '0';

    /**
     * @var int
     */
    private $pluginId = '0';

    /**
     * @var bool
     */
    private $addOnUpdate = '0';

    /**
     * @var bool
     */
    private $removeOnUpdate = '0';


    /**
     * Get ruleId.
     *
     * @return int
     */
    public function getRuleId()
    {
        return $this->ruleId;
    }

    /**
     * Set roleId.
     *
     * @param int $roleId
     *
     * @return ShibRoleAssignment
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
     * Set name.
     *
     * @param string|null $name
     *
     * @return ShibRoleAssignment
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set value.
     *
     * @param string|null $value
     *
     * @return ShibRoleAssignment
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set plugin.
     *
     * @param bool $plugin
     *
     * @return ShibRoleAssignment
     */
    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;

        return $this;
    }

    /**
     * Get plugin.
     *
     * @return bool
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * Set pluginId.
     *
     * @param int $pluginId
     *
     * @return ShibRoleAssignment
     */
    public function setPluginId($pluginId)
    {
        $this->pluginId = $pluginId;

        return $this;
    }

    /**
     * Get pluginId.
     *
     * @return int
     */
    public function getPluginId()
    {
        return $this->pluginId;
    }

    /**
     * Set addOnUpdate.
     *
     * @param bool $addOnUpdate
     *
     * @return ShibRoleAssignment
     */
    public function setAddOnUpdate($addOnUpdate)
    {
        $this->addOnUpdate = $addOnUpdate;

        return $this;
    }

    /**
     * Get addOnUpdate.
     *
     * @return bool
     */
    public function getAddOnUpdate()
    {
        return $this->addOnUpdate;
    }

    /**
     * Set removeOnUpdate.
     *
     * @param bool $removeOnUpdate
     *
     * @return ShibRoleAssignment
     */
    public function setRemoveOnUpdate($removeOnUpdate)
    {
        $this->removeOnUpdate = $removeOnUpdate;

        return $this;
    }

    /**
     * Get removeOnUpdate.
     *
     * @return bool
     */
    public function getRemoveOnUpdate()
    {
        return $this->removeOnUpdate;
    }
}
