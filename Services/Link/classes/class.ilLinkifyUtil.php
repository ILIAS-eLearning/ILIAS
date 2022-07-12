<?php declare(strict_types=1);

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
 * Linkify utility class
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLinkifyUtil
{
    private static string $ver = "1_1";
    private static string $min = ".min";

    public static function initLinkify(?ilGlobalTemplateInterface $a_tpl = null) : void
    {
        global $DIC;

        $tpl = $DIC["tpl"];
        
        if ($a_tpl === null) {
            $a_tpl = $tpl;
        }

        foreach (self::getLocalJsPaths() as $p) {
            $a_tpl->addJavaScript($p);
        }
    }

    /**
     * Get paths of necessary js files
     * @return string[]
     */
    public static function getLocalJsPaths() : array
    {
        return [
            "./node_modules/linkifyjs/dist/linkify.min.js",
            "./node_modules/linkifyjs/dist/linkify-jquery.min.js",
            "./Services/Link/js/ilExtLink.js"
        ];
    }
}
