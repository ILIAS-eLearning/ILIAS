<?php



/**
 * FrmThreads
 */
class FrmThreads
{
    /**
     * @var int
     */
    private $thrPk = '0';

    /**
     * @var int
     */
    private $thrTopFk = '0';

    /**
     * @var string|null
     */
    private $thrSubject;

    /**
     * @var string|null
     */
    private $thrUsrAlias;

    /**
     * @var int
     */
    private $thrNumPosts = '0';

    /**
     * @var string|null
     */
    private $thrLastPost;

    /**
     * @var \DateTime|null
     */
    private $thrDate;

    /**
     * @var \DateTime|null
     */
    private $thrUpdate;

    /**
     * @var int
     */
    private $visits = '0';

    /**
     * @var string|null
     */
    private $importName;

    /**
     * @var bool
     */
    private $isSticky = '0';

    /**
     * @var bool
     */
    private $isClosed = '0';

    /**
     * @var int
     */
    private $threadSorting = '0';

    /**
     * @var float
     */
    private $avgRating = '0';

    /**
     * @var int
     */
    private $thrAuthorId = '0';

    /**
     * @var int
     */
    private $thrDisplayUserId = '0';


    /**
     * Get thrPk.
     *
     * @return int
     */
    public function getThrPk()
    {
        return $this->thrPk;
    }

    /**
     * Set thrTopFk.
     *
     * @param int $thrTopFk
     *
     * @return FrmThreads
     */
    public function setThrTopFk($thrTopFk)
    {
        $this->thrTopFk = $thrTopFk;

        return $this;
    }

    /**
     * Get thrTopFk.
     *
     * @return int
     */
    public function getThrTopFk()
    {
        return $this->thrTopFk;
    }

    /**
     * Set thrSubject.
     *
     * @param string|null $thrSubject
     *
     * @return FrmThreads
     */
    public function setThrSubject($thrSubject = null)
    {
        $this->thrSubject = $thrSubject;

        return $this;
    }

    /**
     * Get thrSubject.
     *
     * @return string|null
     */
    public function getThrSubject()
    {
        return $this->thrSubject;
    }

    /**
     * Set thrUsrAlias.
     *
     * @param string|null $thrUsrAlias
     *
     * @return FrmThreads
     */
    public function setThrUsrAlias($thrUsrAlias = null)
    {
        $this->thrUsrAlias = $thrUsrAlias;

        return $this;
    }

    /**
     * Get thrUsrAlias.
     *
     * @return string|null
     */
    public function getThrUsrAlias()
    {
        return $this->thrUsrAlias;
    }

    /**
     * Set thrNumPosts.
     *
     * @param int $thrNumPosts
     *
     * @return FrmThreads
     */
    public function setThrNumPosts($thrNumPosts)
    {
        $this->thrNumPosts = $thrNumPosts;

        return $this;
    }

    /**
     * Get thrNumPosts.
     *
     * @return int
     */
    public function getThrNumPosts()
    {
        return $this->thrNumPosts;
    }

    /**
     * Set thrLastPost.
     *
     * @param string|null $thrLastPost
     *
     * @return FrmThreads
     */
    public function setThrLastPost($thrLastPost = null)
    {
        $this->thrLastPost = $thrLastPost;

        return $this;
    }

    /**
     * Get thrLastPost.
     *
     * @return string|null
     */
    public function getThrLastPost()
    {
        return $this->thrLastPost;
    }

    /**
     * Set thrDate.
     *
     * @param \DateTime|null $thrDate
     *
     * @return FrmThreads
     */
    public function setThrDate($thrDate = null)
    {
        $this->thrDate = $thrDate;

        return $this;
    }

    /**
     * Get thrDate.
     *
     * @return \DateTime|null
     */
    public function getThrDate()
    {
        return $this->thrDate;
    }

    /**
     * Set thrUpdate.
     *
     * @param \DateTime|null $thrUpdate
     *
     * @return FrmThreads
     */
    public function setThrUpdate($thrUpdate = null)
    {
        $this->thrUpdate = $thrUpdate;

        return $this;
    }

    /**
     * Get thrUpdate.
     *
     * @return \DateTime|null
     */
    public function getThrUpdate()
    {
        return $this->thrUpdate;
    }

    /**
     * Set visits.
     *
     * @param int $visits
     *
     * @return FrmThreads
     */
    public function setVisits($visits)
    {
        $this->visits = $visits;

        return $this;
    }

    /**
     * Get visits.
     *
     * @return int
     */
    public function getVisits()
    {
        return $this->visits;
    }

    /**
     * Set importName.
     *
     * @param string|null $importName
     *
     * @return FrmThreads
     */
    public function setImportName($importName = null)
    {
        $this->importName = $importName;

        return $this;
    }

    /**
     * Get importName.
     *
     * @return string|null
     */
    public function getImportName()
    {
        return $this->importName;
    }

    /**
     * Set isSticky.
     *
     * @param bool $isSticky
     *
     * @return FrmThreads
     */
    public function setIsSticky($isSticky)
    {
        $this->isSticky = $isSticky;

        return $this;
    }

    /**
     * Get isSticky.
     *
     * @return bool
     */
    public function getIsSticky()
    {
        return $this->isSticky;
    }

    /**
     * Set isClosed.
     *
     * @param bool $isClosed
     *
     * @return FrmThreads
     */
    public function setIsClosed($isClosed)
    {
        $this->isClosed = $isClosed;

        return $this;
    }

    /**
     * Get isClosed.
     *
     * @return bool
     */
    public function getIsClosed()
    {
        return $this->isClosed;
    }

    /**
     * Set threadSorting.
     *
     * @param int $threadSorting
     *
     * @return FrmThreads
     */
    public function setThreadSorting($threadSorting)
    {
        $this->threadSorting = $threadSorting;

        return $this;
    }

    /**
     * Get threadSorting.
     *
     * @return int
     */
    public function getThreadSorting()
    {
        return $this->threadSorting;
    }

    /**
     * Set avgRating.
     *
     * @param float $avgRating
     *
     * @return FrmThreads
     */
    public function setAvgRating($avgRating)
    {
        $this->avgRating = $avgRating;

        return $this;
    }

    /**
     * Get avgRating.
     *
     * @return float
     */
    public function getAvgRating()
    {
        return $this->avgRating;
    }

    /**
     * Set thrAuthorId.
     *
     * @param int $thrAuthorId
     *
     * @return FrmThreads
     */
    public function setThrAuthorId($thrAuthorId)
    {
        $this->thrAuthorId = $thrAuthorId;

        return $this;
    }

    /**
     * Get thrAuthorId.
     *
     * @return int
     */
    public function getThrAuthorId()
    {
        return $this->thrAuthorId;
    }

    /**
     * Set thrDisplayUserId.
     *
     * @param int $thrDisplayUserId
     *
     * @return FrmThreads
     */
    public function setThrDisplayUserId($thrDisplayUserId)
    {
        $this->thrDisplayUserId = $thrDisplayUserId;

        return $this;
    }

    /**
     * Get thrDisplayUserId.
     *
     * @return int
     */
    public function getThrDisplayUserId()
    {
        return $this->thrDisplayUserId;
    }
}
