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

namespace ILIAS\UI\Component\Button;

use ILIAS\UI\Component\Component;

/**
 * Engageable Components have an "engaged" state and will be displayed accordingly.
 */
interface Engageable extends Component
{
    /**
     * Returns whether the button is stateful or not.
     * Engageable must be explicitly turned on by initializing the Button with
     * a state (withEngagedState), since not all Buttons are used as toggles
     * and thus should not bear an aria-pressed attribute.
     */
    public function isEngageable(): bool;

    /**
     * Get a copy of the Engageable Button with engaged state for $state=true
     * and with disengaged state for $state=false.
     *
     * @return static
     */
    public function withEngagedState(bool $state);

    /**
     * Returns whether the button is currently engaged or not.
     */
    public function isEngaged(): bool;
}
