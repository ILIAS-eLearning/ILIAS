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

enum TestAdministrationInteractionTypes: string
{
    case NEW_TEST_CREATED = 'new_test_created';
    case MAIN_SETTINGS_MODIFIED = 'main_settings_modified';
    case SCORING_SETTINGS_MODIFIED = 'scoring_settings_modified';
    case MARK_SCHEMA_MODIFIED = 'mark_schema_modified';
    case MARK_SCHEMA_RESET = 'mark_schema_reset';
    case QUESTION_SELECTION_CRITERIA_MODIFIED = 'question_selection_criteria_modified';
    case QUESTION_ADDED = 'question_added';
    case QUESTION_MOVED = 'question_moved';
    case QUESTION_REMOVED = 'question_removed';
    case QUESTIONS_SYNCHRONISATION_RESET = 'question_synchronisation_reset';
    case QUESTIONS_SYNCHRONISED = 'questions_synchronised';
    case EXTRA_TIME_ADDED = 'extra_time_added';
    case TEST_RUN_OF_PARTICIPANT_CLOSED = 'test_run_of_participant_closed';
    case PARTICIPANT_DATA_REMOVED = 'participant_data_removed';
}
