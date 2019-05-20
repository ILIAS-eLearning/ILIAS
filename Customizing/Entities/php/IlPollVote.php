<?php



/**
 * IlPollVote
 */
class IlPollVote
{
    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $pollId = '0';

    /**
     * @var int
     */
    private $answerId = '0';


    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return IlPollVote
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
     * Set pollId.
     *
     * @param int $pollId
     *
     * @return IlPollVote
     */
    public function setPollId($pollId)
    {
        $this->pollId = $pollId;

        return $this;
    }

    /**
     * Get pollId.
     *
     * @return int
     */
    public function getPollId()
    {
        return $this->pollId;
    }

    /**
     * Set answerId.
     *
     * @param int $answerId
     *
     * @return IlPollVote
     */
    public function setAnswerId($answerId)
    {
        $this->answerId = $answerId;

        return $this;
    }

    /**
     * Get answerId.
     *
     * @return int
     */
    public function getAnswerId()
    {
        return $this->answerId;
    }
}
