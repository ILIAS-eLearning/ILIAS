<?php declare(strict_types=1);

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

namespace ILIAS\Certificate\API\Data;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class UserCertificateDto
{
    private string $objectTitle = '';
    /** @var int[] */
    private array $objectRefIds = [];
    private int $objectId = 0;
    private int $issuedOnTimestamp = 0;
    private int $userId = 0;
    private string $downloadLink = '';
    private int $certificateId = 0;
    private string $userFirstName = '';
    private string $userLastName = '';
    private string $userLogin = '';
    private string $userEmail = '';
    private string $userSecondEmail = '';

    public function __construct(
        int $certificateId,
        string $objectTitle,
        int $objectId,
        int $issuedOnTimestamp,
        int $userId,
        string $userFirstName,
        string $userLastName,
        string $userLogin,
        string $userEmail,
        string $userSecondEmail,
        array $objectRefId = [],
        ?string $downloadLink = null
    ) {
        $this->certificateId = $certificateId;
        $this->objectTitle = $objectTitle;
        $this->objectRefIds = $objectRefId;
        $this->objectId = $objectId;
        $this->issuedOnTimestamp = $issuedOnTimestamp;
        $this->userId = $userId;
        $this->downloadLink = (string) $downloadLink;
        $this->userFirstName = $userFirstName;
        $this->userLastName = $userLastName;
        $this->userLogin = $userLogin;
        $this->userEmail = $userEmail;
        $this->userSecondEmail = $userSecondEmail;
    }

    public function getObjectTitle() : string
    {
        return $this->objectTitle;
    }

    public function getObjectId() : int
    {
        return $this->objectId;
    }

    public function getIssuedOnTimestamp() : int
    {
        return $this->issuedOnTimestamp;
    }

    public function getUserId() : int
    {
        return $this->userId;
    }

    public function getDownloadLink() : string
    {
        return $this->downloadLink;
    }

    public function getCertificateId() : int
    {
        return $this->certificateId;
    }

    /**
     * @return int[]
     */
    public function getObjectRefIds() : array
    {
        return $this->objectRefIds;
    }

    public function getUserFirstName() : string
    {
        return $this->userFirstName;
    }

    public function getUserLastName() : string
    {
        return $this->userLastName;
    }

    public function getUserLogin() : string
    {
        return $this->userLogin;
    }

    public function getUserEmail() : string
    {
        return $this->userEmail;
    }

    public function addRefId(int $refId) : void
    {
        $this->objectRefIds[] = $refId;
    }

    public function getUserSecondEmail() : string
    {
        return $this->userSecondEmail;
    }
}
