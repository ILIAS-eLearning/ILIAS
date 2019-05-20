<?php



/**
 * PersonalPcClipboard
 */
class PersonalPcClipboard
{
    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var \DateTime
     */
    private $insertTime = '1970-01-01 00:00:00';

    /**
     * @var int
     */
    private $orderNr = '0';

    /**
     * @var string|null
     */
    private $content;


    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return PersonalPcClipboard
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
     * Set insertTime.
     *
     * @param \DateTime $insertTime
     *
     * @return PersonalPcClipboard
     */
    public function setInsertTime($insertTime)
    {
        $this->insertTime = $insertTime;

        return $this;
    }

    /**
     * Get insertTime.
     *
     * @return \DateTime
     */
    public function getInsertTime()
    {
        return $this->insertTime;
    }

    /**
     * Set orderNr.
     *
     * @param int $orderNr
     *
     * @return PersonalPcClipboard
     */
    public function setOrderNr($orderNr)
    {
        $this->orderNr = $orderNr;

        return $this;
    }

    /**
     * Get orderNr.
     *
     * @return int
     */
    public function getOrderNr()
    {
        return $this->orderNr;
    }

    /**
     * Set content.
     *
     * @param string|null $content
     *
     * @return PersonalPcClipboard
     */
    public function setContent($content = null)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }
}
