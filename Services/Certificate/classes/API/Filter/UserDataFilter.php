<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace Certificate\API\Filter;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class UserDataFilter
{
    const SORT_FIELD_ISSUE_TIMESTAMP = 1;
    const SORT_FIELD_USR_LOGIN = 2;
    const SORT_FIELD_USR_LASTNAME = 3;
    const SORT_FIELD_USR_FIRSTNAME = 4;
    const SORT_FIELD_OBJ_TITLE = 5;
    const SORT_FIELD_USR_EMAIL = 6;
    const SORT_DIRECTION_ASC = 1;
    const SORT_DIRECTION_DESC = 2;

    /** @var string|null */
    private $objectTitle;

    /** @var int|null */
    private $issuedBeforeTimestamp;

    /** @var int|null */
    private $issuedAfterTimestamp;

    /** @var bool */
    private $onlyCertActive = true;

    /** @var string|null */
    private $userFirstName;

    /** @var string|null */
    private $userLastName;

    /** @var string|null */
    private $userLogin;

    /** @var string|null */
    private $userEmail;

    /** @var string|null */
    private $userIdentification;

    /** @var int[] */
    private $userIds = [];

    /** @var int[] */
    private $objIds = [];

    /** @var array */
    private $sorts = [];

    /** @var int|null */
    private $limitOffset = null;

    /** @var int|null */
    private $limitCount = null;

    /** @var bool */
    private $shouldIncludeDeletedObjects = true;

    /** @var int[] */
    private $orgUnitIds = [];

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @param int[] $usrIds
     */
    private function ensureValidUniqueUsrIds(array $usrIds) : void
    {
        array_walk($usrIds, function (int $usrId) {
            // Do nothing, use this for type safety of array values
        });
    }

    /**
     * @param int[] $objIds
     */
    private function ensureValidUniqueObjIds(array $objIds) : void
    {
        array_walk($objIds, function (int $objId) {
            // Do nothing, use this for type safety of array values
        });
    }

    /**
     * @param int[] $orgUnitIds
     */
    private function ensureValidUniqueOrgUnitIds(array $orgUnitIds) : void
    {
        array_walk($orgUnitIds, function (int $orgUnitId) {
            // Do nothing, use this for type safety of array values
        });
    }

    /**
     * @param string|null $title
     * @return $this
     */
    public function withObjectTitle(?string $title) : self
    {
        $clone = clone $this;
        $clone->objectTitle = $title;

        return $clone;
    }

    /**
     * @param string|null $firstName
     * @return $this
     */
    public function withUserFirstName(?string $firstName) : self
    {
        $clone = clone $this;
        $clone->userFirstName = $firstName;

        return $clone;
    }

    /**
     * @param string|null $lastName
     * @return $this
     */
    public function withUserLastName(?string $lastName) : self
    {
        $clone = clone $this;
        $clone->userLastName = $lastName;

        return $clone;
    }

    /**
     * @param string|null $login
     * @return $this
     */
    public function withUserLogin(?string $login) : self
    {
        $clone = clone $this;
        $clone->userLogin = $login;

        return $clone;
    }

    /**
     * @param string|null $emailAddress
     * @return $this
     */
    public function withUserEmailAddress(?string $emailAddress) : self
    {
        $clone = clone $this;
        $clone->userEmail = $emailAddress;

        return $clone;
    }

    /**
     * @param string|null $userIdentification
     * @return $this
     */
    public function withUserIdentification(?string $userIdentification) : self
    {
        $clone = clone $this;
        $clone->userIdentification = $userIdentification;

        return $clone;
    }

    /**
     * @param int|null $timestamp
     * @return $this
     */
    public function withIssuedBeforeTimestamp(?int $timestamp) : self
    {
        $clone = clone $this;
        $clone->issuedBeforeTimestamp = $timestamp;

        return $clone;
    }

    /**
     * @param int|null $timestamp
     * @return $this
     */
    public function withIssuedAfterTimestamp(?int $timestamp) : self
    {
        $clone = clone $this;
        $clone->issuedAfterTimestamp = $timestamp;

        return $clone;
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function withOnlyCertActive(bool $status) : self
    {
        $clone = clone $this;
        $clone->onlyCertActive = $status;

        return $clone;
    }

    /**
     * @param int[] $usrIds
     * @return $this
     */
    public function withUserIds(array $usrIds) : self
    {
        $this->ensureValidUniqueUsrIds($usrIds);

        $clone = clone $this;
        $clone->userIds = array_unique($usrIds);

        return $clone;
    }

    /**
     * @param int[] $usrIds
     * @return $this
     */
    public function withAdditionalUserIds(array $usrIds) : self
    {
        $this->ensureValidUniqueUsrIds($usrIds);

        $clone = clone $this;
        $clone->userIds = array_unique(array_merge($clone->userIds, $usrIds));

        return $clone;
    }

    /**
     * @param int[] $objIds
     *
     * @return $this
     */
    public function withObjIds(array $objIds) : self
    {
        $this->ensureValidUniqueObjIds($objIds);

        $clone = clone $this;
        $clone->objIds = array_unique($objIds);

        return $clone;
    }

    /**
     * @param int[] $objIds
     *
     * @return $this
     */
    public function withAdditionalObjIds(array $objIds) : self
    {
        $this->ensureValidUniqueObjIds($objIds);

        $clone = clone $this;
        $clone->objIds = array_unique(array_merge($clone->objIds, $objIds));

        return $clone;
    }

    /**
     * @param int[] $orgUnitIds
     *
     * @return $this
     */
    public function withOrgUnitIds(array $orgUnitIds) : self
    {
        $this->ensureValidUniqueOrgUnitIds($orgUnitIds);

        $clone = clone $this;
        $clone->orgUnitIds = array_unique($orgUnitIds);

        return $clone;
    }

    /**
     * @param int[] $orgUnitIds
     *
     * @return $this
     */
    public function withAdditionalOrgUnitIds(array $orgUnitIds) : self
    {
        $this->ensureValidUniqueOrgUnitIds($orgUnitIds);

        $clone = clone $this;
        $clone->orgUnitIds = array_unique(array_merge($clone->orgUnitIds, $orgUnitIds));

        return $clone;
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
    public function isOnlyCertActive() : bool
    {
        return $this->onlyCertActive;
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
     * @return string
     */
    public function getUserIdentification() : ?string
    {
        return $this->userIdentification;
    }

    /**
     * @return int[]
     */
    public function getUserIds() : array
    {
        return $this->userIds;
    }

    /**
     * @return int[]
     */
    public function getObjIds() : array
    {
        return $this->objIds;
    }

    /**
     * @return int[]
     */
    public function getOrgUnitIds() : array
    {
        return $this->orgUnitIds;
    }

    /**
     * @param int $direction
     *
     * @return $this
     */
    public function withSortedLastNames(int $direction = self::SORT_DIRECTION_ASC) : self
    {
        $clone = clone $this;
        $clone->sorts[] = [self::SORT_FIELD_USR_LASTNAME, $direction];

        return $clone;
    }

    /**
     * @param int $direction
     *
     * @return $this
     */
    public function withSortedFirstNames(int $direction = self::SORT_DIRECTION_ASC) : self
    {
        $clone = clone $this;
        $clone->sorts[] = [self::SORT_FIELD_USR_FIRSTNAME, $direction];

        return $clone;
    }

    /**
     * @param int $direction
     *
     * @return $this
     */
    public function withSortedObjectTitles(int $direction = self::SORT_DIRECTION_ASC) : self
    {
        $clone = clone $this;
        $clone->sorts[] = [self::SORT_FIELD_OBJ_TITLE, $direction];

        return $clone;
    }

    /**
     * @param int $direction
     *
     * @return $this
     */
    public function withSortedLogins(int $direction = self::SORT_DIRECTION_ASC) : self
    {
        $clone = clone $this;
        $clone->sorts[] = [self::SORT_FIELD_USR_LOGIN, $direction];

        return $clone;
    }

    /**
     * @param int $direction
     *
     * @return $this
     */
    public function withSortedIssuedOnTimestamps(int $direction = self::SORT_DIRECTION_ASC) : self
    {
        $clone = clone $this;
        $clone->sorts[] = [self::SORT_FIELD_ISSUE_TIMESTAMP, $direction];

        return $clone;
    }

    /**
     * @param int $direction
     *
     * @return $this
     */
    public function withSortedEmails(int $direction = self::SORT_DIRECTION_ASC) : self
    {
        $clone = clone $this;
        $clone->sorts[] = [self::SORT_FIELD_USR_EMAIL, $direction];

        return $clone;
    }


    /**
     * @return array
     */
    public function getSorts() : array
    {
        return $this->sorts;
    }


    /**
     * @param int|null $limitOffset
     *
     * @return self
     */
    public function withLimitOffset(?int $limitOffset) : self
    {
        $clone = clone $this;
        $clone->limitOffset = $limitOffset;
        return $clone;
    }


    /**
     * @return int|null
     */
    public function getLimitOffset() : ?int
    {
        return $this->limitOffset;
    }


    /**
     * @param int|null $limitCount
     *
     * @return self
     */
    public function withLimitCount(?int $limitCount) : self
    {
        $clone = clone $this;
        $clone->limitCount = $limitCount;
        return $clone;
    }


    /**
     * @return int|null
     */
    public function getLimitCount() : ?int
    {
        return $this->limitCount;
    }


    /**
     * @return self
     */
    public function withShouldIncludeDeletedObjects() : self
    {
        $clone = clone $this;
        $clone->shouldIncludeDeletedObjects = true;

        return $clone;
    }


    /**
     * @return self
     */
    public function withoutShouldIncludeDeletedObjects() : self
    {
        $clone = clone $this;
        $clone->shouldIncludeDeletedObjects = false;

        return $clone;
    }


    /**
     * @return bool
     */
    public function shouldIncludeDeletedObjects() : bool
    {
        return $this->shouldIncludeDeletedObjects;
    }
}
