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

namespace ILIAS\UI\Implementation\Component\Progress\State\Bar;

/**
 * This enum represents the status of a Progress Bar's underlying process/task, which
 * can transition through various states of this enum. The Progress Bar will behave
 * differently according to each status.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
enum Status: string
{
    /** The progress of the process/task cannot be calculated (yet), but it has started processing. */
    case INDETERMINATE = 'indeterminate';

    /** The progress of the process/task can be calculated and has been provided. */
    case DETERMINATE = 'determinate';

    /** The process/task finished without errors. */
    case SUCCESS = 'success';

    /** The process/task could not be finished, or finished with errors. */
    case FAILURE = 'failure';
}
