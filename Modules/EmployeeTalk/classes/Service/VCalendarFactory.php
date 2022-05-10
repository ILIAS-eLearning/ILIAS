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
use ilException;
use ilObjEmployeeTalkSeries;

final class VCalendarFactory
{
    /**
     * @param ilObjEmployeeTalkSeries $series
     * @param string                  $method
     * @return VCalender
     */
    public static function getInstanceFromTalks(\ilObjEmployeeTalkSeries $series, string $method = VCalenderMethod::PUBLISH): VCalender
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $children = $tree->getChildIds($series->getRefId());
        $talks = array_map(function ($val): ilObjEmployeeTalk {
            return new ilObjEmployeeTalk(intval($val), true);
        }, $children);

        $firstTalk = $talks[0];

        $events = [];

        foreach ($talks as $talk) {
            $events[] = VEventFactory::getInstanceFromTalk($talk);
        }

        return new VCalender(
            $firstTalk->getTitle(),
            md5($series->getType() . $series->getId()),
            $events,
            $method
        );
    }

    /**
     * @param ilObjEmployeeTalkSeries  $series
     * @param string                   $title
     * @param string                   $method
     * @return VCalender
     */
    public static function getEmptyInstance(
        ilObjEmployeeTalkSeries $series,
        string $title,
        string $method = VCalenderMethod::PUBLISH
    ): VCalender {
        return new VCalender(
            $title,
            md5($series->getType() . $series->getId()),
            [],
            $method
        );
    }
}
