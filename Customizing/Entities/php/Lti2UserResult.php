<?php



/**
 * Lti2UserResult
 */
class Lti2UserResult
{
    /**
     * @var int
     */
    private $userPk = '0';

    /**
     * @var int
     */
    private $resourceLinkPk = '0';

    /**
     * @var string
     */
    private $ltiUserId = '';

    /**
     * @var string
     */
    private $ltiResultSourcedid = '';

    /**
     * @var \DateTime
     */
    private $created = '1970-01-01 00:00:00';

    /**
     * @var \DateTime
     */
    private $updated = '1970-01-01 00:00:00';


    /**
     * Get userPk.
     *
     * @return int
     */
    public function getUserPk()
    {
        return $this->userPk;
    }

    /**
     * Set resourceLinkPk.
     *
     * @param int $resourceLinkPk
     *
     * @return Lti2UserResult
     */
    public function setResourceLinkPk($resourceLinkPk)
    {
        $this->resourceLinkPk = $resourceLinkPk;

        return $this;
    }

    /**
     * Get resourceLinkPk.
     *
     * @return int
     */
    public function getResourceLinkPk()
    {
        return $this->resourceLinkPk;
    }

    /**
     * Set ltiUserId.
     *
     * @param string $ltiUserId
     *
     * @return Lti2UserResult
     */
    public function setLtiUserId($ltiUserId)
    {
        $this->ltiUserId = $ltiUserId;

        return $this;
    }

    /**
     * Get ltiUserId.
     *
     * @return string
     */
    public function getLtiUserId()
    {
        return $this->ltiUserId;
    }

    /**
     * Set ltiResultSourcedid.
     *
     * @param string $ltiResultSourcedid
     *
     * @return Lti2UserResult
     */
    public function setLtiResultSourcedid($ltiResultSourcedid)
    {
        $this->ltiResultSourcedid = $ltiResultSourcedid;

        return $this;
    }

    /**
     * Get ltiResultSourcedid.
     *
     * @return string
     */
    public function getLtiResultSourcedid()
    {
        return $this->ltiResultSourcedid;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return Lti2UserResult
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return Lti2UserResult
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }
}
