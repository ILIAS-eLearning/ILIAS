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
 * Interface for event handling. See class.ilAppEventHandler.php for
 * more information.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
interface ilAppEventListener
{
    /**
    * Handle an event in a listener.
    * @param	string $a_component component, e.g. "Modules/Forum" or "Services/User"
    * @param	string $a_event     event e.g. "createUser", "updateUser", "deleteUser", ...
    * @param	array  $a_parameter parameter array (assoc), array("name" => ..., "phone_office" => ...)
    */
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter) : void;
}
