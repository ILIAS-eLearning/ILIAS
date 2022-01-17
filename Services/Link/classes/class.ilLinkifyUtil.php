<?php

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
 * Linkify utility class
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLinkifyUtil
{
    private static $ver = "1_1";
    private static $min = ".min";

    /**
     * Init Linkify
     */
    public static function initLinkify(ilTemplate $a_tpl = null) : void
    {
        global $DIC;

        $tpl = $DIC["tpl"];
        
        if ($a_tpl == null) {
            $a_tpl = $tpl;
        }

        foreach (self::getLocalJsPaths() as $p) {
            $a_tpl->addJavaScript($p);
        }
    }

    /**
     * Get paths of necessary js files
     */
    public static function getLocalJsPaths() : array
    {
        return array(
            "./node_modules/linkifyjs/dist/linkify.min.js",
            "./node_modules/linkifyjs/dist/linkify-jquery.min.js",
            "./Services/Link/js/ilExtLink.js"
        );
    }
}
