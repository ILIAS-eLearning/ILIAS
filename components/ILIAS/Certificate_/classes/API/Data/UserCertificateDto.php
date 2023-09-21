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

namespace ILIAS\Certificate\API\Data;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class UserCertificateDto
{
    private string $downloadLink = '';

    /**
     * @param int[] $objectRefIds
     */
    public function __construct(
        private readonly int $certificateId,
        private readonly string $objectTitle,
        private readonly int $objectId,
        private readonly int $issuedOnTimestamp,
        private readonly int $userId,
        private readonly string $userFirstName,
        private readonly string $userLastName,
        private readonly string $userLogin,
        private readonly string $userEmail,
        private readonly string $userSecondEmail,
        private array $objectRefIds = [],
        ?string $downloadLink = null
    ) {
        $this->downloadLink = (string) $downloadLink;
    }

    public function getObjectTitle(): string
    {
        return $this->objectTitle;
    }

    public function getObjectId(): int
    {
        return $this->objectId;
    }

    public function getIssuedOnTimestamp(): int
    {
        return $this->issuedOnTimestamp;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getDownloadLink(): string
    {
        return $this->downloadLink;
    }

    public function getCertificateId(): int
    {
        return $this->certificateId;
    }

    /**
     * @return int[]
     */
    public function getObjectRefIds(): array
    {
        return $this->objectRefIds;
    }

    public function getUserFirstName(): string
    {
        return $this->userFirstName;
    }

    public function getUserLastName(): string
    {
        return $this->userLastName;
    }

    public function getUserLogin(): string
    {
        return $this->userLogin;
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    public function addRefId(int $refId): void
    {
        $this->objectRefIds[] = $refId;
    }

    public function getUserSecondEmail(): string
    {
        return $this->userSecondEmail;
    }
}
