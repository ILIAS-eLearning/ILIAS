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

class ilWorkingTime
{
    public function __construct(private readonly ilLanguage $lng) {}

    public function prepareWorkingtimeJsTemplate(
        ilObjTest $object,
        array $date,
        float $processing_time_minutes,
        float $processing_time_seconds,
        string $check_url,
        string $redirect_url
    ): ilTemplate {
        // jQuery is required by tpl.workingtime.js
        iljQueryUtil::initjQuery();
        $template = new ilTemplate('tpl.workingtime.js', true, true, 'Modules/Test');
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
}
