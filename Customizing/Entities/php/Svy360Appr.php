<?php



/**
 * Svy360Appr
 */
class Svy360Appr
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int|null
     */
    private $hasClosed = '0';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return Svy360Appr
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return Svy360Appr
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
     * Set hasClosed.
     *
     * @param int|null $hasClosed
     *
     * @return Svy360Appr
     */
    public function setHasClosed($hasClosed = null)
    {
        $this->hasClosed = $hasClosed;

        return $this;
    }

    /**
     * Get hasClosed.
     *
     * @return int|null
     */
    public function getHasClosed()
    {
        return $this->hasClosed;
    }
}
