<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Accessibility;

/**
 * Inits the global page template for screen reade focus
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class GlobalPageHandler
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * @param \ilGlobalTemplateInterface $page
     */
    public static function initPage(\ilGlobalTemplateInterface $page)
    {
        global $DIC;

        $user = $DIC->user();

        if (is_object($user) && $user->getPref("screen_reader_optimization")) {
            $page->addOnLoadCode("il.Util.setStdScreenReaderFocus();");
        }
    }
}
