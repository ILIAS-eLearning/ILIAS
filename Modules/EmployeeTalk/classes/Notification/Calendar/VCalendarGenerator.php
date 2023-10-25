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

namespace ILIAS\EmployeeTalk\Notification\Calendar;

class VCalendarGenerator implements VCalendarGeneratorInterface
{
    protected \ilLanguage $lng;

    public function __construct(\ilLanguage $lng)
    {
        $this->lng = $lng;
    }

    public function fromTalkSeries(
        \ilObjEmployeeTalkSeries $series,
        \ilObjUser $employee,
        \ilObjUser $superior,
        Method $method = Method::PUBLISH
    ): string {
        $events = [];
        foreach ($series->getChildTalks() as $talk) {
            $events[] = $this->getEventfromTalk($talk, $employee, $superior);
        }

        $calendar = new VCalendar(
            $method,
            $series->getTitle(),
            md5($series->getType() . $series->getId()),
            ...$events
        );

        return $calendar->render();
    }

    protected function getEventfromTalk(
        \ilObjEmployeeTalk $talk,
        \ilObjUser $employee,
        \ilObjUser $superior,
        EventStatus $status = EventStatus::CONFIRMED
    ): VEvent {
        $data = $talk->getData();

        //The string \n must not be parsed by PHP, the email / calendar clients handel the line breaks by them self
        $description = $this->lng->txt('title') . ': ' . $talk->getTitle() . '\n';
        $description .= $this->lng->txt('desc') . ': ' . $talk->getLongDescription() . '\n';
        $description .= $this->lng->txt('location') . ': ' . $talk->getLongDescription() . '\n';
        $description .= $this->lng->txt('il_orgu_superior') . ': ' . $superior->getFullname() . '\n';
        $description .= $this->lng->txt('il_orgu_employee') . ': ' . $employee->getFullname() . '\n';

        return new VEvent(
            md5($talk->getType() . $talk->getId()),
            $description,
            $talk->getTitle(),
            0,
            $status,
            $superior->getFullname(),
            $superior->getEmail(),
            $employee->getFullname(),
            $employee->getEmail(),
            $data->getStartDate()->getUnixTime(),
            $data->getEndDate()->getUnixTime(),
            $data->isAllDay(),
            '',
            $data->getLocation()
        );
    }
}
