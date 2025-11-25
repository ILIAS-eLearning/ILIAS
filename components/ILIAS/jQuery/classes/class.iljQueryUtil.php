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
    public static function initjQuery(?ilGlobalTemplateInterface $a_tpl = null): void
    {
        global $DIC;

        self::$min = "";
        if ($a_tpl === null) {
            $a_tpl = $DIC["tpl"];
        }

        $a_tpl->addJavaScript(self::getLocaljQueryPath(), true, 0);
    }


    /**
     * @return string local path of jQuery file
     */
    public static function getLocaljQueryPath(): string
    {
        return "assets/js/jquery" . self::$min . ".js";
    }
}
