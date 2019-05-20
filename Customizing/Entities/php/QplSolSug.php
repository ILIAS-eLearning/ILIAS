<?php



/**
 * QplSolSug
 */
class QplSolSug
{
    /**
     * @var int
     */
    private $suggestedSolutionId = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $internalLink;

    /**
     * @var string|null
     */
    private $importId;

    /**
     * @var int
     */
    private $subquestionIndex = '0';

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var string|null
     */
    private $value;


    /**
     * Get suggestedSolutionId.
     *
     * @return int
     */
    public function getSuggestedSolutionId()
    {
        return $this->suggestedSolutionId;
    }

    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return QplSolSug
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
     * Set internalLink.
     *
     * @param string|null $internalLink
     *
     * @return QplSolSug
     */
    public function setInternalLink($internalLink = null)
    {
        $this->internalLink = $internalLink;

        return $this;
    }

    /**
     * Get internalLink.
     *
     * @return string|null
     */
    public function getInternalLink()
    {
        return $this->internalLink;
    }

    /**
     * Set importId.
     *
     * @param string|null $importId
     *
     * @return QplSolSug
     */
    public function setImportId($importId = null)
    {
        $this->importId = $importId;

        return $this;
    }

    /**
     * Get importId.
     *
     * @return string|null
     */
    public function getImportId()
    {
        return $this->importId;
    }

    /**
     * Set subquestionIndex.
     *
     * @param int $subquestionIndex
     *
     * @return QplSolSug
     */
    public function setSubquestionIndex($subquestionIndex)
    {
        $this->subquestionIndex = $subquestionIndex;

        return $this;
    }

    /**
     * Get subquestionIndex.
     *
     * @return int
     */
    public function getSubquestionIndex()
    {
        return $this->subquestionIndex;
    }

    /**
     * Set type.
     *
     * @param string|null $type
     *
     * @return QplSolSug
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return QplSolSug
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
     * Set value.
     *
     * @param string|null $value
     *
     * @return QplSolSug
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }
}
