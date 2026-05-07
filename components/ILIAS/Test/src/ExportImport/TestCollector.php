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
use ilComponentRepository;
use ilDBConstants;
use ilDBInterface;
use ILIAS\Data\ObjectId;
use ILIAS\Language\Language;
use ILIAS\Test\ExportImport\Envelopes\AdditionalWorkingTime;
use ILIAS\Test\ExportImport\Envelopes\ManualFeedback;
use ILIAS\Test\ExportImport\Envelopes\QuestionResult;
use ILIAS\Test\ExportImport\Envelopes\QuestionSetConfig;
use ILIAS\Test\ExportImport\Envelopes\RandomTestQuestion;
use ILIAS\Test\ExportImport\Envelopes\Solution;
use ILIAS\Test\ExportImport\Envelopes\WorkingTime;
use ILIAS\Test\Logging\TestLogger;
use ILIAS\Test\Participants\ParticipantRepository;
use ILIAS\Test\Questions\Properties\Properties;
use ILIAS\Test\Questions\Properties\Repository as QuestionsRepository;
use ILIAS\Test\Results\Data\Repository as ResultsRepository;
use ILIAS\Test\Settings\GlobalSettings\UserIdentifiers;
use ILIAS\Test\TestManScoringDoneHelper;
use ILIAS\TestQuestionPool\ExportImport\Export\CollectsQuestions;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\DataCollector;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Envelopes\Id;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ilObjTest;
use ilTestQuestionSetConfigFactory;
use ilTestRandomQuestionSetSourcePoolDefinitionFactory;
use ilTestRandomQuestionSetSourcePoolDefinitionList;
use ilTestRandomQuestionSetStagingPoolQuestionList;
use ilTestSequence;
use ilTestSkillLevelThreshold;
use ilTestSkillLevelThresholdList;
use ilTree;

/**
 * Collector to aggregate data from the test object for export.
 */
class TestCollector implements DataCollector
{
    use CollectsQuestions;

    private readonly TestManScoringDoneHelper $manual_scoring;

    /** @var array<int, Properties> $questions */
    private ?array $questions = null;
    private ?ilObjTest $test = null;
    private ?array $participants = null;


    public function __construct(
        private readonly ParticipantRepository $participant_repository,
        private readonly ResultsRepository $results_repository,
        private readonly QuestionsRepository $questions_repository,
        private readonly GeneralQuestionPropertiesRepository $general_questions_repository,
        private readonly ilDBInterface $db,
        private readonly ilTree $tree,
        private readonly Language $lng,
        private readonly TestLogger $logger,
        private readonly ilComponentRepository $component_repository,
        private readonly ObjectId $object_id
    ) {
        $this->manual_scoring = new TestManScoringDoneHelper($this->db);
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
            $in_clause = $this->db->in('usr_id', $user_ids, false, ilDBConstants::T_INTEGER);
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

    /**
     * Get the question set config. If it is a random question set config, also return the source pool sages and
     * definitions.
     */
    public function getQuestionSetConfig(): QuestionSetConfig
    {
        $factory = new ilTestQuestionSetConfigFactory(
            $this->tree,
            $this->db,
            $this->lng,
            $this->logger,
            $this->component_repository,
            $this->getObject(),
            $this->general_questions_repository
        );

        $config = new QuestionSetConfig($factory->getQuestionSetConfig());
        if (!$config->isRandom()) {
            return $config;
        }

        $definition_factory = new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
            $this->db,
            $this->getObject()
        );

        $definition_list = new ilTestRandomQuestionSetSourcePoolDefinitionList(
            $this->db,
            $this->getObject(),
            $definition_factory
        );

        $definition_list->loadDefinitions();
        $config->setDefinitions(iterator_to_array($definition_list));

        foreach ($definition_list->getInvolvedSourcePoolIds() as $pool_id) {
            $config->addStagingPoolQuestions($pool_id, $this->getStagingPoolQuestions($pool_id));
        }

        return $config;
    }

