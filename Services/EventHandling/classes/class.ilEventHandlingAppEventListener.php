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
 * Dummy listener used for unit tests
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilEventHandlingAppEventListener implements ilAppEventListener
{
    /**
     * @throws ilEventHandlingTestException
     */
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter) : void
    {
        if ($a_component == "MyTestComponent" &&
            $a_event == "MyEvent" &&
            isset($a_parameter["par1"]) &&
            $a_parameter["par1"] == "val1" &&
            isset($a_parameter["par2"]) &&
            $a_parameter["par2"] == "val2"
        ) {
            throw new ilEventHandlingTestException("");
        }
    }
}
