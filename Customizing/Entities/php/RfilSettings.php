<?php



/**
 * RfilSettings
 */
class RfilSettings
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
     * @var int
     */
    private $version = '1';

    /**
     * @var int|null
     */
    private $versionTstamp;


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
     * @return RfilSettings
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
     * @return RfilSettings
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
     * @return RfilSettings
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
     * @return RfilSettings
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
     * Set version.
     *
     * @param int $version
     *
     * @return RfilSettings
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version.
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set versionTstamp.
     *
     * @param int|null $versionTstamp
     *
     * @return RfilSettings
     */
    public function setVersionTstamp($versionTstamp = null)
    {
        $this->versionTstamp = $versionTstamp;

        return $this;
    }

    /**
     * Get versionTstamp.
     *
     * @return int|null
     */
    public function getVersionTstamp()
    {
        return $this->versionTstamp;
    }
}
