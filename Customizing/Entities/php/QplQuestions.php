<?php



/**
 * QplQuestions
 */
class QplQuestions
{
    /**
     * @var int
     */
    private $questionId = '0';

    /**
     * @var int
     */
    private $questionTypeFi = '0';

    /**
     * @var int
     */
    private $objFi = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $author;

    /**
     * @var int
     */
    private $owner = '0';

    /**
     * @var string|null
     */
    private $workingTime = '00:00:00';

    /**
     * @var float|null
     */
    private $points;

    /**
     * @var string|null
     */
    private $complete = '1';

    /**
     * @var int|null
     */
    private $originalId;

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var int
     */
    private $created = '0';

    /**
     * @var int
     */
    private $nrOfTries = '0';

    /**
     * @var string|null
     */
    private $questionText;

    /**
     * @var string|null
     */
    private $addContEditMode;

    /**
     * @var string|null
     */
    private $externalId;

    /**
     * @var string|null
     */
    private $lifecycle = 'draft';


    /**
     * Get questionId.
     *
     * @return int
     */
    public function getQuestionId()
    {
        return $this->questionId;
    }

    /**
     * Set questionTypeFi.
     *
     * @param int $questionTypeFi
     *
     * @return QplQuestions
     */
    public function setQuestionTypeFi($questionTypeFi)
    {
        $this->questionTypeFi = $questionTypeFi;

        return $this;
    }

    /**
     * Get questionTypeFi.
     *
     * @return int
     */
    public function getQuestionTypeFi()
    {
        return $this->questionTypeFi;
    }

    /**
     * Set objFi.
     *
     * @param int $objFi
     *
     * @return QplQuestions
     */
    public function setObjFi($objFi)
    {
        $this->objFi = $objFi;

        return $this;
    }

    /**
     * Get objFi.
     *
     * @return int
     */
    public function getObjFi()
    {
        return $this->objFi;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return QplQuestions
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return QplQuestions
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set author.
     *
     * @param string|null $author
     *
     * @return QplQuestions
     */
    public function setAuthor($author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author.
     *
     * @return string|null
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set owner.
     *
     * @param int $owner
     *
     * @return QplQuestions
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner.
     *
     * @return int
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set workingTime.
     *
     * @param string|null $workingTime
     *
     * @return QplQuestions
     */
    public function setWorkingTime($workingTime = null)
    {
        $this->workingTime = $workingTime;

        return $this;
    }

    /**
     * Get workingTime.
     *
     * @return string|null
     */
    public function getWorkingTime()
    {
        return $this->workingTime;
    }

    /**
     * Set points.
     *
     * @param float|null $points
     *
     * @return QplQuestions
     */
    public function setPoints($points = null)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points.
     *
     * @return float|null
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set complete.
     *
     * @param string|null $complete
     *
     * @return QplQuestions
     */
    public function setComplete($complete = null)
    {
        $this->complete = $complete;

        return $this;
    }

    /**
     * Get complete.
     *
     * @return string|null
     */
    public function getComplete()
    {
        return $this->complete;
    }

    /**
     * Set originalId.
     *
     * @param int|null $originalId
     *
     * @return QplQuestions
     */
    public function setOriginalId($originalId = null)
    {
        $this->originalId = $originalId;

        return $this;
    }

    /**
     * Get originalId.
     *
     * @return int|null
     */
    public function getOriginalId()
    {
        return $this->originalId;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return QplQuestions
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
     * Set created.
     *
     * @param int $created
     *
     * @return QplQuestions
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return int
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set nrOfTries.
     *
     * @param int $nrOfTries
     *
     * @return QplQuestions
     */
    public function setNrOfTries($nrOfTries)
    {
        $this->nrOfTries = $nrOfTries;

        return $this;
    }

    /**
     * Get nrOfTries.
     *
     * @return int
     */
    public function getNrOfTries()
    {
        return $this->nrOfTries;
    }

    /**
     * Set questionText.
     *
     * @param string|null $questionText
     *
     * @return QplQuestions
     */
    public function setQuestionText($questionText = null)
    {
        $this->questionText = $questionText;

        return $this;
    }

    /**
     * Get questionText.
     *
     * @return string|null
     */
    public function getQuestionText()
    {
        return $this->questionText;
    }

    /**
     * Set addContEditMode.
     *
     * @param string|null $addContEditMode
     *
     * @return QplQuestions
     */
    public function setAddContEditMode($addContEditMode = null)
    {
        $this->addContEditMode = $addContEditMode;

        return $this;
    }

    /**
     * Get addContEditMode.
     *
     * @return string|null
     */
    public function getAddContEditMode()
    {
        return $this->addContEditMode;
    }

    /**
     * Set externalId.
     *
     * @param string|null $externalId
     *
     * @return QplQuestions
     */
    public function setExternalId($externalId = null)
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * Get externalId.
     *
     * @return string|null
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * Set lifecycle.
     *
     * @param string|null $lifecycle
     *
     * @return QplQuestions
     */
    public function setLifecycle($lifecycle = null)
    {
        $this->lifecycle = $lifecycle;

        return $this;
    }

    /**
     * Get lifecycle.
     *
     * @return string|null
     */
    public function getLifecycle()
    {
        return $this->lifecycle;
    }
}
