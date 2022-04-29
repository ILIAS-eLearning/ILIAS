<?php declare(strict_types=1);

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilUIFramework
{
    public const BOWER_BOOTSTRAP_JS = "./node_modules/bootstrap/dist/js/bootstrap.min.js";

    public static function init(ilGlobalTemplateInterface $template = null) : void
    {
        global $DIC;

        $template = $template ?? $DIC->ui()->mainTemplate();
        $template->addJavaScript(
            self::BOWER_BOOTSTRAP_JS,
            true,
            0
        );
    }
}
