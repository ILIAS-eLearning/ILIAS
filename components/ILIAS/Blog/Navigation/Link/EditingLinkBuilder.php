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

namespace ILIAS\Blog\Navigation\Link;

use ILIAS\Blog\Editing\EditingGUI;

class EditingLinkBuilder extends PresentationLinkBuilder
{
    protected function getPathForView(string $view): array
    {
        return match ($view) {
            self::VIEW_MAIN => [
                \ilObjBlogGUI::class,
                EditingGUI::class
            ],
            self::VIEW_POSTING => [
                \ilObjBlogGUI::class,
                EditingGUI::class,
                \ilBlogPostingGUI::class
            ],
        };
    }

    protected function getCmdForView(string $view): string
    {
        return match ($view) {
            self::VIEW_MAIN => "render",
            self::VIEW_POSTING => "edit",
        };
    }
}
