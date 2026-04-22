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

namespace ILIAS\Test\ExportImport;

use Generator;
use ilDBConstants;
use ilDBInterface;
use ILIAS\Data\ObjectId;
use ILIAS\Test\ExportImport\Envelopes\ManualFeedback;
use ILIAS\Test\ExportImport\Envelopes\Solution;
use ILIAS\Test\ExportImport\Envelopes\WorkingTime;
use ILIAS\Test\Participants\ParticipantRepository;
use ILIAS\Test\Questions\Properties\Properties;
use ILIAS\Test\Questions\Properties\Repository as QuestionsRepository;
use ILIAS\Test\Results\Data\Repository as ResultsRepository;
use ILIAS\Test\Settings\GlobalSettings\UserIdentifiers;
use ILIAS\TestQuestionPool\ExportImport\Export\CollectsQuestions;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\DataCollector;
use ilObjTest;
use ilTestSkillLevelThreshold;
use ilTestSkillLevelThresholdList;

/**
 * Collector to aggregate data from the test object for export.
 */
class TestCollector implements DataCollector
{
    use CollectsQuestions;

    /** @var array<int, Properties> $questions */
    private ?array $questions = null;
    private ?ilObjTest $test = null;
    private ?array $participants = null;


    public function __construct(
        private readonly ParticipantRepository $participant_repository,
        private readonly ResultsRepository $results_repository,
        private readonly QuestionsRepository $questions_repository,
        private readonly ilDBInterface $db,
        private readonly ObjectId $object_id
    ) {
    }

    private function database(): ilDBInterface
    {
        return $this->db;
    }

    public function getObjectId(): ObjectId
    {
        return $this->object_id;
    }

    public function getTestId(): int
    {
        return $this->getObject()->getTestId();
    }

    public function getObject(): ilObjTest
    {
        if ($this->test === null) {
            $this->test = new ilObjTest($this->object_id->toInt(), false);
        }

        return $this->test;
    }

    public function getSettings(): array
    {
        return [
            'main' => $this->test->getMainSettings(),
            'scoring' => $this->test->getScoreSettings(),
            'marks' => $this->test->getMarkSchema(),
        ];
    }

    /**
     * Create a mapping of user IDs to the user identifier field specified in the test object's global settings.
     *
     * @param list<int> $user_ids
     * @return array{identifier: string, mapping: array<int, string>}
     */
    public function getUserMapping(array $user_ids): array
    {
        $export_identifier = $this->getObject()->getGlobalSettings()->getUserIdentifier();

        $mapping = [];
        if ($export_identifier === UserIdentifiers::USER_ID) {
            foreach ($user_ids as $user_id) {
                $mapping[$user_id] = $user_id;
            }
        } else {
            $in_clause = $this->db->in('usr_id', $user_ids, false, 'integer');
            $query = $this->db->query("SELECT usr_id, {$export_identifier->value} FROM usr_data WHERE {$in_clause}");

            foreach ($this->db->fetchAll($query) as $row) {
                $mapping[$row['usr_id']] = $row[$export_identifier->value];
            }
        };

        return [
            'identifier' => $export_identifier->value,
            'mapping' => $mapping,
        ];
    }

    /*
        Questions
    */

    /**
     * @inheritDoc
     */
    public function getQuestionProperties(): array
    {
        return array_map(
            fn(Properties $property) => $property->getGeneralQuestionProperties(),
            $this->getTestQuestionProperties()
        );
    }

    /**
     * @return array<int, Properties>
     */
    public function getTestQuestionProperties(): array
    {
        if ($this->questions === null) {
            $this->questions = $this->questions_repository->getQuestionPropertiesForTest($this->getObject());
        }
        return $this->questions;
    }

    /**
     * @return list<ilTestSkillLevelThreshold>
     */
    public function getSkillLevelThresholds(): array
    {
        $threshold_list = new ilTestSkillLevelThresholdList($this->database());
        $threshold_list->setTestId($this->getTestId());
        $threshold_list->loadFromDb();

        $thresholds = [];
        foreach ($this->getSkillAssignments() as $assignment) {
            $thresholds += $threshold_list->getThesholdsOfBaseAndTrefId(
                $assignment->getSkillBaseId(),
                $assignment->getSkillTrefId()
            );
        }

        return $thresholds;
    }

    /*
        Participants
    */

    /**
     * @return Generator<int, \ILIAS\Test\Participants\Participant>
     */
    public function getParticipants(): Generator
    {
        return $this->participant_repository->getParticipants($this->getTestId());
    }

    /**
     * @return list<int>
     */
    public function getParticipantsIds(): array
    {
        if ($this->participants === null) {
            $this->participants = [];
            foreach ($this->getParticipants() as $participant) {
                $this->participants[] = $participant->getActiveId();
            }
        }
        return $this->participants;
    }

    /*
        Results
    */

    public function getResults(int $participant_id): array
    {
        return [
            'results' => $this->results_repository->getTestResult($participant_id),
            'attempts' => $this->results_repository->getTestAttemptResults($participant_id),
            'solutions' => $this->getSolutions($participant_id),
            'working_times' => $this->getWorkingTimes($participant_id),
            'manual_feedback' => $this->getManualFeedback($participant_id),
        ];
    }

    /**
     * @return list<Solution>
     */
    public function getSolutions(int $participant_id): array
    {
        $query = $this->db->queryF(
            "SELECT * FROM tst_solutions WHERE active_fi = %s",
            [ilDBConstants::T_INTEGER],
            [$participant_id]
        );

        return array_map(
            fn(array $row): Solution => Solution::fromRow($row),
            $this->db->fetchAll($query)
        );
    }

    /**
     * @return list<WorkingTime>
     */
    public function getWorkingTimes(int $participant_id): array
    {
        $query = $this->db->queryF(
            "SELECT * FROM tst_times WHERE active_fi = %s",
            [ilDBConstants::T_INTEGER],
            [$participant_id]
        );

        return array_map(
            fn(array $row): WorkingTime => WorkingTime::fromRow($row),
            $this->db->fetchAll($query)
        );
    }

    /**
     * @return list<ManualFeedback>
     */
    public function getManualFeedback(int $participant_id): array
    {
        $query = $this->db->queryF(
            "SELECT * FROM tst_manual_fb WHERE active_fi = %s",
            [ilDBConstants::T_INTEGER],
            [$participant_id]
        );

        return array_map(
            fn(array $row): ManualFeedback => ManualFeedback::fromRow($row),
            $this->db->fetchAll($query)
        );
    }
}
