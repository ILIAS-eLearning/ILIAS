<?php

namespace ILIAS\BackgroundTasks\Implementation\UI;

use ILIAS\BackgroundTasks\Implementation\Bucket\State;

/**
 * Class StateTranslator
 * @package ILIAS\BackgroundTasks\Implementation\UI
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 */
trait StateTranslator
{
    public function translateState(int $state, \ilLanguage $lng) : string
    {
        switch ($state) {
            case State::SCHEDULED:
                return $lng->txt("observer_state_scheduled");
            case State::RUNNING:
                return $lng->txt("observer_state_running");
            case State::USER_INTERACTION:
                return $lng->txt("observer_state_user_interaction");
            case State::FINISHED:
                return $lng->txt("observer_state_finished");
            case State::ERROR:
                return $lng->txt("observer_state_error");
        }
        return '';
    }
}
