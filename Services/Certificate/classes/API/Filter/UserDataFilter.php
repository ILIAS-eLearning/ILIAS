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

namespace Certificate\API\Filter;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class UserDataFilter
{
    public const SORT_FIELD_ISSUE_TIMESTAMP = 1;
    public const SORT_FIELD_USR_LOGIN = 2;
    public const SORT_FIELD_USR_LASTNAME = 3;
    public const SORT_FIELD_USR_FIRSTNAME = 4;
    public const SORT_FIELD_OBJ_TITLE = 5;
    public const SORT_DIRECTION_ASC = 1;
    public const SORT_DIRECTION_DESC = 2;

    private ?string $objectTitle = null;
    private ?int $issuedBeforeTimestamp = null;
    private ?int $issuedAfterTimestamp = null;
    private bool $onlyCertActive = true;
    private ?string $userFirstName = null;
    private ?string $userLastName = null;
    private ?string $userLogin = null;
    private ?string $userEmail = null;
    /** @var int[] */
    private array $userIds = [];
    /** @var int[] */
    private array $objIds = [];
    private array $sorts = [];
    private ?int $limitOffset = null;
    private ?int $limitCount = null;
    private bool $shouldIncludeDeletedObjects = true;

    public function __construct()
    {
    }

    /**
     * @param int[] $usrIds
     */
    private function ensureValidUniqueUsrIds(array $usrIds) : void
    {
        array_walk($usrIds, static function (int $usrId) : void {
            // Do nothing, use this for type safety of array values
        });
    }

    /**
     * @param int[] $objIds
     */
    private function ensureValidUniqueObjIds(array $objIds) : void
    {
        array_walk($objIds, static function (int $objId) : void {
            // Do nothing, use this for type safety of array values
        });
    }

    public function withObjectTitle(?string $title) : self
    {
        $clone = clone $this;
        $clone->objectTitle = $title;

        return $clone;
    }

    public function withUserFirstName(?string $firstName) : self
    {
        $clone = clone $this;
        $clone->userFirstName = $firstName;

        return $clone;
    }

    public function withUserLastName(?string $lastName) : self
    {
        $clone = clone $this;
        $clone->userLastName = $lastName;

        return $clone;
    }

    public function withUserLogin(?string $login) : self
    {
        $clone = clone $this;
        $clone->userLogin = $login;

        return $clone;
    }

    public function withUserEmailAddress(?string $emailAddress) : self
    {
        $clone = clone $this;
        $clone->userEmail = $emailAddress;

        return $clone;
    }

    public function withIssuedBeforeTimestamp(?int $timestamp) : self
    {
        $clone = clone $this;
        $clone->issuedBeforeTimestamp = $timestamp;

        return $clone;
    }

    public function withIssuedAfterTimestamp(?int $timestamp) : self
    {
        $clone = clone $this;
        $clone->issuedAfterTimestamp = $timestamp;

        return $clone;
    }

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
     * @return $this
     */
    public function withAdditionalObjIds(array $objIds) : self
    {
        $this->ensureValidUniqueObjIds($objIds);

        $clone = clone $this;
        $clone->objIds = array_unique(array_merge($clone->objIds, $objIds));

        return $clone;
    }

    public function getObjectTitle() : ?string
    {
        return $this->objectTitle;
    }

    public function getIssuedBeforeTimestamp() : ?int
    {
        return $this->issuedBeforeTimestamp;
    }

    public function getIssuedAfterTimestamp() : ?int
    {
        return $this->issuedAfterTimestamp;
    }

    public function isOnlyCertActive() : bool
    {
        return $this->onlyCertActive;
    }

    public function getUserFirstName() : ?string
    {
        return $this->userFirstName;
    }

    public function getUserLastName() : ?string
    {
        return $this->userLastName;
    }

    public function getUserLogin() : ?string
    {
        return $this->userLogin;
    }

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
     * @return int[]
     */
    public function getObjIds() : array
    {
        return $this->objIds;
    }

    public function withSortedLastNames(int $direction = self::SORT_DIRECTION_ASC) : self
    {
        $clone = clone $this;
        $clone->sorts[] = [self::SORT_FIELD_USR_LASTNAME, $direction];

        return $clone;
    }

    public function withSortedFirstNames(int $direction = self::SORT_DIRECTION_ASC) : self
    {
        $clone = clone $this;
        $clone->sorts[] = [self::SORT_FIELD_USR_FIRSTNAME, $direction];

        return $clone;
    }

    public function withSortedObjectTitles(int $direction = self::SORT_DIRECTION_ASC) : self
    {
        $clone = clone $this;
        $clone->sorts[] = [self::SORT_FIELD_OBJ_TITLE, $direction];

        return $clone;
    }

    public function withSortedLogins(int $direction = self::SORT_DIRECTION_ASC) : self
    {
        $clone = clone $this;
        $clone->sorts[] = [self::SORT_FIELD_USR_LOGIN, $direction];

        return $clone;
    }

    public function withSortedIssuedOnTimestamps(int $direction = self::SORT_DIRECTION_ASC) : self
    {
        $clone = clone $this;
        $clone->sorts[] = [self::SORT_FIELD_ISSUE_TIMESTAMP, $direction];

        return $clone;
    }

    public function getSorts() : array
    {
        return $this->sorts;
    }

    public function withLimitOffset(?int $limitOffset) : self
    {
        $clone = clone $this;
        $clone->limitOffset = $limitOffset;
        return $clone;
    }

    public function getLimitOffset() : ?int
    {
        return $this->limitOffset;
    }

    public function withLimitCount(?int $limitCount) : self
    {
        $clone = clone $this;
        $clone->limitCount = $limitCount;
        return $clone;
    }

    public function getLimitCount() : ?int
    {
        return $this->limitCount;
    }

    public function withShouldIncludeDeletedObjects() : self
    {
        $clone = clone $this;
        $clone->shouldIncludeDeletedObjects = true;

        return $clone;
    }

    public function withoutShouldIncludeDeletedObjects() : self
    {
        $clone = clone $this;
        $clone->shouldIncludeDeletedObjects = false;

        return $clone;
    }

    public function shouldIncludeDeletedObjects() : bool
    {
        return $this->shouldIncludeDeletedObjects;
    }
}
