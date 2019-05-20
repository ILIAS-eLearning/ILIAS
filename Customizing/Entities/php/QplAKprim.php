<?php



/**
 * QplAKprim
 */
class QplAKprim
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var int
     */
    private $position = '0';

    /**
     * @var string|null
     */
    private $answertext;

    /**
     * @var string|null
     */
    private $imagefile;

    /**
     * @var bool
     */
    private $correctness = '0';


    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return QplAKprim
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
     * Set position.
     *
     * @param int $position
     *
     * @return QplAKprim
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set answertext.
     *
     * @param string|null $answertext
     *
     * @return QplAKprim
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
     * Set imagefile.
     *
     * @param string|null $imagefile
     *
     * @return QplAKprim
     */
    public function setImagefile($imagefile = null)
    {
        $this->imagefile = $imagefile;

        return $this;
    }

    /**
     * Get imagefile.
     *
     * @return string|null
     */
    public function getImagefile()
    {
        return $this->imagefile;
    }

    /**
     * Set correctness.
     *
     * @param bool $correctness
     *
     * @return QplAKprim
     */
    public function setCorrectness($correctness)
    {
        $this->correctness = $correctness;

        return $this;
    }

    /**
     * Get correctness.
     *
     * @return bool
     */
    public function getCorrectness()
    {
        return $this->correctness;
    }
}
