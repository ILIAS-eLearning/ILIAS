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

/**
 * Class ilTestAccess
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components\ILIAS/Test
 */
class ilTestAccess
{
    protected ilAccessHandler $access;
    protected ilDBInterface $db;
    protected ilLanguage $lng;

    protected ilTestParticipantAccessFilterFactory $participant_access_filter;

    public function __construct(
        protected int $ref_id,
        protected int $test_id
    ) {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->db = $DIC['ilDB'];
        $this->lng = $DIC['lng'];
        $this->participant_access_filter = new ilTestParticipantAccessFilterFactory($DIC['ilAccess']);
        $this->setAccess($DIC->access());

        $this->setRefId($ref_id);
        $this->setTestId($test_id);
    }

    public function getAccess(): ilAccessHandler
    {
        return $this->access;
    }

    public function setAccess(ilAccessHandler $access)
    {
        $this->access = $access;
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    public function setRefId(int $ref_id)
    {
        $this->ref_id = $ref_id;
    }

    public function getTestId(): int
    {
        return $this->test_id;
    }

    public function setTestId(int $test_id)
    {
        $this->test_id = $test_id;
    }

    /**
     * @return bool
     */
    public function checkCorrectionsAccess(): bool
    {
        return $this->getAccess()->checkAccess('write', '', $this->getRefId());
    }

    /**
     * @return bool
     */
    public function checkScoreParticipantsAccess(): bool
    {
        if ($this->getAccess()->checkAccess('write', '', $this->getRefId())) {
            return true;
        }

        if ($this->getAccess()->checkPositionAccess(ilOrgUnitOperation::OP_SCORE_PARTICIPANTS, $this->getRefId())) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function checkManageParticipantsAccess(): bool
    {
        if ($this->getAccess()->checkAccess('write', '', $this->getRefId())) {
            return true;
        }

        if ($this->getAccess()->checkPositionAccess(ilOrgUnitOperation::OP_MANAGE_PARTICIPANTS, $this->getRefId())) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function checkParticipantsResultsAccess(): bool
    {
        if ($this->getAccess()->checkAccess('write', '', $this->getRefId())) {
            return true;
        }

        if ($this->getAccess()->checkAccess('tst_results', '', $this->getRefId())) {
            return true;
        }

        if ($this->getAccess()->checkPositionAccess(ilOrgUnitOperation::OP_MANAGE_PARTICIPANTS, $this->getRefId())) {
            return true;
        }

        if ($this->getAccess()->checkPositionAccess(ilOrgUnitOperation::OP_ACCESS_RESULTS, $this->getRefId())) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function checkStatisticsAccess(): bool
    {
        if ($this->getAccess()->checkAccess('tst_statistics', '', $this->getRefId())) {
            return true;
        }

        if ($this->getAccess()->checkPositionAccess(ilOrgUnitOperation::OP_ACCESS_RESULTS, $this->getRefId())) {
            return true;
        }

        return false;
    }

    protected function checkAccessForActiveId(Closure $access_filter, int $active_id): bool
    {
        $participantData = new ilTestParticipantData($this->db, $this->lng);
        $participantData->setActiveIdsFilter(array($active_id));
        $participantData->setParticipantAccessFilter($access_filter);
        $participantData->load($this->getTestId());

        return in_array($active_id, $participantData->getActiveIds());
    }

    public function checkResultsAccessForActiveId(int $active_id): bool
    {
        $access_filter = $this->participant_access_filter->getAccessResultsUserFilter($this->getRefId());
        return $this->checkAccessForActiveId($access_filter, $active_id);
    }

    public function checkScoreParticipantsAccessForActiveId(int $active_id): bool
    {
        $access_filter = $this->participant_access_filter->getScoreParticipantsUserFilter($this->getRefId());
        return $this->checkAccessForActiveId($access_filter, $active_id);
    }

    public function checkStatisticsAccessForActiveId(int $active_id): bool
    {
        $access_filter = $this->participant_access_filter->getAccessStatisticsUserFilter($this->getRefId());
        return $this->checkAccessForActiveId($access_filter, $active_id);
    }
}
