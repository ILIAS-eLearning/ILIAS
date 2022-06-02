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
 
namespace ILIAS\UI\Component\ViewControl;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Button\Button;

/**
 * This describes a Section Control
 */
interface Section extends Component
{

    /**
     * Returns the action executed by clicking on previous.
     */
    public function getPreviousActions() : Button;

    /**
     * Returns the action executed by clicking on next.
     */
    public function getNextActions() : Button;

    /**
     * Returns the Default- or Month Button placed in the middle of the control
     *
     * @return Component the Default- or Month Button placed in the middle of the control
     */
    public function getSelectorButton() : Component;
}
