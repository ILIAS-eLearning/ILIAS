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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestPassesSelector
{
    private ?int $active_id = null;
    private ?int $last_finished_pass = null;
    private ?array $passes = null;
    private array $test_passed_once_cache = [];

    public function __construct(
        private ilDBInterface $db,
        private ilObjTest $test_obj
    ) {
    }

    public function getActiveId(): ?int
    {
        return $this->active_id;
    }

    public function setActiveId(?int $active_id): void
    {
        $this->active_id = $active_id;
    }

    public function getLastFinishedPass(): ?int
    {
        return $this->last_finished_pass;
    }

    public function setLastFinishedPass(?int $last_finished_pass): void
    {
        $this->last_finished_pass = $last_finished_pass === null ? -1 : $last_finished_pass;
    }

    private function passesLoaded(): bool
    {
        return is_array($this->passes);
    }

    private function ensureLoadedPasses()
    {
        if (!$this->passesLoaded()) {
            $this->loadPasses();
        }
    }
    private function loadPasses(): void
    {
        $query = '
			SELECT DISTINCT tst_pass_result.* FROM tst_pass_result
			LEFT JOIN tst_test_result
			ON tst_pass_result.pass = tst_test_result.pass
			AND tst_pass_result.active_fi = tst_test_result.active_fi
			WHERE tst_pass_result.active_fi = %s
			ORDER BY tst_pass_result.pass
		';

        $res = $this->db->queryF(
            $query,
            ['integer'],
            [$this->getActiveId()]
        );

        $this->passes = [];

        while ($row = $this->db->fetchAssoc($res)) {
            $this->passes[$row['pass']] = $row;
        }
    }

    private function getLazyLoadedPasses(): array
    {
        $this->ensureLoadedPasses();
        return $this->passes;
    }

    public function loadLastFinishedPass(): void
    {
        $query = 'SELECT last_finished_pass FROM tst_active WHERE active_id = %s';

        $res = $this->db->queryF(
            $query,
            ['integer'],
            [$this->getActiveId()]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $this->setLastFinishedPass($row['last_finished_pass']);
        }
    }

    public function getExistingPasses(): array
    {
        return array_keys($this->getLazyLoadedPasses());
    }

    public function hasExistingPasses(): bool
    {
        return $this->getExistingPasses() !== [];
    }

    public function getNumExistingPasses(): int
    {
        return count($this->getExistingPasses());
    }

    public function openPassExists(): bool
    {
        return count($this->getExistingPasses()) > count($this->getClosedPasses());
    }

    public function getClosedPasses(): array
    {
        $existing_passes = $this->getExistingPasses();
        return $this->fetchClosedPasses($existing_passes);
    }

    public function getReportablePasses(): array
    {
        $existing_passes = $this->getExistingPasses();
        return $this->fetchReportablePasses($existing_passes);
    }

    public function hasReportablePasses(): bool
    {
        return (bool) count($this->getReportablePasses());
    }

    private function fetchReportablePasses(array $existing_passes): array
    {
        $last_pass = $this->fetchLastPass($existing_passes);

        $reportable_passes = [];

        foreach ($existing_passes as $pass) {
            if ($this->isReportablePass($last_pass, $pass)) {
                $reportable_passes[] = $pass;
            }
        }

        return $reportable_passes;
    }

    private function fetchClosedPasses(array $existing_passes): array
    {
        $closed_passes = [];

        foreach ($existing_passes as $pass) {
            if ($this->isClosedPass($pass)) {
                $closed_passes[] = $pass;
            }
        }

        return $closed_passes;
    }

    private function fetchLastPass(array $existing_passes): ?int
    {
        $last_pass = null;

        foreach ($existing_passes as $pass) {
            if ($last_pass === null || $pass > $last_pass) {
                $last_pass = $pass;
            }
        }

        return $last_pass;
    }

    private function isReportablePass(int $last_pass, int $pass): bool
    {
        switch ($this->test_obj->getScoreReporting()) {
            case ilObjTestSettingsResultSummary::SCORE_REPORTING_IMMIDIATLY:
                return true;

            case ilObjTestSettingsResultSummary::SCORE_REPORTING_DATE:
                return $this->isReportingDateReached();

            case ilObjTestSettingsResultSummary::SCORE_REPORTING_FINISHED:
                if ($pass < $last_pass) {
                    return true;
                }

                return $this->isClosedPass($pass);

            case ilObjTestSettingsResultSummary::SCORE_REPORTING_AFTER_PASSED:
                if (!$this->hasTestPassedOnce($this->getActiveId())) {
                    return false;
                }

                return $this->isClosedPass($pass);
        }

        return false;
    }

    private function checkLastFinishedPassInitialised()
    {
        if ($this->getLastFinishedPass() === null) {
            throw new ilTestException('invalid object state: last finished pass was not set!');
        }
    }

    private function isClosedPass(int $pass): bool
    {
        $this->checkLastFinishedPassInitialised();

        if ($pass <= $this->getLastFinishedPass()) {
            return true;
        }

        if ($this->isProcessingTimeReached($pass)) {
            return true;
        }

        return false;
    }

    private function isReportingDateReached(): bool
    {
        $reporting_date = $this->test_obj->getScoreSettings()->getResultSummarySettings()->getReportingDate();
        return $reporting_date <= new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }

    private function isProcessingTimeReached(int $pass): bool
    {
        if (!$this->test_obj->getEnableProcessingTime()) {
            return false;
        }

        $startingTime = $this->test_obj->getStartingTimeOfUser($this->getActiveId(), $pass);

        if ($startingTime === false) {
            return false;
        }

        return $this->test_obj->isMaxProcessingTimeReached($startingTime, $this->getActiveId());
    }

    public function getLastFinishedPassTimestamp(): ?int
    {
        $last_finished_pass = $this->getLastFinishedPass();
        if ($last_finished_pass === null || $last_finished_pass === -1) {
            return null;
        }

        $passes = $this->getLazyLoadedPasses();
        if (!isset($passes[$last_finished_pass])) {
            return null;
        }
        return $passes[$last_finished_pass]['tstamp'];
    }

    public function hasTestPassedOnce(int $active_id): bool
    {
        if (!isset($this->test_passed_once_cache[$active_id])) {
            $this->test_passed_once_cache[$active_id] = false;

            $res = $this->db->queryF(
                'SELECT passed_once FROM tst_result_cache WHERE active_fi = %s',
                ['integer'],
                [$active_id]
            );

            while ($row = $this->db->fetchAssoc($res)) {
                $this->test_passed_once_cache[$active_id] = (bool) $row['passed_once'];
            }
        }

        return $this->test_passed_once_cache[$active_id];
    }
}
