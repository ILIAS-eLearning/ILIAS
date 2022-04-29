<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Abstract parent class for all event hook plugin classes.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class ilEventHookPlugin extends ilPlugin
{
    /**
     * Handle the event
     *
     * @param	string		component, e.g. "Services/User"
     * @param	string		event, e.g. "afterUpdate"
     * @param	array		array of event specific parameters
     */
    abstract public function handleEvent(
        string $a_component,
        string $a_event,
        array $a_parameter
    ) : void;
}
