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

namespace ILIAS\Test\Results\Data;

use ILIAS\Test\Participants\ParticipantRepository;

class TestParticipantResultStatus
{
    protected const string STATUS_PASSED = 'passed';
    protected const string STATUS_FAILED = 'failed';

    /**
     * @var array<string, array{passed: bool, failed: bool}> $status_cache
     */
    protected static array $status_cache = [];

    /**
     * @var array<string, bool> $finished_passes
     */
    protected static array $finished_passes = [];

    public function __construct(
        protected \ilDBInterface $db,
        protected TestPassResultManager $result_manager,
        protected ParticipantRepository $participant_repository,
    ) {
    }

    public function isPassed(int $user_id, int $test_id): bool
    {
        return $this->readFromResultCache($user_id, $test_id, self::STATUS_PASSED);
    }

    public function isFailed(int $user_id, int $test_id): bool
    {
        return $this->readFromResultCache($user_id, $test_id, self::STATUS_FAILED);
    }

    public function isFinished(int $user_id, int $test_id): bool
    {
        if (isset(self::$finished_passes[self::cacheKey($user_id, $test_id)])) {
            return self::$finished_passes[self::cacheKey($user_id, $test_id)];
        }

        $participant = $this->participant_repository->getParticipantByUserId($test_id, $user_id);
        self::$finished_passes[self::cacheKey($user_id, $test_id)] = $participant && $participant->getLastFinishedAttempt() !== null;
        return self::$finished_passes[self::cacheKey($user_id, $test_id)];
    }


    private function readFromResultCache(int $user_id, int $test_id, string $param): bool
    {
        if (isset(self::$status_cache[self::cacheKey($user_id, $test_id)])) {
            return self::$status_cache[self::cacheKey($user_id, $test_id)][$param];
        }

        $result = $this->result_manager->getTestResultByParticipant($test_id, $user_id);
        if ($result === null) {
            return false;
        }

        self::$status_cache[self::cacheKey($user_id, $test_id)] = [
            self::STATUS_PASSED => $result->isPassed(),
            self::STATUS_FAILED => $result->isFailed()
        ];

        return self::$status_cache[self::cacheKey($user_id, $test_id)][$param];
    }

    private function cacheKey(int $user_id, int $test_id): string
    {
        return $test_id . ':' . $user_id;
    }
}
