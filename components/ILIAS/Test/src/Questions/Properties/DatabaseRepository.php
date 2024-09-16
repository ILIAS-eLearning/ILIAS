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
    private const QUESTION_ORDER_TABLE = 'tst_test_question';
    private const RESULTS_TABLE = 'tst_rest_result';

    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly GeneralQuestionPropertiesRepository $question_properties_repo
    ) {
    }

    public function getQuestionPropertiesForQuestionId(int $question_id): ?QuestionProperties
    {
        return $this->getQuestionPropertiesForQuestionIds([$question_id]);
    }

    public function getQuestionPropertiesForQuestionIds(array $question_ids): array
    {
        $sequence_properties = $this->getSequencePropertiesForQuestionIds($question_ids);
        $general_question_properties = $this->question_properties_repo->getForQuestionIds($question_ids);

        return array_reduce(
            $general_question_properties,
            static function (array $c, ?GeneralQuestionProperties $v) use ($sequence_properties): array {
                if ($v === null) {
                    return $c;
                }
                $question_properties = new Properties(
                    $v->getQuestionId(),
                    $v
                );
                if ($sequence_properties[$id] !== null) {
                    $question_properties = $question_properties
                        ->withSequenceInformation($sequence_properties[$v->getQuestionId()]);
                }

                $c[$v->question_fi] = $question_properties;
                return $c;
            },
            array_fill_keys($question_ids, null)
        );
    }

    /**
     * @param array<int> $question_ids
     * @return array<int, Properties|null>
     */
    public function getQuestionPropertiesWithAggregatedResultsForQuestionIds(array $question_ids): array
    {
        $general_question_properties = $this->getQuestionPropertiesForQuestionIds($question_ids);
        $query = $this->db->query(
            'SELECT question_fi, COUNT(*) as nr_of_answers, SUM(points) as achieved_points'
            . ' FROM ' . self::RESULTS_TABLE
            . ' WHERE answered = 1 AND ' . $this->db->in(
                'question_fi',
                $question_ids,
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
            'SELECT test_fi, question_fi, sequence FROM ' . self::QUESTION_ORDER_TABLE
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
}
