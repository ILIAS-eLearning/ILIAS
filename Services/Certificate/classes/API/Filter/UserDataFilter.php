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
    const SORT_DIRECTION_ASC = 1;
    const SORT_DIRECTION_DESC = 2;

    /** @var string|null */
    private $objectTitle;

    /** @var int|null */
    private $objectId;

    /** @var int|null */
    private $issuedBeforeTimestamp;

    /** @var int|null */
    private $issuedAfterTimestamp;

    /** @var bool */
    private $onlyActive = true;

    /** @var string|null */
    private $userFirstName;

    /** @var string|null */
    private $userLastName;

    /** @var string|null */
    private $userLogin;

    /** @var string|null */
    private $userEmail;

    /** @var int[] */
    private $userIds = [];

    /** @var array */
    private $sorts = [];

    /**
     * @param int[] $usrIds
     * @param bool $onlyActive Show only the currently active certificates of the user
     * @throws \ilException
     */
    public function __construct(
        array $usrIds,
        bool $onlyActive = true
    ) {
        $this->ensureValidUniqueUsrIds($usrIds);

        $this->userIds = $usrIds;
        $this->onlyActive = $onlyActive;
    }

    /**
     * @param int[] $usrIds
     * @throws \ilException
     */
    private function ensureValidUniqueUsrIds(array $usrIds) : void
    {
        if ([] === $usrIds) {
            throw new \ilException('The passed array of user ids must not be empty!');
        }

        array_walk($usrIds, function (int $usrId) {
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
     * @param int|null $objId
     * @return $this
     */
    public function withObjectId(?int $objId) : self
    {
        $clone = clone $this;
        $clone->objectId = $objId;

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
    public function withOnlyActive(bool $status) : self
    {
        $clone = clone $this;
        $clone->onlyActive = $status;

        return $clone;
    }

    /**
     * @param int[] $usrIds
     * @return $this
     * @throws \ilException
     */
    public function withUserIds(array $usrIds) : self
    {
        $this->ensureValidUniqueUsrIds($usrIds);

        $clone = clone $this;
        $clone->userIds = $usrIds;

        return $clone;
    }

    /**
     * @param int[] $usrIds
     * @return $this
     * @throws \ilException
     */
    public function withAdditionalUserIds(array $usrIds) : self
    {
        $this->ensureValidUniqueUsrIds($usrIds);

        $clone = clone $this;
        $clone->userIds = array_unique(array_merge($clone->userIds, $usrIds));

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
    public function isOnlyActive() : bool
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
     * @return int[]
     */
    public function getUserIds() : array
    {
        return $this->userIds;
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
     * @return array
     */
    public function getSorts() : array
    {
        return $this->sorts;
    }
}
