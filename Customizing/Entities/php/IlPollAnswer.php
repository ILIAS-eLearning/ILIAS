<?php



/**
 * IlPollAnswer
 */
class IlPollAnswer
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $pollId = '0';

    /**
     * @var string|null
     */
    private $answer;

    /**
     * @var int
     */
    private $pos = '0';


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
     * Set pollId.
     *
     * @param int $pollId
     *
     * @return IlPollAnswer
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
     * Set answer.
     *
     * @param string|null $answer
     *
     * @return IlPollAnswer
     */
    public function setAnswer($answer = null)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Get answer.
     *
     * @return string|null
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * Set pos.
     *
     * @param int $pos
     *
     * @return IlPollAnswer
     */
    public function setPos($pos)
    {
        $this->pos = $pos;

        return $this;
    }

    /**
     * Get pos.
     *
     * @return int
     */
    public function getPos()
    {
        return $this->pos;
    }
}
