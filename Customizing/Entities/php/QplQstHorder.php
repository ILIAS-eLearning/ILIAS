<?php



/**
 * QplQstHorder
 */
class QplQstHorder
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $ordertext;

    /**
     * @var float|null
     */
    private $textsize;


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
     * Set ordertext.
     *
     * @param string|null $ordertext
     *
     * @return QplQstHorder
     */
    public function setOrdertext($ordertext = null)
    {
        $this->ordertext = $ordertext;

        return $this;
    }

    /**
     * Get ordertext.
     *
     * @return string|null
     */
    public function getOrdertext()
    {
        return $this->ordertext;
    }

    /**
     * Set textsize.
     *
     * @param float|null $textsize
     *
     * @return QplQstHorder
     */
    public function setTextsize($textsize = null)
    {
        $this->textsize = $textsize;

        return $this;
    }

    /**
     * Get textsize.
     *
     * @return float|null
     */
    public function getTextsize()
    {
        return $this->textsize;
    }
}
