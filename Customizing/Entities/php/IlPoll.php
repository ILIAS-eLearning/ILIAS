<?php



/**
 * IlPoll
 */
class IlPoll
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $question;

    /**
     * @var string|null
     */
    private $image;

    /**
     * @var bool
     */
    private $onlineStatus = '0';

    /**
     * @var bool
     */
    private $viewResults = '3';

    /**
     * @var bool
     */
    private $period = '0';

    /**
     * @var int|null
     */
    private $periodBegin = '0';

    /**
     * @var int|null
     */
    private $periodEnd = '0';

    /**
     * @var bool
     */
    private $maxAnswers = '1';

    /**
     * @var bool
     */
    private $resultSort = '0';

    /**
     * @var bool
     */
    private $nonAnon = '0';

    /**
     * @var bool
     */
    private $showResultsAs = '1';


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
     * Set question.
     *
     * @param string|null $question
     *
     * @return IlPoll
     */
    public function setQuestion($question = null)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question.
     *
     * @return string|null
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set image.
     *
     * @param string|null $image
     *
     * @return IlPoll
     */
    public function setImage($image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image.
     *
     * @return string|null
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set onlineStatus.
     *
     * @param bool $onlineStatus
     *
     * @return IlPoll
     */
    public function setOnlineStatus($onlineStatus)
    {
        $this->onlineStatus = $onlineStatus;

        return $this;
    }

    /**
     * Get onlineStatus.
     *
     * @return bool
     */
    public function getOnlineStatus()
    {
        return $this->onlineStatus;
    }

    /**
     * Set viewResults.
     *
     * @param bool $viewResults
     *
     * @return IlPoll
     */
    public function setViewResults($viewResults)
    {
        $this->viewResults = $viewResults;

        return $this;
    }

    /**
     * Get viewResults.
     *
     * @return bool
     */
    public function getViewResults()
    {
        return $this->viewResults;
    }

    /**
     * Set period.
     *
     * @param bool $period
     *
     * @return IlPoll
     */
    public function setPeriod($period)
    {
        $this->period = $period;

        return $this;
    }

    /**
     * Get period.
     *
     * @return bool
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Set periodBegin.
     *
     * @param int|null $periodBegin
     *
     * @return IlPoll
     */
    public function setPeriodBegin($periodBegin = null)
    {
        $this->periodBegin = $periodBegin;

        return $this;
    }

    /**
     * Get periodBegin.
     *
     * @return int|null
     */
    public function getPeriodBegin()
    {
        return $this->periodBegin;
    }

    /**
     * Set periodEnd.
     *
     * @param int|null $periodEnd
     *
     * @return IlPoll
     */
    public function setPeriodEnd($periodEnd = null)
    {
        $this->periodEnd = $periodEnd;

        return $this;
    }

    /**
     * Get periodEnd.
     *
     * @return int|null
     */
    public function getPeriodEnd()
    {
        return $this->periodEnd;
    }

    /**
     * Set maxAnswers.
     *
     * @param bool $maxAnswers
     *
     * @return IlPoll
     */
    public function setMaxAnswers($maxAnswers)
    {
        $this->maxAnswers = $maxAnswers;

        return $this;
    }

    /**
     * Get maxAnswers.
     *
     * @return bool
     */
    public function getMaxAnswers()
    {
        return $this->maxAnswers;
    }

    /**
     * Set resultSort.
     *
     * @param bool $resultSort
     *
     * @return IlPoll
     */
    public function setResultSort($resultSort)
    {
        $this->resultSort = $resultSort;

        return $this;
    }

    /**
     * Get resultSort.
     *
     * @return bool
     */
    public function getResultSort()
    {
        return $this->resultSort;
    }

    /**
     * Set nonAnon.
     *
     * @param bool $nonAnon
     *
     * @return IlPoll
     */
    public function setNonAnon($nonAnon)
    {
        $this->nonAnon = $nonAnon;

        return $this;
    }

    /**
     * Get nonAnon.
     *
     * @return bool
     */
    public function getNonAnon()
    {
        return $this->nonAnon;
    }

    /**
     * Set showResultsAs.
     *
     * @param bool $showResultsAs
     *
     * @return IlPoll
     */
    public function setShowResultsAs($showResultsAs)
    {
        $this->showResultsAs = $showResultsAs;

        return $this;
    }

    /**
     * Get showResultsAs.
     *
     * @return bool
     */
    public function getShowResultsAs()
    {
        return $this->showResultsAs;
    }
}
