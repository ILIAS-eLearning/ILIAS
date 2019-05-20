<?php



/**
 * Svy360Rater
 */
class Svy360Rater
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $apprId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $anonymousId = '0';

    /**
     * @var int|null
     */
    private $mailSent = '0';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return Svy360Rater
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
     * Set apprId.
     *
     * @param int $apprId
     *
     * @return Svy360Rater
     */
    public function setApprId($apprId)
    {
        $this->apprId = $apprId;

        return $this;
    }

    /**
     * Get apprId.
     *
     * @return int
     */
    public function getApprId()
    {
        return $this->apprId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return Svy360Rater
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
     * Set anonymousId.
     *
     * @param int $anonymousId
     *
     * @return Svy360Rater
     */
    public function setAnonymousId($anonymousId)
    {
        $this->anonymousId = $anonymousId;

        return $this;
    }

    /**
     * Get anonymousId.
     *
     * @return int
     */
    public function getAnonymousId()
    {
        return $this->anonymousId;
    }

    /**
     * Set mailSent.
     *
     * @param int|null $mailSent
     *
     * @return Svy360Rater
     */
    public function setMailSent($mailSent = null)
    {
        $this->mailSent = $mailSent;

        return $this;
    }

    /**
     * Get mailSent.
     *
     * @return int|null
     */
    public function getMailSent()
    {
        return $this->mailSent;
    }
}
