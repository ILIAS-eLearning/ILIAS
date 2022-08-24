<?php

declare(strict_types=1);

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

namespace ILIAS\EmployeeTalk\Service;

use ilObjEmployeeTalk;
use ilObjUser;
use ILIAS\DI\Container;

final class VEventFactory
{
    /**
     * @param ilObjEmployeeTalk $talk
     * @param string            $status VEventStatus
     * @return VEvent
     *
     * @see VEventStatus
     */
    public static function getInstanceFromTalk(ilObjEmployeeTalk $talk, string $status = VEventStatus::CONFIRMED): VEvent
    {
        $data = $talk->getData();
        $superior = new ilObjUser($talk->getOwner());
        $employee = new ilObjUser($talk->getData()->getEmployee());
        $superiorName = $superior->getFullname();

        return new VEvent(
            md5($talk->getType() . $talk->getId()),
            self::msOutlook2013Workaround($talk),
            $talk->getTitle(),
            0,
            $status,
            $superiorName,
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

    private static function msOutlook2013Workaround(ilObjEmployeeTalk $talk): string
    {

        /**
         * @var Container $DIC
         */
        global $DIC;
        $superior = new ilObjUser($talk->getOwner());
        $employee = new ilObjUser($talk->getData()->getEmployee());
        $language = $DIC->language();
        $language->loadLanguageModule('crs');

        //The string \n must not be parsed by PHP, the email / calendar clients handel the line breaks by them self
        $description = $language->txt('title') . ': ' . $talk->getTitle() . '\n';
        $description .= $language->txt('desc') . ': ' . $talk->getLongDescription() . '\n';
        $description .= $language->txt('location') . ': ' . $talk->getLongDescription() . '\n';
        $description .= $language->txt('il_orgu_superior') . ': ' . $superior->getFullname() . '\n';
        $description .= $language->txt('il_orgu_employee') . ': ' . $employee->getFullname() . '\n';

        return $description;
    }
}
