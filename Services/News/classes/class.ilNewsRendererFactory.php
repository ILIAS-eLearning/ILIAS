<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  News renderer factory
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup Services News
 */
class ilNewsRendererFactory
{
    /**
     * @var array of ilNewsRendererGUIs
     */
    protected static $renderer = array();

    /**
     * Get renderer
     *
     * @param
     * @return ilNewsRendererGUI
     */
    public static function getRenderer($a_context_obj_type)
    {
        global $DIC;

        if (!isset(self::$renderer[$a_context_obj_type])) {
            $obj_def = $DIC["objDefinition"];

            $comp = $obj_def->getComponentForType($a_context_obj_type);
            $class = $obj_def->getClassName($a_context_obj_type);

            $class = "il" . $class . "NewsRendererGUI";
            $type_renderer_path = "./" . $comp . "/classes/class." . $class . ".php";
            if (is_file($type_renderer_path)) {
                include_once($type_renderer_path);
                $rend = new $class();
            } else {
                include_once("./Services/News/classes/class.ilNewsDefaultRendererGUI.php");
                $rend = new ilNewsDefaultRendererGUI();
            }
            self::$renderer[$a_context_obj_type] = $rend;
        }

        return self::$renderer[$a_context_obj_type];
    }
}
