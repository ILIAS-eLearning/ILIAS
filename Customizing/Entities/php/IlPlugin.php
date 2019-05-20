<?php



/**
 * IlPlugin
 */
class IlPlugin
{
    /**
     * @var string
     */
    private $componentType = '';

    /**
     * @var string
     */
    private $componentName = ' ';

    /**
     * @var string
     */
    private $slotId = '';

    /**
     * @var string
     */
    private $name = ' ';

    /**
     * @var string|null
     */
    private $lastUpdateVersion;

    /**
     * @var bool|null
     */
    private $active;

    /**
     * @var int
     */
    private $dbVersion = '0';

    /**
     * @var string|null
     */
    private $pluginId;


    /**
     * Set componentType.
     *
     * @param string $componentType
     *
     * @return IlPlugin
     */
    public function setComponentType($componentType)
    {
        $this->componentType = $componentType;

        return $this;
    }

    /**
     * Get componentType.
     *
     * @return string
     */
    public function getComponentType()
    {
        return $this->componentType;
    }

    /**
     * Set componentName.
     *
     * @param string $componentName
     *
     * @return IlPlugin
     */
    public function setComponentName($componentName)
    {
        $this->componentName = $componentName;

        return $this;
    }

    /**
     * Get componentName.
     *
     * @return string
     */
    public function getComponentName()
    {
        return $this->componentName;
    }

    /**
     * Set slotId.
     *
     * @param string $slotId
     *
     * @return IlPlugin
     */
    public function setSlotId($slotId)
    {
        $this->slotId = $slotId;

        return $this;
    }

    /**
     * Get slotId.
     *
     * @return string
     */
    public function getSlotId()
    {
        return $this->slotId;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return IlPlugin
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set lastUpdateVersion.
     *
     * @param string|null $lastUpdateVersion
     *
     * @return IlPlugin
     */
    public function setLastUpdateVersion($lastUpdateVersion = null)
    {
        $this->lastUpdateVersion = $lastUpdateVersion;

        return $this;
    }

    /**
     * Get lastUpdateVersion.
     *
     * @return string|null
     */
    public function getLastUpdateVersion()
    {
        return $this->lastUpdateVersion;
    }

    /**
     * Set active.
     *
     * @param bool|null $active
     *
     * @return IlPlugin
     */
    public function setActive($active = null)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool|null
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set dbVersion.
     *
     * @param int $dbVersion
     *
     * @return IlPlugin
     */
    public function setDbVersion($dbVersion)
    {
        $this->dbVersion = $dbVersion;

        return $this;
    }

    /**
     * Get dbVersion.
     *
     * @return int
     */
    public function getDbVersion()
    {
        return $this->dbVersion;
    }

    /**
     * Set pluginId.
     *
     * @param string|null $pluginId
     *
     * @return IlPlugin
     */
    public function setPluginId($pluginId = null)
    {
        $this->pluginId = $pluginId;

        return $this;
    }

    /**
     * Get pluginId.
     *
     * @return string|null
     */
    public function getPluginId()
    {
        return $this->pluginId;
    }
}
