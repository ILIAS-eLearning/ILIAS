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

namespace ILIAS\TestQuestionPool\Questions;

use ILIAS\Test\Logging\TestParticipantInteraction;
use ILIAS\Test\Logging\TestParticipantInteractionTypes;
use ILIAS\Test\Logging\TestQuestionAdministrationInteraction;
use ILIAS\Test\Logging\TestQuestionAdministrationInteractionTypes;

use ILIAS\Test\Logging\AdditionalInformationGenerator;

interface Question
{
    public function validateSolutionSubmit();

    public function toLog(AdditionalInformationGenerator $additional_info): array;

    public function answerToParticipantInteraction(
        int $test_id,
        int $active_id,
        int $pass,
        string $source_ip,
        TestParticipantInteractionTypes $interaction_type
    ): TestParticipantInteraction;

    public function toQuestionAdministrationInteraction(
        AdditionalInformationGenerator $additional_info,
        int $test_id,
        TestQuestionAdministrationInteractionTypes $interaction_type
    ): TestQuestionAdministrationInteraction;
}
