<?php



/**
 * SvyAnswer
 */
class SvyAnswer
{
    /**
     * @var int
     */
    private $answerId = '0';

    /**
     * @var int
     */
    private $activeFi = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var float|null
     */
    private $value;

    /**
     * @var string|null
     */
    private $textanswer;

    /**
     * @var int
     */
    private $rowvalue = '0';

    /**
     * @var int
     */
    private $tstamp = '0';


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
     * Set activeFi.
     *
     * @param int $activeFi
     *
     * @return SvyAnswer
     */
    public function setActiveFi($activeFi)
    {
        $this->activeFi = $activeFi;

        return $this;
    }

    /**
     * Get activeFi.
     *
     * @return int
     */
    public function getActiveFi()
    {
        return $this->activeFi;
    }

    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return SvyAnswer
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
     * Set value.
     *
     * @param float|null $value
     *
     * @return SvyAnswer
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return float|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set textanswer.
     *
     * @param string|null $textanswer
     *
     * @return SvyAnswer
     */
    public function setTextanswer($textanswer = null)
    {
        $this->textanswer = $textanswer;

        return $this;
    }

    /**
     * Get textanswer.
     *
     * @return string|null
     */
    public function getTextanswer()
    {
        return $this->textanswer;
    }

    /**
     * Set rowvalue.
     *
     * @param int $rowvalue
     *
     * @return SvyAnswer
     */
    public function setRowvalue($rowvalue)
    {
        $this->rowvalue = $rowvalue;

        return $this;
    }

    /**
     * Get rowvalue.
     *
     * @return int
     */
    public function getRowvalue()
    {
        return $this->rowvalue;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return SvyAnswer
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
}
