<?php



/**
 * RemoteCourseSettings
 */
class RemoteCourseSettings
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $localInformation;

    /**
     * @var bool
     */
    private $availabilityType = '0';

    /**
     * @var int
     */
    private $rStart = '0';

    /**
     * @var int
     */
    private $rEnd = '0';

    /**
     * @var string|null
     */
    private $remoteLink;

    /**
     * @var int
     */
    private $mid = '0';

    /**
     * @var string|null
     */
    private $organization;


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
     * Set localInformation.
     *
     * @param string|null $localInformation
     *
     * @return RemoteCourseSettings
     */
    public function setLocalInformation($localInformation = null)
    {
        $this->localInformation = $localInformation;

        return $this;
    }

    /**
     * Get localInformation.
     *
     * @return string|null
     */
    public function getLocalInformation()
    {
        return $this->localInformation;
    }

    /**
     * Set availabilityType.
     *
     * @param bool $availabilityType
     *
     * @return RemoteCourseSettings
     */
    public function setAvailabilityType($availabilityType)
    {
        $this->availabilityType = $availabilityType;

        return $this;
    }

    /**
     * Get availabilityType.
     *
     * @return bool
     */
    public function getAvailabilityType()
    {
        return $this->availabilityType;
    }

    /**
     * Set rStart.
     *
     * @param int $rStart
     *
     * @return RemoteCourseSettings
     */
    public function setRStart($rStart)
    {
        $this->rStart = $rStart;

        return $this;
    }

    /**
     * Get rStart.
     *
     * @return int
     */
    public function getRStart()
    {
        return $this->rStart;
    }

    /**
     * Set rEnd.
     *
     * @param int $rEnd
     *
     * @return RemoteCourseSettings
     */
    public function setREnd($rEnd)
    {
        $this->rEnd = $rEnd;

        return $this;
    }

    /**
     * Get rEnd.
     *
     * @return int
     */
    public function getREnd()
    {
        return $this->rEnd;
    }

    /**
     * Set remoteLink.
     *
     * @param string|null $remoteLink
     *
     * @return RemoteCourseSettings
     */
    public function setRemoteLink($remoteLink = null)
    {
        $this->remoteLink = $remoteLink;

        return $this;
    }

    /**
     * Get remoteLink.
     *
     * @return string|null
     */
    public function getRemoteLink()
    {
        return $this->remoteLink;
    }

    /**
     * Set mid.
     *
     * @param int $mid
     *
     * @return RemoteCourseSettings
     */
    public function setMid($mid)
    {
        $this->mid = $mid;

        return $this;
    }

    /**
     * Get mid.
     *
     * @return int
     */
    public function getMid()
    {
        return $this->mid;
    }

    /**
     * Set organization.
     *
     * @param string|null $organization
     *
     * @return RemoteCourseSettings
     */
    public function setOrganization($organization = null)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization.
     *
     * @return string|null
     */
    public function getOrganization()
    {
        return $this->organization;
    }
}
