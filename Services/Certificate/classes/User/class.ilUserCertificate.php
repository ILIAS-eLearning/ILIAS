<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    private int $version;
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
        ?int $validUntil,
        int $version,
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
        $this->validUntil = (int) $validUntil;
        $this->version = $version;
        $this->iliasVersion = $iliasVersion;
        $this->currentlyActive = $currentlyActive;
        $this->backgroundImagePath = (string) $backgroundImagePath;
        $this->thumbnailImagePath = (string) $thumbnailImagePath;
        $this->id = $id;
    }

    public function withId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function withVersion(int $version): self
    {
        $clone = clone $this;
        $clone->version = $version;

        return $clone;
    }

    public function getPatternCertificateId(): int
    {
        return $this->patternCertificateId;
    }

    public function getObjId(): int
    {
        return $this->objId;
    }

    public function getObjType(): string
    {
        return $this->objType;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function getAcquiredTimestamp(): int
    {
        return $this->acquiredTimestamp;
    }

    public function getCertificateContent(): string
    {
        return $this->certificateContent;
    }

    public function getTemplateValues(): string
    {
        return $this->templateValues;
    }

    public function getValidUntil(): int
    {
        return $this->validUntil;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getIliasVersion(): string
    {
        return $this->iliasVersion;
    }

    public function isCurrentlyActive(): bool
    {
        return $this->currentlyActive;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBackgroundImagePath(): ?string
    {
        return $this->backgroundImagePath;
    }

    public function getThumbnailImagePath(): string
    {
        return $this->thumbnailImagePath;
    }
}
