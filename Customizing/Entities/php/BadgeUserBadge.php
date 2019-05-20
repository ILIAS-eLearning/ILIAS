<?php



/**
 * BadgeUserBadge
 */
class BadgeUserBadge
{
    /**
     * @var int
     */
    private $badgeId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var int|null
     */
    private $awardedBy;

    /**
     * @var int|null
     */
    private $pos;


    /**
     * Set badgeId.
     *
     * @param int $badgeId
     *
     * @return BadgeUserBadge
     */
    public function setBadgeId($badgeId)
    {
        $this->badgeId = $badgeId;

        return $this;
    }

    /**
     * Get badgeId.
     *
     * @return int
     */
    public function getBadgeId()
    {
        return $this->badgeId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return BadgeUserBadge
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
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return BadgeUserBadge
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return int
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * Set awardedBy.
     *
     * @param int|null $awardedBy
     *
     * @return BadgeUserBadge
     */
    public function setAwardedBy($awardedBy = null)
    {
        $this->awardedBy = $awardedBy;

        return $this;
    }

    /**
     * Get awardedBy.
     *
     * @return int|null
     */
    public function getAwardedBy()
    {
        return $this->awardedBy;
    }

    /**
     * Set pos.
     *
     * @param int|null $pos
     *
     * @return BadgeUserBadge
     */
    public function setPos($pos = null)
    {
        $this->pos = $pos;

        return $this;
    }

    /**
     * Get pos.
     *
     * @return int|null
     */
    public function getPos()
    {
        return $this->pos;
    }
}
