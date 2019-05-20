<?php



/**
 * SyscTasks
 */
class SyscTasks
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $grpId = '0';

    /**
     * @var \DateTime|null
     */
    private $lastUpdate;

    /**
     * @var bool
     */
    private $status = '0';

    /**
     * @var string|null
     */
    private $identifier;


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
     * Set grpId.
     *
     * @param int $grpId
     *
     * @return SyscTasks
     */
    public function setGrpId($grpId)
    {
        $this->grpId = $grpId;

        return $this;
    }

    /**
     * Get grpId.
     *
     * @return int
     */
    public function getGrpId()
    {
        return $this->grpId;
    }

    /**
     * Set lastUpdate.
     *
     * @param \DateTime|null $lastUpdate
     *
     * @return SyscTasks
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
     * @return SyscTasks
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

    /**
     * Set identifier.
     *
     * @param string|null $identifier
     *
     * @return SyscTasks
     */
    public function setIdentifier($identifier = null)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier.
     *
     * @return string|null
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
}
