<?php



/**
 * QplQstEssay
 */
class QplQstEssay
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var int
     */
    private $maxnumofchars = '0';

    /**
     * @var string|null
     */
    private $keywords;

    /**
     * @var string|null
     */
    private $textgapRating;

    /**
     * @var int
     */
    private $matchcondition = '0';

    /**
     * @var string
     */
    private $keywordRelation = 'any';

    /**
     * @var bool|null
     */
    private $wordCntEnabled = '0';


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
     * Set maxnumofchars.
     *
     * @param int $maxnumofchars
     *
     * @return QplQstEssay
     */
    public function setMaxnumofchars($maxnumofchars)
    {
        $this->maxnumofchars = $maxnumofchars;

        return $this;
    }

    /**
     * Get maxnumofchars.
     *
     * @return int
     */
    public function getMaxnumofchars()
    {
        return $this->maxnumofchars;
    }

    /**
     * Set keywords.
     *
     * @param string|null $keywords
     *
     * @return QplQstEssay
     */
    public function setKeywords($keywords = null)
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Get keywords.
     *
     * @return string|null
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set textgapRating.
     *
     * @param string|null $textgapRating
     *
     * @return QplQstEssay
     */
    public function setTextgapRating($textgapRating = null)
    {
        $this->textgapRating = $textgapRating;

        return $this;
    }

    /**
     * Get textgapRating.
     *
     * @return string|null
     */
    public function getTextgapRating()
    {
        return $this->textgapRating;
    }

    /**
     * Set matchcondition.
     *
     * @param int $matchcondition
     *
     * @return QplQstEssay
     */
    public function setMatchcondition($matchcondition)
    {
        $this->matchcondition = $matchcondition;

        return $this;
    }

    /**
     * Get matchcondition.
     *
     * @return int
     */
    public function getMatchcondition()
    {
        return $this->matchcondition;
    }

    /**
     * Set keywordRelation.
     *
     * @param string $keywordRelation
     *
     * @return QplQstEssay
     */
    public function setKeywordRelation($keywordRelation)
    {
        $this->keywordRelation = $keywordRelation;

        return $this;
    }

    /**
     * Get keywordRelation.
     *
     * @return string
     */
    public function getKeywordRelation()
    {
        return $this->keywordRelation;
    }

    /**
     * Set wordCntEnabled.
     *
     * @param bool|null $wordCntEnabled
     *
     * @return QplQstEssay
     */
    public function setWordCntEnabled($wordCntEnabled = null)
    {
        $this->wordCntEnabled = $wordCntEnabled;

        return $this;
    }

    /**
     * Get wordCntEnabled.
     *
     * @return bool|null
     */
    public function getWordCntEnabled()
    {
        return $this->wordCntEnabled;
    }
}
