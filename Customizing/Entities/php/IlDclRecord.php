<?php



/**
 * IlDclRecord
 */
class IlDclRecord
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $tableId = '0';

    /**
     * @var \DateTime|null
     */
    private $createDate;

    /**
     * @var \DateTime|null
     */
    private $lastUpdate;

    /**
     * @var int
     */
    private $owner = '0';

    /**
     * @var int|null
     */
    private $lastEditBy;


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
     * Set tableId.
     *
     * @param int $tableId
     *
     * @return IlDclRecord
     */
    public function setTableId($tableId)
    {
        $this->tableId = $tableId;

        return $this;
    }

    /**
     * Get tableId.
     *
     * @return int
     */
    public function getTableId()
    {
        return $this->tableId;
    }

    /**
     * Set createDate.
     *
     * @param \DateTime|null $createDate
     *
     * @return IlDclRecord
     */
    public function setCreateDate($createDate = null)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate.
     *
     * @return \DateTime|null
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Set lastUpdate.
     *
     * @param \DateTime|null $lastUpdate
     *
     * @return IlDclRecord
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
     * Set owner.
     *
     * @param int $owner
     *
     * @return IlDclRecord
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner.
     *
     * @return int
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set lastEditBy.
     *
     * @param int|null $lastEditBy
     *
     * @return IlDclRecord
     */
    public function setLastEditBy($lastEditBy = null)
    {
        $this->lastEditBy = $lastEditBy;

        return $this;
    }

    /**
     * Get lastEditBy.
     *
     * @return int|null
     */
    public function getLastEditBy()
    {
        return $this->lastEditBy;
    }
}
