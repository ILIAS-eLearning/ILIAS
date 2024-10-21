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

use ILIAS\Test\Participants\ParticipantRepository;
use ILIAS\Test\TestDIC;
use ILIAS\Test\Access\ParticipantAccess;
use ILIAS\Test\Settings\MainSettings\MainSettingsDatabaseRepository;
use ILIAS\Test\Settings\MainSettings\SettingsAccess;

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
    protected MainSettingsDatabaseRepository $main_settings_repository;

    protected ilTestParticipantAccessFilterFactory $participant_access_filter;
    protected ParticipantRepository $participant_repository;

    public function __construct(
        protected int $ref_id
    ) {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->db = $DIC['ilDB'];
        $this->lng = $DIC['lng'];
        $this->participant_access_filter = new ilTestParticipantAccessFilterFactory($DIC['ilAccess']);
        $this->participant_repository = TestDIC::dic()['participant.repository'];
        $this->access = $DIC->access();
        $this->main_settings_repository = TestDIC::dic()['settings.main.repository'];
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

    protected function checkAccessForActiveId(Closure $access_filter, int $active_id, int $test_id): bool
    {
        $participantData = new ilTestParticipantData($this->db, $this->lng);
        $participantData->setActiveIdsFilter([$active_id]);
        $participantData->setParticipantAccessFilter($access_filter);
        $participantData->load($test_id);

        return in_array($active_id, $participantData->getActiveIds());
    }

    public function checkResultsAccessForActiveId(int $active_id, int $test_id): bool
    {
        $access_filter = $this->participant_access_filter->getAccessResultsUserFilter($this->getRefId());
        return $this->checkAccessForActiveId($access_filter, $active_id, $test_id);
    }

    public function checkScoreParticipantsAccessForActiveId(int $active_id, int $test_id): bool
    {
        $access_filter = $this->participant_access_filter->getScoreParticipantsUserFilter($this->getRefId());
        return $this->checkAccessForActiveId($access_filter, $active_id, $test_id);
    }

    public function isParticipantAllowed(int $obj_id, int $user_id): ParticipantAccess
    {
        $access_settings = $this->main_settings_repository->getForObjFi($obj_id)
            ->getAccessSettings();

        if (!$access_settings->getFixedParticipants()
            && !$access_settings->isIpRangeEnabled()) {
            return ParticipantAccess::ALLOWED;
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        $allowed_by_individual_ip = $this->isParticipantWithIpAllowedToAccessTest(
            $user_id,
            $ip,
            $access_settings
        );

        if ($allowed_by_individual_ip === true) {
            return ParticipantAccess::ALLOWED;
        }

        if ($allowed_by_individual_ip === false) {
            return ParticipantAccess::INDIVIDUAL_CLIENT_IP_MISMATCH;
        }

        if (!$this->isIpAllowedToAccessTest($ip, $access_settings)) {
            return ParticipantAccess::TEST_LEVEL_CLIENT_IP_MISMATCH;
        }

        return ParticipantAccess::ALLOWED;
    }

    private function isParticipantWithIpAllowedToAccessTest(
        int $user_id,
        string $ip,
        SettingsAccess $access_settings
    ): ?bool {
        $participant = $this->participant_repository->getParticipantByUserId(
            ilObjTest::_getTestIDFromObjectID(
                ilObjTest::_lookupObjId($this->getRefId())
            ),
            $user_id
        );

        if (!$participant) {
            return false;
        }

        $range_start = $participant->getClientIpFrom();
        $range_end = $participant->getClientIpTo();

        if ($range_start === null || $range_end === null) {
            return false;
        }

        if ($this->isIpTypeOf(FILTER_FLAG_IPV4, $ip, $range_start, $range_end)) {
            return $this->isIpv4Between($ip, $range_start, $range_end);
        }

        if ($this->isIpTypeOf(FILTER_FLAG_IPV6, $ip, $range_start, $range_end)) {
            return $this->isIpv6Between($ip, $range_start, $range_end);
        }

        return false;
    }

    private function isIpAllowedToAccessTest(
        string $ip,
        SettingsAccess $access_settings
    ): bool {
        if (!$access_settings->isIpRangeEnabled()) {
            return true;
        }

        $range_start = $access_settings->getIpRangeFrom();
        $range_end = $access_settings->getIpRangeTo();

        if ($this->isIpTypeOf(FILTER_FLAG_IPV4, $ip, $range_start, $range_end)) {
            return $this->isIpv4Between($ip, $range_start, $range_end);
        }

        if ($this->isIpTypeOf(FILTER_FLAG_IPV6, $ip, $range_start, $range_end)) {
            return $this->isIpv6Between($ip, $range_start, $range_end);
        }

        return false;
    }

    private function isIpTypeOf(int $ip_type_flag, string $ip, string $range_start, string $range_end): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, $ip_type_flag) !== false
            && filter_var($range_start, FILTER_VALIDATE_IP, $ip_type_flag) !== false
            && filter_var($range_end, FILTER_VALIDATE_IP, $ip_type_flag) !== false;
    }

    private function isIpv4Between(string $ip, string $range_start, string $range_end): bool
    {
        return ip2long($range_start) <= ip2long($ip)
            && ip2long($ip) <= ip2long($range_end);
    }

    private function isIpv6Between(string $ip, string $range_start, string $range_end): bool
    {
        return bin2hex(inet_pton($range_start)) <= bin2hex(inet_pton($ip))
            && bin2hex(inet_pton($ip)) <= bin2hex(inet_pton($range_end));
    }
}
