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
 * This is a utility class for the yui tooltips.
 * this only works, if a parent has class="yui-skin-sam" attached.
 *
 * @deprecated 10
 */
class ilTooltipGUI
{
    protected static bool $initialized = false;
    protected static bool $library_initialized = false;

    public static function addTooltip(
        string $a_el_id,
        string $a_text,
        string $a_container = "",
        string $a_my = "bottom center",
        string $a_at = "top center",
        bool $a_use_htmlspecialchars = true
    ): void {
        // to get rid of globals here, we need to change the
        // process in learning modules, e.g. which does not work with $DIC (since it does not
        // use the standard template)
        $tpl = $GLOBALS["tpl"];

        self::init();

        $code = self::getToolTip(
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
     */
    public static function getToolTip(
        string $a_el_id,
        string $a_text,
        string $a_container = "",
        string $a_my = "bottom center",
        string $a_at = "top center",
        bool $a_use_htmlspecialchars = true
    ): string {
        $addstr = "";

        // not needed, just make sure the position plugin is included
        //		$addstr.= ", position: {viewport: $('#fixed_content')}";

        if ($a_container !== "") {
            $addstr .= ", container: '" . $a_container . "'";
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
    public static function init(): void
    {
        // for globals use, see comment above
        $tpl = $GLOBALS["tpl"];

        if (!self::$initialized) {
            $tpl->addCss("./node_modules/qtip2/dist/jquery.qtip.min.css");
            $tpl->addJavascript("./node_modules/qtip2/dist/jquery.qtip.min.js");
            $tpl->addJavascript("./Services/UIComponent/Tooltip/js/ilTooltip.js");

            // use setTimeout as a workaround, since the last parameter is ignored
            $tpl->addOnLoadCode('setTimeout(function() {il.Tooltip.init();}, 500);', 3);
            self::$initialized = true;
        }
    }
}
