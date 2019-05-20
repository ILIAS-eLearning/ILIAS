<?php



/**
 * QplAOrdering
 */
class QplAOrdering
{
    /**
     * @var int
     */
    private $answerId = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $answertext;

    /**
     * @var int
     */
    private $solutionKey = '0';

    /**
     * @var int
     */
    private $randomId = '0';

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var int
     */
    private $depth = '0';

    /**
     * @var int|null
     */
    private $position;


    /**
     * Get answerId.
     *
     * @return int
     */
    public function getAnswerId()
    {
        return $this->answerId;
    }

    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return QplAOrdering
     */
    public function setQuestionFi($questionFi)
    {
        $this->questionFi = $questionFi;

        return $this;
    }

    /**
     * Get questionFi.
     *
     * @return int
     */
    public function getQuestionFi()
    {
        return $this->questionFi;
    }

    /**
     * Set answertext.
     *
     * @param string|null $answertext
     *
     * @return QplAOrdering
     */
    public function setAnswertext($answertext = null)
    {
        $this->answertext = $answertext;

        return $this;
    }

    /**
     * Get answertext.
     *
     * @return string|null
     */
    public function getAnswertext()
    {
        return $this->answertext;
    }

    /**
     * Set solutionKey.
     *
     * @param int $solutionKey
     *
     * @return QplAOrdering
     */
    public function setSolutionKey($solutionKey)
    {
        $this->solutionKey = $solutionKey;

        return $this;
    }

    /**
     * Get solutionKey.
     *
     * @return int
     */
    public function getSolutionKey()
    {
        return $this->solutionKey;
    }

    /**
     * Set randomId.
     *
     * @param int $randomId
     *
     * @return QplAOrdering
     */
    public function setRandomId($randomId)
    {
        $this->randomId = $randomId;

        return $this;
    }

    /**
     * Get randomId.
     *
     * @return int
     */
    public function getRandomId()
    {
        return $this->randomId;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return QplAOrdering
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
     * Set depth.
     *
     * @param int $depth
     *
     * @return QplAOrdering
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;

        return $this;
    }

    /**
     * Get depth.
     *
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * Set position.
     *
     * @param int|null $position
     *
     * @return QplAOrdering
     */
    public function setPosition($position = null)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     *
     * @return int|null
     */
    public function getPosition()
    {
        return $this->position;
    }
}
