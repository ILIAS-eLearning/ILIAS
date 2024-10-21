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

namespace ILIAS\Test\Questions\Presentation;

use ILIAS\Language\Language;

enum Types: string
{
    case RESULTS_VIEW_TYPE_SHOW = 'show';
    case RESULTS_VIEW_TYPE_HIDE = 'hide';

    public function getLabel(Language $lng): string
    {
        return match($this) {
            self::RESULTS_VIEW_TYPE_SHOW => $lng->txt('show_best_solution'),
            self::RESULTS_VIEW_TYPE_HIDE => $lng->txt('hide_best_solution')
        };
    }
}
