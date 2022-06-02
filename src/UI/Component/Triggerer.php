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
 
namespace ILIAS\UI\Component;

use ILIAS\UI\Implementation\Component\TriggeredSignal;

/**
 * Interface Triggerer
 *
 * Describes a component that can trigger signals of other components on given events, such as click or hover.
 * All supported events are abstracted with interfaces (see Clickable, Hoverable).
 * Example: A button can trigger the show signal of a modal on click (which will open the modal on button click).
 *
 * @package ILIAS\UI\Component
 */
interface Triggerer extends JavaScriptBindable
{
    /**
     * Get a component like this but reset any triggered signals of other components
     *
     * @return static
     */
    public function withResetTriggeredSignals();

    /**
     * Get all triggered signals of this component
     *
     * @return TriggeredSignal[]
     */
    public function getTriggeredSignals() : array;
}
