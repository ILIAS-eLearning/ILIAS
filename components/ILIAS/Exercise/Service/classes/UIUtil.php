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

namespace ILIAS\Exercise;

use ILIAS\Exercise\Assignment\Mandatory\MandatoryAssignmentsManager;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Component\Link\Link;

use function PHPUnit\Framework\isInstanceOf;

class UIUtil
{
    public function __construct(
    ) {
    }

    public function formatTextInput(string $text): string
    {
        $text = \ilRTE::_replaceMediaObjectImageSrc($text, 1);
        if (!str_contains($text, "<p>")) {
            $text = nl2br($text);
        }
        return $text;
    }
}
