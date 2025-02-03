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

namespace ILIAS\UI\Component\Progress;

use ILIAS\UI\Component\Triggerable;
use ILIAS\UI\Component\Triggerer;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Signal;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface Bar extends Component, Triggerable, Triggerer
{
    /**
     * Get a Signal which can be used to update the current Progress Bar.
     */
    public function getUpdateSignal(): Signal;

    /**
     * Get a Signal which can be used to reset the current Progress Bar.
     */
    public function getResetSignal(): Signal;
}
