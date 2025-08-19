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

namespace ILIAS\Test\Presentation;

use ilDatePresentation;
use ilDateTime;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ilLanguage;
use ilObjTest;
use ilTemplate;

class WorkingTime
{
    public function __construct(
        private readonly ilLanguage $lng,
        private readonly Factory $ui_factory,
        private readonly Renderer $ui_renderer,
        private readonly int $starting_time,
        private readonly int $processing_time
    ) {
    }

    public function prepareWorkingTimeJsTemplate(
        ilObjTest $object,
        array $date,
        string $check_url,
        string $redirect_url
    ): ilTemplate {
        [$processing_time_minutes, $processing_time_seconds] = $this->getUserProcessingTimeMinutesAndSeconds();
        $template = new ilTemplate('tpl.workingtime.js', true, true, 'components/ILIAS/Test');
        $template->setVariable('STRING_MINUTE', $this->lng->txt('minute'));
        $template->setVariable('STRING_MINUTES', $this->lng->txt('minutes'));
        $template->setVariable('STRING_SECOND', $this->lng->txt('second'));
        $template->setVariable('STRING_SECONDS', $this->lng->txt('seconds'));
        $template->setVariable('STRING_TIMELEFT', $this->lng->txt('tst_time_already_spent_left'));
        $template->setVariable('AND', strtolower($this->lng->txt('and')));
        $template->setVariable('YEAR', $date['year']);
        $template->setVariable('MONTH', $date['mon'] - 1);
        $template->setVariable('DAY', $date['mday']);
        $template->setVariable('HOUR', $date['hours']);
        $template->setVariable('MINUTE', $date['minutes']);
        $template->setVariable('SECOND', $date['seconds']);
        if ($object->isEndingTimeEnabled()) {
            $date_time = new ilDateTime($object->getEndingTime(), IL_CAL_UNIX);
            preg_match('/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', $date_time->get(IL_CAL_TIMESTAMP), $matches);
            if ($matches !== []) {
                $template->setVariable('ENDYEAR', $matches[1]);
                $template->setVariable('ENDMONTH', $matches[2] - 1);
                $template->setVariable('ENDDAY', $matches[3]);
                $template->setVariable('ENDHOUR', $matches[4]);
                $template->setVariable('ENDMINUTE', $matches[5]);
                $template->setVariable('ENDSECOND', $matches[6]);
            }
        }

        $datenow = getdate();
        $template->setVariable('YEARNOW', $datenow['year']);
        $template->setVariable('MONTHNOW', $datenow['mon'] - 1);
        $template->setVariable('DAYNOW', $datenow['mday']);
        $template->setVariable('HOURNOW', $datenow['hours']);
        $template->setVariable('MINUTENOW', $datenow['minutes']);
        $template->setVariable('SECONDNOW', $datenow['seconds']);
        $template->setVariable('PTIME_M', $processing_time_minutes);
        $template->setVariable('PTIME_S', $processing_time_seconds);
        $template->setVariable('REDIRECT_URL', $redirect_url);
        $template->setVariable('CHECK_URL', $check_url);

        return $template;
    }

    public function getMessageBox(bool $verbose): string
    {
        $message_text = $verbose
            ? $this->getUserProcessingTimeString() . ' <span id="timeleft">' . $this->getUserRemainingTimeString() . '</span>'
            : '<div class="ilTstWorkingFormBlock_WorkingTime"><span id="timeleft" class="ilTstWorkingFormInfo_ProcessTimeLeft">' . $this->getUserRemainingTimeString() . '</span></div>';
        return $this->ui_renderer->render($this->ui_factory->messageBox()->info($message_text));
    }

    private function getUserProcessingTimeMinutesAndSeconds(): array
    {
        $processing_time_minutes = floor($this->processing_time / 60);
        $processing_time_seconds = $this->processing_time - $processing_time_minutes * 60;

        return [$processing_time_minutes, $processing_time_seconds];
    }

    private function getUserProcessingTimeString(): string
    {
        [$processing_time_minutes, $processing_time_seconds] = $this->getUserProcessingTimeMinutesAndSeconds();

        $str_processing_time = '';
        if ($processing_time_minutes > 0) {
            $str_processing_time = "$processing_time_minutes {$this->lng->txt($processing_time_minutes === 1 ? 'minute' : 'minutes')}";
        }

        if ($processing_time_seconds > 0) {
            if ($str_processing_time !== '') {
                $str_processing_time .= " {$this->lng->txt('and')} ";
            }
            $str_processing_time .= "$processing_time_seconds {$this->lng->txt($processing_time_seconds === 1 ? 'second' : 'seconds')}";
        }

        return sprintf(
            $this->lng->txt('tst_time_already_spent'),
            ilDatePresentation::formatDate(new ilDateTime(getdate($this->starting_time), IL_CAL_FKT_GETDATE)),
            $str_processing_time
        );
    }

    private function getUserRemainingTimeString(): string
    {
        $time_left = $this->starting_time + $this->processing_time - time();
        $time_left_minutes = floor($time_left / 60);
        $time_left_seconds = $time_left - $time_left_minutes * 60;
        $str_time_left = '';
        if ($time_left_minutes > 0) {
            $str_time_left = "$time_left_minutes {$this->lng->txt($time_left_minutes === 1 ? 'minute' : 'minutes')}";
        }
        if ($time_left < 300 && $time_left_seconds > 0) {
            if ($str_time_left !== '') {
                $str_time_left .= " {$this->lng->txt('and')} ";
            }
            $str_time_left .= "$time_left_seconds {$this->lng->txt($time_left_seconds === 1 ? 'second' : 'seconds')}";
        }

        return sprintf($this->lng->txt('tst_time_already_spent_left'), $str_time_left);
    }
}
