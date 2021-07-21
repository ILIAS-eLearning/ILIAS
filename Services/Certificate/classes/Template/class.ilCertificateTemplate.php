<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplate
{
    private int $obj_id;
    private string $certificateContent;
    private string $certificateHash;
    private string $templateValues;
    private string $version;
    private string $iliasVersion;
    private int $createdTimestamp;
    private bool $currentlyActive;
    private ?int $id;
    private ?string $backgroundImagePath;
    private string $obj_type;
    private bool $deleted;
    private string $thumbnailImagePath;

    /**
     * @param integer $obj_id
     * @param $obj_type
     * @param string $certificateContent
     * @param string $certificateHash
     * @param string $templateValues
     * @param string $version
     * @param string $iliasVersion
     * @param integer $createdTimestamp
     * @param boolean $currentlyActive
     * @param string $backgroundImagePath
     * @param string $thumbnailImagePath
     * @param integer|null $id
     * @param bool $deleted
     */
    public function __construct(
        $obj_id,
        $obj_type,
        $certificateContent,
        $certificateHash,
        $templateValues,
        $version,
        $iliasVersion,
        $createdTimestamp,
        $currentlyActive,
        $backgroundImagePath = '',
        $thumbnailImagePath = '',
        $id = null,
        bool $deleted = false
    ) {
        $this->obj_id = $obj_id;
        $this->obj_type = $obj_type;
        $this->certificateContent = $certificateContent;
        $this->certificateHash = $certificateHash;
        $this->templateValues = $templateValues;
        $this->version = $version;
        $this->iliasVersion = $iliasVersion;
        $this->createdTimestamp = $createdTimestamp;
        $this->currentlyActive = $currentlyActive;
        $this->backgroundImagePath = (string) $backgroundImagePath;
        $this->thumbnailImagePath = (string) $thumbnailImagePath;
        $this->id = $id;
        $this->deleted = $deleted;
    }

    /**
     * @return int
     */
    public function getObjId() : int
    {
        return $this->obj_id;
    }

    /**
     * @return string
     */
    public function getCertificateContent() : string
    {
        return $this->certificateContent;
    }

    /**
     * @return string
     */
    public function getCertificateHash() : string
    {
        return $this->certificateHash;
    }

    /**
     * @return string
     */
    public function getTemplateValues() : string
    {
        return $this->templateValues;
    }

    /**
     * @return string
     */
    public function getVersion() : string
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getIliasVersion() : string
    {
        return $this->iliasVersion;
    }

    /**
     * @return int
     */
    public function getCreatedTimestamp() : int
    {
        return $this->createdTimestamp;
    }

    /**
     * @return bool
     */
    public function isCurrentlyActive() : bool
    {
        return $this->currentlyActive;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getBackgroundImagePath() : string
    {
        return (string) $this->backgroundImagePath;
    }

    /**
     * @return string
     */
    public function getObjType() : string
    {
        return $this->obj_type;
    }

    /**
     * @return bool
     */
    public function isDeleted() : bool
    {
        return $this->deleted;
    }

    /**
     * @return string
     */
    public function getThumbnailImagePath() : string
    {
        return (string) $this->thumbnailImagePath;
    }
}
