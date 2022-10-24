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
            $obj_def = $DIC["objDefinition"];

            $comp = $obj_def->getComponentForType($a_context_obj_type);
            $class = $obj_def->getClassName($a_context_obj_type);

            $class = "il" . $class . "NewsRendererGUI";
            $type_renderer_path = "./" . $comp . "/classes/class." . $class . ".php";
            if (is_file($type_renderer_path)) {
                $rend = new $class();
            } else {
                $rend = new ilNewsDefaultRendererGUI();
            }
            self::$renderer[$a_context_obj_type] = $rend;
        }

        return self::$renderer[$a_context_obj_type];
    }
}
