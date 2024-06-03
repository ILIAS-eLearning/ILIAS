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

namespace ILIAS\UI\Component\Chart\ProgressBar;

use ILIAS\Data\URI;

interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     The Standard Progress Bar is used when displaying the progress of an operation is the main UI
     *     element displayed on a page.
     *   composition: >
     *     The Standard Progress Bar is composed of a horizontal bar that represents the entire range of an operation.
     *     Within this bar, a colour in percentage width indicates how much of the total scope has already been achieved.
     *     The colour changes when the operation is either successful (100%) or has failed.
     *     The bar also contains a textual representation of the percentage value that has already been achieved.
     *     In the event of an error, an error message is displayed below the bar. a button allows you to exit the view.
     *     In the event of success, you are optionally forwarded to a new view, which displays a success message.
     *   effect: >
     *     The progress bar is updated asynchronously
     *
     * rules:
     *   composition:
     *     1: The standalone progress bar MUST contain a success message, a general error message and a success URL.
     *     2: The standalone progress bar MUST contain a success message, a general error message and a success URL.
     *
     * ---
     * @param int|float $maximum          Maximum reachable value. Defaults to 100.
     * @param int|float $current          Current value to be displayed by main bar, defaults to 0.
     * @return \ILIAS\UI\Component\Chart\ProgressBar\Standard
     */
    public function standard(
        string $title,
        ProgressProvider $callback,
        int|float $maximum = 100,
        int|float $current = 0
    ): Standard;

}
