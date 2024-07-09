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

namespace ILIAS\Test\Logging;

enum TestErrorTypes: string
{
    case ERROR_ON_TEST_ADMINISTRATION_INTERACTION = 'error_on_test_administration_interaction';
    case ERROR_ON_QUESTION_ADMINISTRATION_INTERACTION = 'error_on_question_administration_interaction';
    case ERROR_ON_PARTICIPANT_INTERACTION = 'error_on_participant_interaction';
    case ERROR_ON_SCORING_INTERACTION = 'error_on_scoring_interaction';
    case ERROR_ON_UNDEFINED_INTERACTION = 'error_on_undefined_interaction';
}
