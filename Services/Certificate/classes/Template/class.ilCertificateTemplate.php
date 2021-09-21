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
    private int $version;
    private string $iliasVersion;
    private int $createdTimestamp;
    private bool $currentlyActive;
    private ?int $id;
    private string $backgroundImagePath;
    private string $obj_type;
    private bool $deleted;
    private string $thumbnailImagePath;

    public function __construct(
        int $obj_id,
        string $obj_type,
        string $certificateContent,
        string $certificateHash,
        string $templateValues,
        int $version,
        string $iliasVersion,
        int $createdTimestamp,
        bool $currentlyActive,
        string $backgroundImagePath = '',
        string $thumbnailImagePath = '',
        ?int $id = null,
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
        $this->backgroundImagePath = $backgroundImagePath;
        $this->thumbnailImagePath = $thumbnailImagePath;
        $this->id = $id;
        $this->deleted = $deleted;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function getCertificateContent() : string
    {
        return $this->certificateContent;
    }

    public function getCertificateHash() : string
    {
        return $this->certificateHash;
    }

    public function getTemplateValues() : string
    {
        return $this->templateValues;
    }

    public function getVersion() : int
    {
        return $this->version;
    }

    public function getIliasVersion() : string
    {
        return $this->iliasVersion;
    }

    public function getCreatedTimestamp() : int
    {
        return $this->createdTimestamp;
    }

    public function isCurrentlyActive() : bool
    {
        return $this->currentlyActive;
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getBackgroundImagePath() : string
    {
        return $this->backgroundImagePath;
    }

    public function getObjType() : string
    {
        return $this->obj_type;
    }

    public function isDeleted() : bool
    {
        return $this->deleted;
    }

    public function getThumbnailImagePath() : string
    {
        return $this->thumbnailImagePath;
    }
}
