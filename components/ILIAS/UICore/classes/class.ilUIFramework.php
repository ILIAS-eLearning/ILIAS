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

declare(strict_types=1);

/**
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilUIFramework
{
    public const BOOTSTRAP_JS = "./public/node_modules/bootstrap/dist/js/bootstrap.min.js";

    public static function init(ilGlobalTemplateInterface $template = null): void
    {
        global $DIC;

        $template = $template ?? $DIC->ui()->mainTemplate();
        $template->addJavaScript(
            self::BOOTSTRAP_JS,
            true,
            0
        );
    }
}
