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

namespace ILIAS\Test\Questions\Properties;

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionProperties;

class DatabaseRepository implements Repository
{
    private const SEQUENCE_TABLE_TEST_FIXED = 'tst_sequence';
    private const SEQUENCE_TABLE_TEST_RANDOM = 'tst_test_rnd_qst';
    private const LINKING_TABLE_TEST_FIXED = 'tst_test_question';
    private const LINKING_TABLE_TEST_RANDOM = 'tst_rnd_cpy';
    private const RESULTS_TABLE = 'tst_test_result';

    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly GeneralQuestionPropertiesRepository $question_properties_repo
    ) {
    }

    public function getQuestionPropertiesForQuestionId(int $question_id): ?Properties
    {
        return $this->getQuestionPropertiesForQuestionIds([$question_id])[$question_id] ?? null;
    }

    public function getQuestionPropertiesForQuestionIds(array $question_ids): array
    {
        return $this->buildQuestionPropertiesFromGeneralQuestionPropertiesAndSquenceProperties(
            $this->question_properties_repo->getForQuestionIds($question_ids),
            $this->getSequencePropertiesForQuestionIds($question_ids)
        );
    }

    public function getQuestionPropertiesForTest(\ilObjTest $test): array
    {
        if ($test->isFixedTest()) {
            return $this->buildQuestionPropertiesForFixedTest($test);
        }

        if ($test->isRandomTest()) {
            return $this->buildQuestionPropertiesForRandomTest($test);
        }

        return [];
    }

    public function getQuestionPropertiesWithAggregatedResultsForTest(\ilObjTest $test): array
    {
        $general_question_properties = $this->getQuestionPropertiesForTest($test);
        $query = $this->db->query(
            'SELECT question_fi, COUNT(*) as nr_of_answers, SUM(points) as achieved_points'
            . ' FROM ' . self::RESULTS_TABLE
            . ' WHERE answered = 1 AND ' . $this->db->in(
                'question_fi',
                array_keys($general_question_properties),
                false,
                \ilDBConstants::T_INTEGER
            ) . ' GROUP BY question_fi'
        );

        return array_reduce(
            $this->db->fetchAll($query, \ilDBConstants::FETCHMODE_OBJECT),
            static function (array $c, \stdClass $v): array {
                if ($c[$v->question_fi] === null) {
                    return $c;
                }

                $c[$v->question_fi] = $c[$v->question_fi]->withAggregatedResults(
                    new PropertyAggregatedResults(
                        $v->question_fi,
                        $v->nr_of_answers,
                        $c[$v->question_fi]->getGeneralQuestionProperties()->getAvailablePoints(),
                        $v->achieved_points
                    )
                );
                return $c;
            },
            $general_question_properties
        );
    }

    public function originalQuestionExists(int $question_id): bool
    {
        return $this->question_properties_repo->originalQuestionExists($question_id);
    }

    /**
     * @param array<int> $question_ids
     * @return array<int, PropertySequence|null>
     */
    private function getSequencePropertiesForQuestionIds(array $question_ids): array
    {
        $query = $this->db->query(
            'SELECT test_fi, question_fi, sequence FROM ' . self::LINKING_TABLE_TEST_FIXED
            . ' WHERE ' . $this->db->in('question_fi', $question_ids, false, \ilDBConstants::T_INTEGER)
        );

        return array_reduce(
            $this->db->fetchAll($query, \ilDBConstants::FETCHMODE_OBJECT),
            static function (array $c, \stdClass $v): array {
                $c[$v->question_fi] = new PropertySequence(
                    $v->question_fi,
                    $v->test_fi,
                    $v->sequence
                );
                return $c;
            },
            array_fill_keys($question_ids, null)
        );
    }

    /**
     * @return array<int>
     */
    private function getQuestionIdsForRandomTest(int $test_id): array
    {
        $query = $this->db->query(
            'SELECT qst_fi FROM ' . self::LINKING_TABLE_TEST_RANDOM
            . ' WHERE tst_fi=' . $test_id
        );

        return array_map(
            fn(\stdClass $v): int => $v->qst_fi,
            $this->db->fetchAll($query, \ilDBConstants::FETCHMODE_OBJECT)
        );
    }

    /**
     * @return array<Properties>
     */
    private function buildQuestionPropertiesForFixedTest(\ilObjTest $test): array
    {
        $general_question_properties = $this->question_properties_repo->getForParentObjectId($test->getId());
        return $this->buildQuestionPropertiesFromGeneralQuestionPropertiesAndSquenceProperties(
            $general_question_properties,
            $this->getSequencePropertiesForQuestionIds(array_keys($general_question_properties)),
            true
        );
    }

    /**
     * @return array<Properties>
     */
    private function buildQuestionPropertiesForRandomTest(\ilObjTest $test): array
    {
        if ($test->getQuestionSetConfig()->getLastQuestionSyncTimestamp() === 0) {
            return [];
        }
        $question_ids = $this->getQuestionIdsForRandomTest($test->getTestId());
        return array_reduce(
            $this->question_properties_repo->getForQuestionIds($question_ids),
            static function (array $c, GeneralQuestionProperties $v): array {
                if ($v === null) {
                    return $c;
                }
                $c[$v->getQuestionId()] = new Properties(
                    $v->getQuestionId(),
                    $v
                );
                return $c;
            },
            []
        );
    }

    /**
     * @param array<ILIAS\TestQuestionPool\Questions\GeneralQuestionProperties> $general_question_properties
     * @param array<PropertySquence> $sequence_properties
     * @param bool $delete_on_missing_sequence This is not what it should be,
     * but currently there might be a lot of questions around that are broken.
     * For tests with a fixed question set we need to clean them up otherwise
     * too many questions are shown.
     * @return array<Properties>
     */
    private function buildQuestionPropertiesFromGeneralQuestionPropertiesAndSquenceProperties(
        array $general_question_properties,
        array $sequence_properties,
        bool $delete_on_missing_sequence = false
    ): array {
        return array_reduce(
            $general_question_properties,
            static function (array $c, GeneralQuestionProperties $v) use (
                $sequence_properties,
                $delete_on_missing_sequence
            ): array {
                if ($v === null
                    || $delete_on_missing_sequence
                        && $sequence_properties[$v->getQuestionId()] === null) {
                    return $c;
                }

                $question_properties = new Properties(
                    $v->getQuestionId(),
                    $v
                );
                if ($sequence_properties[$v->getQuestionId()] !== null) {
                    $question_properties = $question_properties
                        ->withSequenceInformation($sequence_properties[$v->getQuestionId()]);
                }

                $c[$v->getQuestionId()] = $question_properties;
                return $c;
            },
            []
        );
    }
}
