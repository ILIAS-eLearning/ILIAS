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

namespace ILIAS\TestQuestionPool\Questions;

use ILIAS\DI\Container;

class PublicInterface
{
    private GeneralQuestionPropertiesRepository $general_questions_repository;

    public function __construct(
        Container $dic
    ) {
        $this->general_questions_repository = new GeneralQuestionPropertiesRepository(
            $dic['ilDB'],
            $dic['component.factory'],
            $dic['lng']
        );
    }
    /**
     * Returns an object containing the basic properties shared by all
     * question types
     */
    public function getGeneralQuestionProperties(int $question_id): ?GeneralQuestionProperties
    {
        return $this->general_questions_repository->getGeneralQuestionProperties($question_id);
    }

    /**
     * Checks if an array of question ids is answered by a user or not
     *
     * @param array<int> $question_ids user id array
     */
    public function areQuestionsAnsweredByUser(int $user_id, array $question_ids): bool
    {
        return $this->general_questions_repository->areQuestionsAnsweredByUser($user_id, $question_ids);
    }
}
