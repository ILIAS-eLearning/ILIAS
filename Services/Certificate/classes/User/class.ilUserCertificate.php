<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserCertificate
{
    private int $patternCertificateId;
    private int $objId;
    private string $objType;
    private int $userId;
    private string $userName;
    private int $acquiredTimestamp;
    private string $certificateContent;
    private string $templateValues;
    private int $validUntil;
    private string $version;
    private string $iliasVersion;
    private bool $currentlyActive;
    private ?int $id;
    private ?string $backgroundImagePath;
    private ?string $thumbnailImagePath;

    public function __construct(
        int $patternCertificateId,
        int $objId,
        string $objType,
        int $userId,
        string $userName,
        int $acquiredTimestamp,
        string $certificateContent,
        string $templateValues,
        int $validUntil,
        string $version,
        string $iliasVersion,
        bool $currentlyActive,
        ?string $backgroundImagePath = null,
        ?string $thumbnailImagePath = null,
        ?int $id = null
    ) {
        $this->patternCertificateId = $patternCertificateId;
        $this->objId = $objId;
        $this->objType = $objType;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->acquiredTimestamp = $acquiredTimestamp;
        $this->certificateContent = $certificateContent;
        $this->templateValues = $templateValues;
        $this->validUntil = $validUntil;
        $this->version = $version;
        $this->iliasVersion = $iliasVersion;
        $this->currentlyActive = $currentlyActive;
        $this->backgroundImagePath = $backgroundImagePath;
        $this->thumbnailImagePath = $thumbnailImagePath;
        $this->id = $id;
    }

    public function withId(int $id) : self
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function withVersion(int $version) : self
    {
        $clone = clone $this;
        $clone->version = (string) $version;

        return $clone;
    }

    public function getPatternCertificateId() : int
    {
        return $this->patternCertificateId;
    }

    public function getObjId() : int
    {
        return $this->objId;
    }

    public function getObjType() : string
    {
        return $this->objType;
    }

    public function getUserId() : int
    {
        return $this->userId;
    }

    public function getUserName() : string
    {
        return $this->userName;
    }

    public function getAcquiredTimestamp() : int
    {
        return $this->acquiredTimestamp;
    }

    public function getCertificateContent() : string
    {
        return $this->certificateContent;
    }

    public function getTemplateValues() : string
    {
        return $this->templateValues;
    }

    public function getValidUntil() : int
    {
        return $this->validUntil;
    }

    public function getVersion() : string
    {
        return $this->version;
    }

    public function getIliasVersion() : string
    {
        return $this->iliasVersion;
    }

    public function isCurrentlyActive() : bool
    {
        return $this->currentlyActive;
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getBackgroundImagePath() : ?string
    {
        return $this->backgroundImagePath;
    }

    public function getThumbnailImagePath() : string
    {
        return $this->thumbnailImagePath;
    }
}
