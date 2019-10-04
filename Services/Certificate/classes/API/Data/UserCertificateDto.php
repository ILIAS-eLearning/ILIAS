<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace Certificate\API\Data;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class UserCertificateDto
{
    /** @var string */
    private $objectTitle = '';

    /** @var int[] */
    private $objectRefIds = [];

    /** @var int */
    private $objectId = 0;

    /** @var int */
    private $issuedOnTimestamp = 0;

    /** @var int */
    private $userId = 0;

    /** @var string */
    private $downloadLink = '';

    /** @var int */
    private $certificateId = 0;

    /** @var string */
    private $userFirstName = '';

    /** @var string */
    private $userLastName = '';

    /** @var string */
    private $userLogin = '';

    /** @var string */
    private $userEmail = '';

    /** @var string */
    private $userSecondEmail = '';

    /**
     * @param int $certificateId
     * @param string $objectTitle
     * @param int $objectId
     * @param int $issuedOnTimestamp
     * @param int $userId
     * @param string $userFirstName
     * @param string $userLastName
     * @param string $userLogin
     * @param string $userEmail
     * @param string $userSecondEmail
     * @param array $objectRefId
     * @param string|null $downloadLink
     */
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
        array $objectRefId = array(),
        string $downloadLink = null
    ) {
        $this->certificateId = $certificateId;
        $this->objectTitle = $objectTitle;
        $this->objectRefIds = $objectRefId;
        $this->objectId = $objectId;
        $this->issuedOnTimestamp = $issuedOnTimestamp;
        $this->userId = $userId;
        $this->downloadLink = $downloadLink;

        $this->userFirstName = $userFirstName;
        $this->userLastName = $userLastName;
        $this->userLogin = $userLogin;
        $this->userEmail = $userEmail;
        $this->userSecondEmail = $userSecondEmail;
    }

    /**
     * @return string
     */
    public function getObjectTitle() : string
    {
        return $this->objectTitle;
    }

    /**
     * @return int
     */
    public function getObjectId() : int
    {
        return $this->objectId;
    }

    /**
     * @return int
     */
    public function getIssuedOnTimestamp() : int
    {
        return $this->issuedOnTimestamp;
    }

    /**
     * @return int
     */
    public function getUserId() : int
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getDownloadLink() : string
    {
        return $this->downloadLink;
    }

    /**
     * @return int
     */
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

    /**
     * @return string
     */
    public function getUserFirstName() : string
    {
        return $this->userFirstName;
    }

    /**
     * @return string
     */
    public function getUserLastName() : string
    {
        return $this->userLastName;
    }

    /**
     * @return string
     */
    public function getUserLogin() : string
    {
        return $this->userLogin;
    }

    /**
     * @return string
     */
    public function getUserEmail() : string
    {
        return $this->userEmail;
    }

    /**
     *
     */
    public function addRefId(int $refId) : void
    {
        $this->objectRefIds[] = $refId;
    }

    /**
     * @return string
     */
    public function getUserSecondEmail() : string
    {
        return $this->userSecondEmail;
    }
}
