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

namespace ILIAS\Test\Participants;

use ILIAS\Language\Language;
use ILIAS\UI\Component\ViewControl\Mode;
use ILIAS\UI\Factory as UIFactory;

enum AttemptSpecifiers: string
{
    case ScoredAttempt = 'tst_participant_scored_attempts';
    case LastAttempt = 'tst_participant_last_attempts';

    public function getAttemptId(Participant $participant): ?int
    {
        return match ($this) {
            self::ScoredAttempt => null,
            self::LastAttempt => $participant->getLastStartedAttempt(),
        };
    }

    public function buildViewControl(\ilCtrl $ctrl, Language $lng, UIFactory $ui_factory): Mode
    {
        $target_class = \ilTestParticipantsGUI::class;
        $actions = [];

        foreach (self::cases() as $case) {
            $ctrl->setParameterByClass($target_class, 'view_mode', $case->value);
            $actions[$lng->txt($case->value)] = $ctrl->getLinkTargetByClass($target_class);
        }

        $ctrl->clearParametersByClass($target_class);

        return $ui_factory->viewControl()->mode(
            $actions,
            $lng->txt('participant_view_control_aria')
        )->withActive($lng->txt($this->value));
    }
}
