<?php



/**
 * IlNewsRead
 */
class IlNewsRead
{
    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $newsId = '0';


    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return IlNewsRead
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
     * Set newsId.
     *
     * @param int $newsId
     *
     * @return IlNewsRead
     */
    public function setNewsId($newsId)
    {
        $this->newsId = $newsId;

        return $this;
    }

    /**
     * Get newsId.
     *
     * @return int
     */
    public function getNewsId()
    {
        return $this->newsId;
    }
}
