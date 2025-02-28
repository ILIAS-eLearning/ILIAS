<?php

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

declare(strict_types=1);

use ILIAS\Certificate\ValueObject\CertificateId;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserCertificate
{
    private readonly int $validUntil;
    private readonly ?string $backgroundImageIdentification;
    private readonly ?string $tile_image_identification;
    private readonly ?string $backgroundImagePath;
    private readonly ?string $tile_image_path;

    public function __construct(
        private readonly int $patternCertificateId,
        private readonly int $objId,
        private readonly string $objType,
        private readonly int $userId,
        private readonly string $userName,
        private readonly int $acquiredTimestamp,
        private readonly string $certificateContent,
        private readonly string $templateValues,
        ?int $validUntil,
        private int $version,
        private readonly string $iliasVersion,
        private readonly bool $currentlyActive,
        private readonly CertificateId $certificate_id,
        ?string $backgroundImagePath = null,
        ?string $tile_image_path = null,
        ?string $backgroundImageIdentification = null,
        ?string $tile_image_identification = null,
        private ?int $id = null
    ) {
        $this->validUntil = (int) $validUntil;
        $this->backgroundImagePath = (string) $backgroundImagePath;
        $this->tile_image_path = (string) $tile_image_path;
        $this->backgroundImageIdentification = (string) $backgroundImageIdentification;
        $this->tile_image_identification = (string) $tile_image_identification;
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

    public function getBackgroundImagePath(): string
    {
        return $this->backgroundImagePath;
    }

    public function getBackgroundImageIdentification(): string
    {
        return $this->backgroundImageIdentification;
    }

    public function getCurrentBackgroundImageUsed(): string
    {
        if ($this->getBackgroundImageIdentification() === '' || $this->getBackgroundImageIdentification() === '-') {
            return $this->getBackgroundImagePath();
        }
        return $this->getBackgroundImageIdentification();
    }

    public function getTileImagePath(): string
    {
        return $this->tile_image_path;
    }

    public function getTileImageIdentification(): string
    {
        return $this->tile_image_identification;
    }

    public function getCurrentTileImageUsed(): string
    {
        if ($this->getTileImageIdentification() === '' || $this->getTileImageIdentification() === '-') {
            return $this->getTileImagePath();
        }
        return $this->getTileImageIdentification();
    }

    public function getCertificateId(): CertificateId
    {
        return $this->certificate_id;
    }
}
