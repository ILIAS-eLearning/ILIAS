<?php



/**
 * IlCertUserCert
 */
class IlCertUserCert
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $patternCertificateId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string
     */
    private $objType = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var string
     */
    private $userName = '0';

    /**
     * @var int
     */
    private $acquiredTimestamp = '0';

    /**
     * @var string
     */
    private $certificateContent;

    /**
     * @var string
     */
    private $templateValues;

    /**
     * @var int|null
     */
    private $validUntil;

    /**
     * @var string|null
     */
    private $backgroundImagePath;

    /**
     * @var string
     */
    private $version = '1';

    /**
     * @var string
     */
    private $iliasVersion = 'v5.4.0';

    /**
     * @var bool
     */
    private $currentlyActive = '0';

    /**
     * @var string|null
     */
    private $thumbnailImagePath;


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
     * Set patternCertificateId.
     *
     * @param int $patternCertificateId
     *
     * @return IlCertUserCert
     */
    public function setPatternCertificateId($patternCertificateId)
    {
        $this->patternCertificateId = $patternCertificateId;

        return $this;
    }

    /**
     * Get patternCertificateId.
     *
     * @return int
     */
    public function getPatternCertificateId()
    {
        return $this->patternCertificateId;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return IlCertUserCert
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

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
     * Set objType.
     *
     * @param string $objType
     *
     * @return IlCertUserCert
     */
    public function setObjType($objType)
    {
        $this->objType = $objType;

        return $this;
    }

    /**
     * Get objType.
     *
     * @return string
     */
    public function getObjType()
    {
        return $this->objType;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return IlCertUserCert
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set userName.
     *
     * @param string $userName
     *
     * @return IlCertUserCert
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * Get userName.
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Set acquiredTimestamp.
     *
     * @param int $acquiredTimestamp
     *
     * @return IlCertUserCert
     */
    public function setAcquiredTimestamp($acquiredTimestamp)
    {
        $this->acquiredTimestamp = $acquiredTimestamp;

        return $this;
    }

    /**
     * Get acquiredTimestamp.
     *
     * @return int
     */
    public function getAcquiredTimestamp()
    {
        return $this->acquiredTimestamp;
    }

    /**
     * Set certificateContent.
     *
     * @param string $certificateContent
     *
     * @return IlCertUserCert
     */
    public function setCertificateContent($certificateContent)
    {
        $this->certificateContent = $certificateContent;

        return $this;
    }

    /**
     * Get certificateContent.
     *
     * @return string
     */
    public function getCertificateContent()
    {
        return $this->certificateContent;
    }

    /**
     * Set templateValues.
     *
     * @param string $templateValues
     *
     * @return IlCertUserCert
     */
    public function setTemplateValues($templateValues)
    {
        $this->templateValues = $templateValues;

        return $this;
    }

    /**
     * Get templateValues.
     *
     * @return string
     */
    public function getTemplateValues()
    {
        return $this->templateValues;
    }

    /**
     * Set validUntil.
     *
     * @param int|null $validUntil
     *
     * @return IlCertUserCert
     */
    public function setValidUntil($validUntil = null)
    {
        $this->validUntil = $validUntil;

        return $this;
    }

    /**
     * Get validUntil.
     *
     * @return int|null
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    /**
     * Set backgroundImagePath.
     *
     * @param string|null $backgroundImagePath
     *
     * @return IlCertUserCert
     */
    public function setBackgroundImagePath($backgroundImagePath = null)
    {
        $this->backgroundImagePath = $backgroundImagePath;

        return $this;
    }

    /**
     * Get backgroundImagePath.
     *
     * @return string|null
     */
    public function getBackgroundImagePath()
    {
        return $this->backgroundImagePath;
    }

    /**
     * Set version.
     *
     * @param string $version
     *
     * @return IlCertUserCert
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set iliasVersion.
     *
     * @param string $iliasVersion
     *
     * @return IlCertUserCert
     */
    public function setIliasVersion($iliasVersion)
    {
        $this->iliasVersion = $iliasVersion;

        return $this;
    }

    /**
     * Get iliasVersion.
     *
     * @return string
     */
    public function getIliasVersion()
    {
        return $this->iliasVersion;
    }

    /**
     * Set currentlyActive.
     *
     * @param bool $currentlyActive
     *
     * @return IlCertUserCert
     */
    public function setCurrentlyActive($currentlyActive)
    {
        $this->currentlyActive = $currentlyActive;

        return $this;
    }

    /**
     * Get currentlyActive.
     *
     * @return bool
     */
    public function getCurrentlyActive()
    {
        return $this->currentlyActive;
    }

    /**
     * Set thumbnailImagePath.
     *
     * @param string|null $thumbnailImagePath
     *
     * @return IlCertUserCert
     */
    public function setThumbnailImagePath($thumbnailImagePath = null)
    {
        $this->thumbnailImagePath = $thumbnailImagePath;

        return $this;
    }

    /**
     * Get thumbnailImagePath.
     *
     * @return string|null
     */
    public function getThumbnailImagePath()
    {
        return $this->thumbnailImagePath;
    }
}
