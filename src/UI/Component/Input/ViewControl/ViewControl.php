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
 
namespace ILIAS\UI\Component\Input\ViewControl;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Triggerer;

/**
 * This describes the basis of all View Control Inputs.
 */
interface ViewControl extends Component, Input, Triggerer
{
    /**
     * Trigger this signal when the control is being operated.
     */
    public function withOnChange(Signal $signal) : ViewControl;
}
