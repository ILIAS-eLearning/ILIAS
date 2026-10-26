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

/**
 * The activityProgress of an Assignment and Grade Services score, as stored in lti_consumer_grades.
 * The learning progress (ilLPStatusLtiOutcome) reads it by this name.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
enum ilLTIToolActivityProgress: string
{
    case INITIALIZED = 'Initialized';
    case STARTED = 'Started';
    case IN_PROGRESS = 'InProgress';
    case SUBMITTED = 'Submitted';
    case COMPLETED = 'Completed';

    public function isInProgress(): bool
    {
        return $this === self::STARTED || $this === self::IN_PROGRESS;
    }
}
