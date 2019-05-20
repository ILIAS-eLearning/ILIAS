<?php



/**
 * SahsSc13SeqCourse
 */
class SahsSc13SeqCourse
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var bool|null
     */
    private $flow = '0';

    /**
     * @var bool|null
     */
    private $choice = '1';

    /**
     * @var bool|null
     */
    private $forwardonly = '0';


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
     * Set flow.
     *
     * @param bool|null $flow
     *
     * @return SahsSc13SeqCourse
     */
    public function setFlow($flow = null)
    {
        $this->flow = $flow;

        return $this;
    }

    /**
     * Get flow.
     *
     * @return bool|null
     */
    public function getFlow()
    {
        return $this->flow;
    }

    /**
     * Set choice.
     *
     * @param bool|null $choice
     *
     * @return SahsSc13SeqCourse
     */
    public function setChoice($choice = null)
    {
        $this->choice = $choice;

        return $this;
    }

    /**
     * Get choice.
     *
     * @return bool|null
     */
    public function getChoice()
    {
        return $this->choice;
    }

    /**
     * Set forwardonly.
     *
     * @param bool|null $forwardonly
     *
     * @return SahsSc13SeqCourse
     */
    public function setForwardonly($forwardonly = null)
    {
        $this->forwardonly = $forwardonly;

        return $this;
    }

    /**
     * Get forwardonly.
     *
     * @return bool|null
     */
    public function getForwardonly()
    {
        return $this->forwardonly;
    }
}