    /**
     * @return list<int>
     */
    private function getStagingPoolQuestions(int $pool_id): array
    {
        $question_list = new ilTestRandomQuestionSetStagingPoolQuestionList(
            $this->db,
            $this->component_repository
        );
        $question_list->setTestId($this->getTestId());
        $question_list->setPoolId($pool_id);
        $question_list->loadQuestions();

        return $question_list->getQuestions();
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
                if ($participant->getActiveId() !== null) {
                    $this->participants[] = $participant->getActiveId();
                }
            }
        }
        return $this->participants;
    }

    /**
     * @param list<int> $participant_ids
     * @return array<int, array{submittimestamp: ?string, lastindex: ?int, objective_container: ?int, start_lock: ?string}>
     */
    public function getAdditionalParticipantData(array $participant_ids): array
    {
        $in_clause = $this->db->in('active_id', $participant_ids, false, ilDBConstants::T_INTEGER);
        $query = $this->db->query("SELECT active_id AS mapping_id, submittimestamp, lastindex, objective_container, start_lock FROM tst_active WHERE {$in_clause}");

        $data = [];
        while ($row = $this->db->fetchAssoc($query)) {
            $data[$row['mapping_id']] = $row;
        }

        return $data;
    }

    /*
        Results
    */

    public function getResults(int $participant_id): array
    {
        $attempt_results = $this->results_repository->getTestAttemptResults($participant_id);
        $set = [
            'sequences' => $this->getSequences($participant_id, array_keys($attempt_results)),
            'solutions' => $this->getSolutions($participant_id),
            'results' => $this->getQuestionResults($participant_id),
            'attempts' => $attempt_results,
            'test_result' => $this->results_repository->getTestResult($participant_id),
            'working_times' => $this->getWorkingTimes($participant_id),
            'manual_feedback' => $this->getManualFeedback($participant_id),
            'manual_scoring' => [
                'active_id' => new Id($participant_id, 'participant'),
                'done' => $this->manual_scoring->isDone($participant_id),
            ],
        ];

        if ($this->getObject()->isRandomTest()) {
            $set['questions'] = $this->getRandomTestQuestions($participant_id);
        }
        return $set;
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
     * @return list<QuestionResult>
     */
    public function getQuestionResults(int $participant_id): array
    {
        $query = $this->db->queryF(
            "SELECT * FROM tst_test_result WHERE active_fi = %s",
            [ilDBConstants::T_INTEGER],
            [$participant_id]
        );

        return array_map(
            fn(array $row): QuestionResult => QuestionResult::fromRow($row),
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

    /**
     * @param list<int> $attempts
     * @return list<ilTestSequence>
     */
    public function getSequences(int $participant_id, array $attempts): array
    {
        foreach ($attempts as $attempt) {
            $test_sequence = new ilTestSequence($this->db, $participant_id, $attempt, $this->general_questions_repository);
            $test_sequence->loadFromDb();
            $sequences[] = $test_sequence;
        }
        return $sequences;
    }


    /**
     * @return list<AdditionalWorkingTime>
     */
    public function getAdditionalWorkingTimes(): array
    {
        $query = $this->db->queryF(
            "SELECT * FROM tst_addtime WHERE test_fi = %s",
            [ilDBConstants::T_INTEGER],
            [$this->getTestId()]
        );

        return array_map(
            fn(array $row): AdditionalWorkingTime => AdditionalWorkingTime::fromRow($row),
            $this->db->fetchAll($query)
        );
    }

    /**
     * @return list<RandomTestQuestion>
     */
    public function getRandomTestQuestions(int $participant_id): array
    {
        $query = $this->db->queryF(
            "SELECT * FROM tst_test_rnd_qst WHERE active_fi = %s",
            [ilDBConstants::T_INTEGER],
            [$participant_id]
        );

        return array_map(
            fn(array $row): RandomTestQuestion => RandomTestQuestion::fromRow($row),
            $this->db->fetchAll($query)
        );
    }
}
