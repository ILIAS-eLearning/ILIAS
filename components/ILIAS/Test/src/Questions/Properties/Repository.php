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

namespace ILIAS\Test\Questions\Properties;

interface Repository
{
    /**
     * Returns a Properties-objects if available. An entry might be null when a
     * question does not exist anymore (this should not happen, but...) and
     * it will also have the SequenceProperty set to null, if the question
     * is part of a random test or the sequence-information is missing for
     * another reason.
     * As the question ids are test specific the results will also be for a single test.
     */
    public function getQuestionPropertiesForQuestionId(int $question_id): ?QuestionProperties;

    /**
     * Returns an array of Properties-objects if available. An entry might be null
     * when a question does not exist anymore (this should not happen, but...) and
     * it will also have the SequenceProperty set to null, if the question
     * is part of a random test or the sequence-information is missing for
     * another reason.
     *
     * @return array<Properties|null>
     */
    public function getQuestionPropertiesForQuestionIds(array $question_ids): array;

    /**
     * Returns an array of Properties-objects with AggregatedResultsProperties
     * if available. A entry might be null when a question does not exist anymore
     * (this should not happen, but...) and it might also have the AggregatedResultsProperty
     * set to null, if there are no results for the question.
     * As the question ids are test specific the results will also be for a single test.
     *
     * @return array<Properties|null>
     */
    public function getQuestionPropertiesWithAggregatedResultsForQuestionIds(array $question_ids): array;

    /**
     * This is an adaptor to query the question pool if a question exists for
     * the original_id of a given question_id. The original_id points to the
     * question a given question was derived from (if any).
     */
    public function originalQuestionExists(int $question_id): bool;
}
