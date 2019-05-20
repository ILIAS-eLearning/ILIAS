<?php



/**
 * IlWikiContributor
 */
class IlWikiContributor
{
    /**
     * @var int
     */
    private $wikiId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int|null
     */
    private $status;

    /**
     * @var \DateTime|null
     */
    private $statusTime;


    /**
     * Set wikiId.
     *
     * @param int $wikiId
     *
     * @return IlWikiContributor
     */
    public function setWikiId($wikiId)
    {
        $this->wikiId = $wikiId;

        return $this;
    }

    /**
     * Get wikiId.
     *
     * @return int
     */
    public function getWikiId()
    {
        return $this->wikiId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return IlWikiContributor
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
     * Set status.
     *
     * @param int|null $status
     *
     * @return IlWikiContributor
     */
    public function setStatus($status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return int|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set statusTime.
     *
     * @param \DateTime|null $statusTime
     *
     * @return IlWikiContributor
     */
    public function setStatusTime($statusTime = null)
    {
        $this->statusTime = $statusTime;

        return $this;
    }

    /**
     * Get statusTime.
     *
     * @return \DateTime|null
     */
    public function getStatusTime()
    {
        return $this->statusTime;
    }
}
