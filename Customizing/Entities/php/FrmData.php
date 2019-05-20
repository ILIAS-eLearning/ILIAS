<?php



/**
 * FrmData
 */
class FrmData
{
    /**
     * @var int
     */
    private $topPk = '0';

    /**
     * @var int
     */
    private $topFrmFk = '0';

    /**
     * @var string|null
     */
    private $topName;

    /**
     * @var string|null
     */
    private $topDescription;

    /**
     * @var int
     */
    private $topNumPosts = '0';

    /**
     * @var int
     */
    private $topNumThreads = '0';

    /**
     * @var string|null
     */
    private $topLastPost;

    /**
     * @var int
     */
    private $topMods = '0';

    /**
     * @var \DateTime|null
     */
    private $topDate;

    /**
     * @var int
     */
    private $visits = '0';

    /**
     * @var \DateTime|null
     */
    private $topUpdate;

    /**
     * @var int
     */
    private $updateUser = '0';

    /**
     * @var int
     */
    private $topUsrId = '0';


    /**
     * Get topPk.
     *
     * @return int
     */
    public function getTopPk()
    {
        return $this->topPk;
    }

    /**
     * Set topFrmFk.
     *
     * @param int $topFrmFk
     *
     * @return FrmData
     */
    public function setTopFrmFk($topFrmFk)
    {
        $this->topFrmFk = $topFrmFk;

        return $this;
    }

    /**
     * Get topFrmFk.
     *
     * @return int
     */
    public function getTopFrmFk()
    {
        return $this->topFrmFk;
    }

    /**
     * Set topName.
     *
     * @param string|null $topName
     *
     * @return FrmData
     */
    public function setTopName($topName = null)
    {
        $this->topName = $topName;

        return $this;
    }

    /**
     * Get topName.
     *
     * @return string|null
     */
    public function getTopName()
    {
        return $this->topName;
    }

    /**
     * Set topDescription.
     *
     * @param string|null $topDescription
     *
     * @return FrmData
     */
    public function setTopDescription($topDescription = null)
    {
        $this->topDescription = $topDescription;

        return $this;
    }

    /**
     * Get topDescription.
     *
     * @return string|null
     */
    public function getTopDescription()
    {
        return $this->topDescription;
    }

    /**
     * Set topNumPosts.
     *
     * @param int $topNumPosts
     *
     * @return FrmData
     */
    public function setTopNumPosts($topNumPosts)
    {
        $this->topNumPosts = $topNumPosts;

        return $this;
    }

    /**
     * Get topNumPosts.
     *
     * @return int
     */
    public function getTopNumPosts()
    {
        return $this->topNumPosts;
    }

    /**
     * Set topNumThreads.
     *
     * @param int $topNumThreads
     *
     * @return FrmData
     */
    public function setTopNumThreads($topNumThreads)
    {
        $this->topNumThreads = $topNumThreads;

        return $this;
    }

    /**
     * Get topNumThreads.
     *
     * @return int
     */
    public function getTopNumThreads()
    {
        return $this->topNumThreads;
    }

    /**
     * Set topLastPost.
     *
     * @param string|null $topLastPost
     *
     * @return FrmData
     */
    public function setTopLastPost($topLastPost = null)
    {
        $this->topLastPost = $topLastPost;

        return $this;
    }

    /**
     * Get topLastPost.
     *
     * @return string|null
     */
    public function getTopLastPost()
    {
        return $this->topLastPost;
    }

    /**
     * Set topMods.
     *
     * @param int $topMods
     *
     * @return FrmData
     */
    public function setTopMods($topMods)
    {
        $this->topMods = $topMods;

        return $this;
    }

    /**
     * Get topMods.
     *
     * @return int
     */
    public function getTopMods()
    {
        return $this->topMods;
    }

    /**
     * Set topDate.
     *
     * @param \DateTime|null $topDate
     *
     * @return FrmData
     */
    public function setTopDate($topDate = null)
    {
        $this->topDate = $topDate;

        return $this;
    }

    /**
     * Get topDate.
     *
     * @return \DateTime|null
     */
    public function getTopDate()
    {
        return $this->topDate;
    }

    /**
     * Set visits.
     *
     * @param int $visits
     *
     * @return FrmData
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
     * Set topUpdate.
     *
     * @param \DateTime|null $topUpdate
     *
     * @return FrmData
     */
    public function setTopUpdate($topUpdate = null)
    {
        $this->topUpdate = $topUpdate;

        return $this;
    }

    /**
     * Get topUpdate.
     *
     * @return \DateTime|null
     */
    public function getTopUpdate()
    {
        return $this->topUpdate;
    }

    /**
     * Set updateUser.
     *
     * @param int $updateUser
     *
     * @return FrmData
     */
    public function setUpdateUser($updateUser)
    {
        $this->updateUser = $updateUser;

        return $this;
    }

    /**
     * Get updateUser.
     *
     * @return int
     */
    public function getUpdateUser()
    {
        return $this->updateUser;
    }

    /**
     * Set topUsrId.
     *
     * @param int $topUsrId
     *
     * @return FrmData
     */
    public function setTopUsrId($topUsrId)
    {
        $this->topUsrId = $topUsrId;

        return $this;
    }

    /**
     * Get topUsrId.
     *
     * @return int
     */
    public function getTopUsrId()
    {
        return $this->topUsrId;
    }
}
