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

namespace ILIAS\Questions\UserSettings;

use ILIAS\Language\Language;

enum EditingModes: string
{
    case Simple = 'simple';
    case Full = 'full';

    public function getLabelForInput(
        Language $lng
    ): string {
        return $lng->txt("editing_mode_{$this->value}");
    }

    public function getBylineForInput(
        Language $lng
    ): string {
        return $lng->txt("byline_editing_mode_{$this->value}");
    }

    public static function getDefaultMode(): self
    {
        return self::Simple;
    }
}
