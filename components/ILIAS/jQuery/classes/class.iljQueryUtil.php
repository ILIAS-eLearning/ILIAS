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
 * jQuery utilities
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 */
class iljQueryUtil
{
    /**
     * @var string Suffix for minified File
     */
    private static $min = ".min";


    /**
     * inits and adds the jQuery JS-File to the global or a passed template
     *
     * @param \ilTemplate $a_tpl global $tpl is used when null
     */
    public static function initjQuery(ilGlobalTemplateInterface $a_tpl = null): void
    {
        global $DIC;

        self::$min = "";
        if ($a_tpl === null) {
            $a_tpl = $DIC["tpl"];
        }

        $a_tpl->addJavaScript(self::getLocaljQueryPath(), true, 0);
        $a_tpl->addJavaScript('./node_modules/jquery-migrate/dist/jquery-migrate.min.js', true, 0);
    }


    /**
     * inits and adds the jQuery-UI JS-File to the global template
     * (see included_components.txt for included components)
     */
    public static function initjQueryUI(ilGlobalTemplateInterface $a_tpl = null): void
    {
        global $DIC;

        if ($a_tpl === null) {
            $a_tpl = $DIC["tpl"];
        }

        // Important: jQueryUI has to be included before(!) the bootstrap JS file
        $a_tpl->addJavaScript(self::getLocaljQueryUIPath(), true, 0);
    }


    /**
     * @return string local path of jQuery file
     */
    public static function getLocaljQueryPath(): string
    {
        return "./node_modules/jquery/dist/jquery" . self::$min . ".js";
    }


    /**
     * @return string local path of jQuery UI file
     */
    public static function getLocaljQueryUIPath(): string
    {
        return "./node_modules/jquery-ui-dist/jquery-ui" . self::$min . ".js";
    }

    //
    // Maphilight plugin
    //

    /**
     * Inits and add maphilight to the general template
     */
    public static function initMaphilight(): void
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        $tpl->addJavaScript(self::getLocalMaphilightPath(), true, 1);
    }


    /**
     * Get local path of maphilight file
     */
    public static function getLocalMaphilightPath(): string
    {
        return "./node_modules/maphilight/jquery.maphilight.min.js";
    }
}
