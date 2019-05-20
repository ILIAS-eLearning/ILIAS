<?php



/**
 * IlCertTemplate
 */
class IlCertTemplate
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string
     */
    private $objType = '';

    /**
     * @var string
     */
    private $certificateContent;

    /**
     * @var string
     */
    private $certificateHash;

    /**
     * @var string
     */
    private $templateValues;

    /**
     * @var string|null
     */
    private $backgroundImagePath;

    /**
     * @var string
     */
    private $version = 'v1';

    /**
     * @var string
     */
    private $iliasVersion = 'v5.4.0';

    /**
     * @var int
     */
    private $createdTimestamp = '0';

    /**
     * @var bool
     */
    private $currentlyActive = '0';

    /**
     * @var bool
     */
    private $deleted = '0';

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
     * Set objId.
     *
     * @param int $objId
     *
     * @return IlCertTemplate
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
     * @return IlCertTemplate
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
     * Set certificateContent.
     *
     * @param string $certificateContent
     *
     * @return IlCertTemplate
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
     * Set certificateHash.
     *
     * @param string $certificateHash
     *
     * @return IlCertTemplate
     */
    public function setCertificateHash($certificateHash)
    {
        $this->certificateHash = $certificateHash;

        return $this;
    }

    /**
     * Get certificateHash.
     *
     * @return string
     */
    public function getCertificateHash()
    {
        return $this->certificateHash;
    }

    /**
     * Set templateValues.
     *
     * @param string $templateValues
     *
     * @return IlCertTemplate
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
     * Set backgroundImagePath.
     *
     * @param string|null $backgroundImagePath
     *
     * @return IlCertTemplate
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
     * @return IlCertTemplate
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
     * @return IlCertTemplate
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
     * Set createdTimestamp.
     *
     * @param int $createdTimestamp
     *
     * @return IlCertTemplate
     */
    public function setCreatedTimestamp($createdTimestamp)
    {
        $this->createdTimestamp = $createdTimestamp;

        return $this;
    }

    /**
     * Get createdTimestamp.
     *
     * @return int
     */
    public function getCreatedTimestamp()
    {
        return $this->createdTimestamp;
    }

    /**
     * Set currentlyActive.
     *
     * @param bool $currentlyActive
     *
     * @return IlCertTemplate
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
     * Set deleted.
     *
     * @param bool $deleted
     *
     * @return IlCertTemplate
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted.
     *
     * @return bool
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set thumbnailImagePath.
     *
     * @param string|null $thumbnailImagePath
     *
     * @return IlCertTemplate
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
