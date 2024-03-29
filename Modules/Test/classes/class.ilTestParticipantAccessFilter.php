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

/**
 * Class ilTestParticipantAccessFilter
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test
 */
class ilTestParticipantAccessFilter
{
    public const FILTER_MANAGE_PARTICIPANTS = 'manageParticipantsUserFilter';
    public const FILTER_SCORE_PARTICIPANTS = 'scoreParticipantsUserFilter';
    public const FILTER_ACCESS_RESULTS = 'accessResultsUserFilter';
    public const FILTER_ACCESS_STATISTICS = 'accessStatisticsUserFilter';

    public const CALLBACK_METHOD = 'filterCallback';

    /**
     * @var integer
     */
    protected $refId;

    /**
     * @var string
     */
    protected $filter;

    /**
     * @return int
     */
    public function getRefId(): int
    {
        return $this->refId;
    }

    /**
     * @param int $refId
     */
    public function setRefId($refId)
    {
        $this->refId = $refId;
    }

    /**
     * @return string
     */
    public function getFilter(): string
    {
        return $this->filter;
    }

    /**
     * @param string $filter
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * @param int[] $userIds
     * @return int[]
     */
    public function filterCallback($userIds): array
    {
        switch ($this->getFilter()) {
            case self::FILTER_MANAGE_PARTICIPANTS:
                return $this->manageParticipantsUserFilter($userIds);

            case self::FILTER_SCORE_PARTICIPANTS:
                return $this->scoreParticipantsUserFilter($userIds);

            case self::FILTER_ACCESS_RESULTS:
                return $this->accessResultsUserFilter($userIds);

            case self::FILTER_ACCESS_STATISTICS:
                return $this->accessStatisticsUserFilter($userIds);
        }

        require_once 'Modules/Test/exceptions/class.ilTestException.php';
        throw new ilTestException('invalid user access filter mode chosen: ' . $this->getFilter());
    }

    /**
     * @param int[] $userIds
     * @return int[]
     */
    public function manageParticipantsUserFilter($userIds): array
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $userIds = $DIC->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
            'write',
            ilOrgUnitOperation::OP_MANAGE_PARTICIPANTS,
            $this->getRefId(),
            $userIds
        );

        return $userIds;
    }

    /**
     * @param int[] $userIds
     * @return int[]
     */
    public function scoreParticipantsUserFilter($userIds): array
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $userIds = $DIC->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
            'write',
            ilOrgUnitOperation::OP_SCORE_PARTICIPANTS,
            $this->getRefId(),
            $userIds
        );

        return $userIds;
    }

    /**
     * @param int[] $userIds
     * @return int[]
     */
    public function accessResultsUserFilter($userIds): array
    {
        /** @var ILIAS\DI\Container $DIC **/
        global $DIC;

        $ref_id = $this->getRefId();

        $perm = 'write';

        if ($DIC->access()->checkAccess('tst_results', '', $ref_id, 'tst')) {
            $perm = 'tst_results';
        }

        $userIds = $DIC->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
            $perm,
            ilOrgUnitOperation::OP_ACCESS_RESULTS,
            $ref_id,
            $userIds
        );

        return $userIds;
    }

    /**
     * @param int[] $userIds
     * @return int[]
     */
    public function accessStatisticsUserFilter($userIds): array
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        if ($DIC->access()->checkAccess('tst_statistics', '', $this->getRefId())) {
            return $userIds;
        }

        return $this->accessResultsUserFilter($userIds);
    }

    /**
     * @param integer $refId
     * @return callable
     */
    public static function getManageParticipantsUserFilter($refId)
    {
        $filter = new self();
        $filter->setFilter(self::FILTER_MANAGE_PARTICIPANTS);
        $filter->setRefId($refId);
        return [$filter, self::CALLBACK_METHOD];
    }

    /**
     * @param integer $refId
     * @return callable
     */
    public static function getScoreParticipantsUserFilter($refId)
    {
        $filter = new self();
        $filter->setFilter(self::FILTER_SCORE_PARTICIPANTS);
        $filter->setRefId($refId);
        return [$filter, self::CALLBACK_METHOD];
    }

    /**
     * @param integer $refId
     * @return callable
     */
    public static function getAccessResultsUserFilter($refId)
    {
        $filter = new self();
        $filter->setFilter(self::FILTER_ACCESS_RESULTS);
        $filter->setRefId($refId);
        return [$filter, self::CALLBACK_METHOD];
    }

    /**
     * @param integer $refId
     * @return callable
     */
    public static function getAccessStatisticsUserFilter($refId)
    {
        $filter = new self();
        $filter->setFilter(self::FILTER_ACCESS_STATISTICS);
        $filter->setRefId($refId);
        return [$filter, self::CALLBACK_METHOD];
    }
}
