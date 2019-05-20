<?php



/**
 * SvyQstMc
 */
class SvyQstMc
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $orientation = '0';

    /**
     * @var bool
     */
    private $useMinAnswers = '0';

    /**
     * @var int|null
     */
    private $nrMinAnswers;

    /**
     * @var int|null
     */
    private $nrMaxAnswers;


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
     * Set orientation.
     *
     * @param string|null $orientation
     *
     * @return SvyQstMc
     */
    public function setOrientation($orientation = null)
    {
        $this->orientation = $orientation;

        return $this;
    }

    /**
     * Get orientation.
     *
     * @return string|null
     */
    public function getOrientation()
    {
        return $this->orientation;
    }

    /**
     * Set useMinAnswers.
     *
     * @param bool $useMinAnswers
     *
     * @return SvyQstMc
     */
    public function setUseMinAnswers($useMinAnswers)
    {
        $this->useMinAnswers = $useMinAnswers;

        return $this;
    }

    /**
     * Get useMinAnswers.
     *
     * @return bool
     */
    public function getUseMinAnswers()
    {
        return $this->useMinAnswers;
    }

    /**
     * Set nrMinAnswers.
     *
     * @param int|null $nrMinAnswers
     *
     * @return SvyQstMc
     */
    public function setNrMinAnswers($nrMinAnswers = null)
    {
        $this->nrMinAnswers = $nrMinAnswers;

        return $this;
    }

    /**
     * Get nrMinAnswers.
     *
     * @return int|null
     */
    public function getNrMinAnswers()
    {
        return $this->nrMinAnswers;
    }

    /**
     * Set nrMaxAnswers.
     *
     * @param int|null $nrMaxAnswers
     *
     * @return SvyQstMc
     */
    public function setNrMaxAnswers($nrMaxAnswers = null)
    {
        $this->nrMaxAnswers = $nrMaxAnswers;

        return $this;
    }

    /**
     * Get nrMaxAnswers.
     *
     * @return int|null
     */
    public function getNrMaxAnswers()
    {
        return $this->nrMaxAnswers;
    }
}
