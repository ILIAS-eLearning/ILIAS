<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */
namespace Certificate\API\Filter;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class UserDataFilter
{
    /**
     * @var string
     */
    private $objectTitle;
    /**
     * @var int
     */
    private $objectId;
    /**
     * @var int
     */
    private $issuedBeforeTimestamp;
    /**
     * @var int|null
     */
    private $issuedAfterTimestamp;
    /**
     * @var bool
     */
    private $onlyActive;
    /**
     * @var string
     */
    private $userFirstName;
    /**
     * @var string
     */
    private $userLastName;
    /**
     * @var string
     */
    private $userLogin;
    /**
     * @var string
     */
    private $userEmail;
    /**
     * @var array
     */
    private $userIds;

    /**
     * @param array       $userIds
     * @param string      $userFirstName
     * @param string      $userLastName
     * @param string      $userLogin
     * @param string      $userEmail - matches on the first and the second email address
     * @param string|null $objectTitle
     * @param int|null    $objectId
     * @param int|null    $issuedBeforeTimestamp
     * @param int|null    $issuedAfterTimestamp
     * @param bool        $onlyActive - Show only the currently active certificates of the user
     * @throws \ilException
     */
    public function __construct(
        array $userIds,
        string $userFirstName = null,
        string $userLastName = null,
        string $userLogin = null,
        string $userEmail = null,
        string $objectTitle = null,
        int $objectId = null,
        int $issuedBeforeTimestamp = null,
        int $issuedAfterTimestamp = null,
        bool $onlyActive = true
    ) {
        if (array() === $userIds) {
            throw new \ilException('The array of user ids is empty');
        }

        $this->userIds = $userIds;
        $this->userFirstName = $userFirstName;
        $this->userLastName  = $userLastName;
        $this->userLogin     = $userLogin;
        $this->userEmail     = $userEmail;

        $this->objectTitle           = $objectTitle;
        $this->objectId              = $objectId;
        $this->issuedBeforeTimestamp = $issuedBeforeTimestamp;
        $this->issuedAfterTimestamp  = $issuedAfterTimestamp;
        $this->onlyActive            = $onlyActive;
    }

    /**
     * @return string
     */
    public function getObjectTitle() : ?string
    {
        return $this->objectTitle;
    }

    /**
     * @return int
     */
    public function getObjectId() : ?int
    {
        return $this->objectId;
    }

    /**
     * @return int
     */
    public function getIssuedBeforeTimestamp() : ?int
    {
        return $this->issuedBeforeTimestamp;
    }

    /**
     * @return int|null
     */
    public function getIssuedAfterTimestamp() : ?int
    {
        return $this->issuedAfterTimestamp;
    }

    /**
     * @return bool
     */
    public function isOnlyActive() : ?bool
    {
        return $this->onlyActive;
    }

    /**
     * @return string
     */
    public function getUserFirstName() : ?string
    {
        return $this->userFirstName;
    }

    /**
     * @return string
     */
    public function getUserLastName() : ?string
    {
        return $this->userLastName;
    }

    /**
     * @return string
     */
    public function getUserLogin() : ?string
    {
        return $this->userLogin;
    }

    /**
     * @return string
     */
    public function getUserEmail() : ?string
    {
        return $this->userEmail;
    }

    /**
     * @return array
     */
    public function getUserIds() : array
    {
        return $this->userIds;
    }
}
