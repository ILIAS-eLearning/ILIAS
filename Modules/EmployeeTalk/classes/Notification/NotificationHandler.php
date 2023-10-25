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

namespace ILIAS\EmployeeTalk\Notification;

use ILIAS\EmployeeTalk\Notification\Calendar\VCalendarGeneratorInterface;

class NotificationHandler implements NotificationHandlerInterface
{
    protected VCalendarGeneratorInterface $vcalendar_generator;

    public function __construct(
        VCalendarGeneratorInterface $vcalendar_generator
    ) {
        $this->vcalendar_generator = $vcalendar_generator;
    }

    public function send(
        NotificationType $type,
        \ilObjEmployeeTalk ...$affected_talks
    ): void {
        if (count($affected_talks) === 0) {
            return;
        }

        $superior = new \ilObjUser($affected_talks[0]->getOwner());
        $employee = new \ilObjUser($affected_talks[0]->getData()->getEmployee());

        switch ($type) {
            case NotificationType::INVITATION:
                $subject_key = 'notification_talks_subject';
                $message_key = 'notification_talks_created';
                $add_goto = true;
                break;

            case NotificationType::CANCELLATION:
                $subject_key = 'notification_talks_subject_update';
                $message_key = 'notification_talks_removed';
                $add_goto = false;
                break;

            case NotificationType::UPDATE:
            default:
                $subject_key = 'notification_talks_subject_update';
                $message_key = 'notification_talks_updated';
                $add_goto = true;
                break;
        }

        $add_time = $affected_talks[0]->getData()->isAllDay() ? 0 : 1;
        $format = \ilCalendarUtil::getUserDateFormat($add_time, true);
        $timezone = $employee->getTimeZone();

        $notification = new Notification(
            $employee,
            $superior,
            $affected_talks[0]->getRefId(),
            $affected_talks[0]->getTitle(),
            $affected_talks[0]->getDescription(),
            $affected_talks[0]->getData()->getLocation(),
            $subject_key,
            $message_key,
            $this->vcalendar_generator->fromTalkSeries($affected_talks[0]->getParent(), $employee, $superior),
            $add_goto,
            ...$this->extractFormattedDates($format, $timezone, ...$affected_talks)
        );
        $notification->send();
    }

    /**
     * @return string[]
     */
    protected function extractFormattedDates(
        string $format,
        string $timezone,
        \ilObjEmployeeTalk ...$talks
    ): array {
        $dates = [];
        foreach ($talks as $talk) {
            $dates[] = $talk->getData()->getStartDate();
        }

        usort($dates, function (\ilDateTime $a, \ilDateTime $b) {
            $a = $a->getUnixTime();
            $b = $b->getUnixTime();
            if ($a === $b) {
                return 0;
            }
            return $a < $b ? -1 : 1;
        });

        $formatted_dates = [];
        foreach ($dates as $date) {
            $formatted_dates[] = $date->get(IL_CAL_FKT_DATE, $format, $timezone);
        }
        return $formatted_dates;
    }
}
