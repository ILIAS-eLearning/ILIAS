<?php



/**
 * IlOrguUa
 */
class IlOrguUa
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int|null
     */
    private $userId;

    /**
     * @var int|null
     */
    private $positionId;

    /**
     * @var int|null
     */
    private $orguId;


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
     * Set userId.
     *
     * @param int|null $userId
     *
     * @return IlOrguUa
     */
    public function setUserId($userId = null)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int|null
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set positionId.
     *
     * @param int|null $positionId
     *
     * @return IlOrguUa
     */
    public function setPositionId($positionId = null)
    {
        $this->positionId = $positionId;

        return $this;
    }

    /**
     * Get positionId.
     *
     * @return int|null
     */
    public function getPositionId()
    {
        return $this->positionId;
    }

    /**
     * Set orguId.
     *
     * @param int|null $orguId
     *
     * @return IlOrguUa
     */
    public function setOrguId($orguId = null)
    {
        $this->orguId = $orguId;

        return $this;
    }

    /**
     * Get orguId.
     *
     * @return int|null
     */
    public function getOrguId()
    {
        return $this->orguId;
    }
}
