<?php declare(strict_types=1);

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

use ILIAS\UI\Component\Signal;

/**
 * This describes a toggle button.
 */
interface Toggle extends Button
{
    /**
     * Get the action of the Toggle Button when it is set from off to on.
     *
     * @return	string|Signal[]
     */
    public function getActionOn();

    /**
     * Get the action of the Toggle Button when it is set from on to off.
     *
     * @return	string|Signal[]
     */
    public function getActionOff();

    public function withAdditionalToggleOnSignal(Signal $signal) : Toggle;

    public function withAdditionalToggleOffSignal(Signal $signal) : Toggle;
}
