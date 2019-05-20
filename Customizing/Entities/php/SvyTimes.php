<?php



/**
 * SvyTimes
 */
class SvyTimes
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $finishedFi = '0';

    /**
     * @var int|null
     */
    private $enteredPage;

    /**
     * @var int|null
     */
    private $leftPage;

    /**
     * @var int|null
     */
    private $firstQuestion;


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
     * Set finishedFi.
     *
     * @param int $finishedFi
     *
     * @return SvyTimes
     */
    public function setFinishedFi($finishedFi)
    {
        $this->finishedFi = $finishedFi;

        return $this;
    }

    /**
     * Get finishedFi.
     *
     * @return int
     */
    public function getFinishedFi()
    {
        return $this->finishedFi;
    }

    /**
     * Set enteredPage.
     *
     * @param int|null $enteredPage
     *
     * @return SvyTimes
     */
    public function setEnteredPage($enteredPage = null)
    {
        $this->enteredPage = $enteredPage;

        return $this;
    }

    /**
     * Get enteredPage.
     *
     * @return int|null
     */
    public function getEnteredPage()
    {
        return $this->enteredPage;
    }

    /**
     * Set leftPage.
     *
     * @param int|null $leftPage
     *
     * @return SvyTimes
     */
    public function setLeftPage($leftPage = null)
    {
        $this->leftPage = $leftPage;

        return $this;
    }

    /**
     * Get leftPage.
     *
     * @return int|null
     */
    public function getLeftPage()
    {
        return $this->leftPage;
    }

    /**
     * Set firstQuestion.
     *
     * @param int|null $firstQuestion
     *
     * @return SvyTimes
     */
    public function setFirstQuestion($firstQuestion = null)
    {
        $this->firstQuestion = $firstQuestion;

        return $this;
    }

    /**
     * Get firstQuestion.
     *
     * @return int|null
     */
    public function getFirstQuestion()
    {
        return $this->firstQuestion;
    }
}
