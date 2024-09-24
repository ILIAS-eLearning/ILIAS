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
 * @author    Björn Heyser <info@bjoernheyser.de>
 */
class ilAssQuestionHintRequestStatisticRegister
{
    /**
     * @var array<ilAssQuestionHintRequestStatisticData>
     */
    protected $requests_by_test_pass_and_question_id = [];

    public function addRequestByTestPassIndexAndQuestionId(
        int $pass,
        int $question_id,
        ilAssQuestionHintRequestStatisticData $request
    ): void {
        if (!isset($this->requests_by_test_pass_and_question_id[$pass])) {
            $this->requests_by_test_pass_and_question_id[$pass] = [];
        }

        $this->requests_by_test_pass_and_question_id[$pass][$question_id] = $request;
    }

    public function getRequestByTestPassIndexAndQuestionId(
        int $pass,
        int $question_id
    ): ?ilAssQuestionHintRequestStatisticData {
        if (isset($this->requests_by_test_pass_and_question_id[$pass])
            && isset($this->requests_by_test_pass_and_question_id[$pass][$question_id])) {
            return $this->requests_by_test_pass_and_question_id[$pass][$question_id];
        }
        return null;
    }
}
