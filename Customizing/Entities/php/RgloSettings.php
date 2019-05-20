<?php



/**
 * RgloSettings
 */
class RgloSettings
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $mid = '0';

    /**
     * @var string|null
     */
    private $organization;

    /**
     * @var string|null
     */
    private $localInformation;

    /**
     * @var string|null
     */
    private $remoteLink;

    /**
     * @var bool
     */
    private $availabilityType = '0';


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
     * Set mid.
     *
     * @param int $mid
     *
     * @return RgloSettings
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
     * @return RgloSettings
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

    /**
     * Set localInformation.
     *
     * @param string|null $localInformation
     *
     * @return RgloSettings
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
     * Set remoteLink.
     *
     * @param string|null $remoteLink
     *
     * @return RgloSettings
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
     * Set availabilityType.
     *
     * @param bool $availabilityType
     *
     * @return RgloSettings
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
}
