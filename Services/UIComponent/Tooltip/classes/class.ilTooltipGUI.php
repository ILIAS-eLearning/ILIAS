<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This is a utility class for the yui tooltips.
* this only works, if a parent has class="yui-skin-sam" attached.
*/
class ilTooltipGUI
{
    protected static $initialized = false;
    protected static $library_initialized = false;
    
    /**
     * Adds a tooltip to an HTML element
     *
     * @param string $a_el_id element id
     * @param string $a_el_id tooltip text
     * @param string $a_el_id element id of container the tooltip should be added to
     */
    public static function addTooltip(
        $a_el_id,
        $a_text,
        $a_container = "",
        $a_my = "bottom center",
        $a_at = "top center",
        $a_use_htmlspecialchars = true
    ) {
        // to get rid of globals here, we need to change the
        // process in learning modules, e.g. which does not work with $DIC (since it does not
        // use the standard template)
        $tpl = $GLOBALS["tpl"];
        
        self::init();

        $code = self::getTooltip(
            $a_el_id,
            $a_text,
            $a_container,
            $a_my,
            $a_at,
            $a_use_htmlspecialchars
        );
        $tpl->addOnLoadCode($code);
    }
    
    /**
     * Get tooltip js code
     *
     * @param string $a_el_id element id
     * @param string $a_el_id tooltip text
     * @param string $a_el_id element id of container the tooltip should be added to
     */
    public static function getToolTip(
        $a_el_id,
        $a_text,
        $a_container = "",
        $a_my = "bottom center",
        $a_at = "top center",
        $a_use_htmlspecialchars = true
    ) {
        $addstr = "";

        // not needed, just make sure the position plugin is included
        //		$addstr.= ", position: {viewport: $('#fixed_content')}";
        
        if ($a_container != "") {
            $addstr.= ", container: '" . $a_container . "'";
        }

        if ($a_use_htmlspecialchars) {
            $a_text = htmlspecialchars(str_replace(array("\n", "\r"), "", $a_text));
        } else {
            $a_text = str_replace(array("\n", "\r", "'", '"'), array("", "", "\'", '\"'), $a_text);
        }
        return 'il.Tooltip.add("' . $a_el_id . '", {' .
            ' context:"' . $a_el_id . '",' .
            ' my:"' . $a_my . '",' .
            ' at:"' . $a_at . '",' .
            ' text:"' . $a_text . '" ' . $addstr . '} );';
    }
    
    /**
     * Initializes the needed tooltip libraries.
     */
    public static function init()
    {
        // for globals use, see comment above
        $tpl = $GLOBALS["tpl"];
        
        if (!self::$initialized) {
            $tpl->addCss("./libs/bower/bower_components/qtip2/dist/jquery.qtip.min.css");
            $tpl->addJavascript("./libs/bower/bower_components/qtip2/dist/jquery.qtip.min.js");
            $tpl->addJavascript("./Services/UIComponent/Tooltip/js/ilTooltip.js");
            $tpl->addOnLoadCode('il.Tooltip.init();', 3);
            self::$initialized = true;
        }
    }
}
