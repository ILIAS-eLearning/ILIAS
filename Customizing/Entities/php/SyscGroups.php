<?php



/**
 * SyscGroups
 */
class SyscGroups
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $component;

    /**
     * @var \DateTime|null
     */
    private $lastUpdate;

    /**
     * @var bool
     */
    private $status = '0';


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set component.
     *
     * @param string|null $component
     *
     * @return SyscGroups
     */
    public function setComponent($component = null)
    {
        $this->component = $component;

        return $this;
    }

    /**
     * Get component.
     *
     * @return string|null
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * Set lastUpdate.
     *
     * @param \DateTime|null $lastUpdate
     *
     * @return SyscGroups
     */
    public function setLastUpdate($lastUpdate = null)
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * Get lastUpdate.
     *
     * @return \DateTime|null
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /**
     * Set status.
     *
     * @param bool $status
     *
     * @return SyscGroups
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return bool
     */
    public function getStatus()
    {
        return $this->status;
    }
}
