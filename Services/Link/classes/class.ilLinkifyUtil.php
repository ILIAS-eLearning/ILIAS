<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Linkify utility class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class ilLinkifyUtil
{
    private static $ver = "1_1";
    private static $min = ".min";

    /**
     * Init Linkify
     *
     * @param ilTemplate $a_tpl template
     */
    public static function initLinkify($a_tpl = null)
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
    public static function getLocalJsPaths()
    {
        return array(
            "./Services/Link/lib/linkify/" . self::$ver . "/jquery.linkify" . self::$min . ".js",
            "./Services/Link/js/ilExtLink.js"
        );
    }
}
