<?php

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

/**
 * News renderer factory
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNewsRendererFactory
{
    /** @var array<string, ilNewsDefaultRendererGUI> */
    protected static array $renderer = [];

    public static function getRenderer(string $a_context_obj_type): ilNewsRendererGUI
    {
        global $DIC;

        if (!isset(self::$renderer[$a_context_obj_type])) {
            $class_name = $DIC['objDefinition']->getClassName($a_context_obj_type);
            $class = "il{$class_name}NewsRendererGUI";

            self::$renderer[$a_context_obj_type] = class_exists($class) ? new $class() : new ilNewsDefaultRendererGUI();
        }

        return self::$renderer[$a_context_obj_type];
    }
}
