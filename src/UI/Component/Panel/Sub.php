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

namespace ILIAS\UI\Component\Panel;

use ILIAS\UI\Component\Card\Card;
use ILIAS\UI\Component\Panel\Secondary\Secondary;

/**
 * This describes a Sub Panel.
 */
interface Sub extends Panel
{
    /**
     * Sets the component to be displayed on the right of the Sub Panel
     * @param Card|Secondary $component
     */
    public function withFurtherInformation($component): Sub;

    /**
     * Gets the component to be displayed on the right of the Sub Panel
     * @return Card|Secondary|null
     */
    public function getFurtherInformation();
}
