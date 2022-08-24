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

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getCertificateContent(): string
    {
        return $this->certificateContent;
    }

    public function getCertificateHash(): string
    {
        return $this->certificateHash;
    }

    public function getTemplateValues(): string
    {
        return $this->templateValues;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getIliasVersion(): string
    {
        return $this->iliasVersion;
    }

    public function getCreatedTimestamp(): int
    {
        return $this->createdTimestamp;
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

    public function getObjType(): string
    {
        return $this->obj_type;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function getThumbnailImagePath(): string
    {
        return $this->thumbnailImagePath;
    }
}
