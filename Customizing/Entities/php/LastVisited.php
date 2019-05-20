<?php



/**
 * LastVisited
 */
class LastVisited
{
    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $nr = '0';

    /**
     * @var int
     */
    private $refId = '0';

    /**
     * @var string
     */
    private $type = '';

    /**
     * @var string|null
     */
    private $subObjId;

    /**
     * @var string|null
     */
    private $gotoLink;


    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return LastVisited
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set nr.
     *
     * @param int $nr
     *
     * @return LastVisited
     */
    public function setNr($nr)
    {
        $this->nr = $nr;

        return $this;
    }

    /**
     * Get nr.
     *
     * @return int
     */
    public function getNr()
    {
        return $this->nr;
    }

    /**
     * Set refId.
     *
     * @param int $refId
     *
     * @return LastVisited
     */
    public function setRefId($refId)
    {
        $this->refId = $refId;

        return $this;
    }

    /**
     * Get refId.
     *
     * @return int
     */
    public function getRefId()
    {
        return $this->refId;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return LastVisited
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set subObjId.
     *
     * @param string|null $subObjId
     *
     * @return LastVisited
     */
    public function setSubObjId($subObjId = null)
    {
        $this->subObjId = $subObjId;

        return $this;
    }

    /**
     * Get subObjId.
     *
     * @return string|null
     */
    public function getSubObjId()
    {
        return $this->subObjId;
    }

    /**
     * Set gotoLink.
     *
     * @param string|null $gotoLink
     *
     * @return LastVisited
     */
    public function setGotoLink($gotoLink = null)
    {
        $this->gotoLink = $gotoLink;

        return $this;
    }

    /**
     * Get gotoLink.
     *
     * @return string|null
     */
    public function getGotoLink()
    {
        return $this->gotoLink;
    }
}
