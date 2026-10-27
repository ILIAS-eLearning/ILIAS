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

namespace ILIAS\BackgroundTasks\Implementation\UI;

use ILIAS\BackgroundTasks\Implementation\Bucket\State;

/**
 * Class StateTranslator
 * @package ILIAS\BackgroundTasks\Implementation\UI
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 */
trait StateTranslator
{
    public function translateState(int $state, \ilLanguage $lng): string
    {
        return match ($state) {
            State::SCHEDULED => $lng->txt("observer_state_scheduled"),
            State::RUNNING => $lng->txt("observer_state_running"),
            State::USER_INTERACTION => $lng->txt("observer_state_user_interaction"),
            State::FINISHED => $lng->txt("observer_state_finished"),
            State::ERROR => $lng->txt("observer_state_error"),
            default => '',
        };
    }
}
