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

namespace ILIAS\UI\Component\ViewControl;

use ILIAS\UI\Component\Component;

/**
 * This describes a Mode Control
 */
interface Mode extends Component
{
    /**
     * set the currently active/engaged Button by label.
     *
     * @param string $label. The label of the button to activate
     */
    public function withActive(string $label): Mode;

    /**
     * get the label of the currently active/engaged button of the mode control
     *
     * @return string the label of the currently active button of the mode control
     */
    public function getActive(): ?string;

    /**
     * Get the array containing the actions and labels of the mode control
     *
     * @return array (string|string)[]. Array containing keys as label and values as actions.
     */
    public function getLabelledActions(): array;

    /**
    * Get the aria-label on the ViewControl
    */
    public function getAriaLabel(): string;
}
