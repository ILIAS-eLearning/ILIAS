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

namespace ILIAS\UI\examples\MainControls\SystemInfo;

use ILIAS\UI\Component\MainControls\SystemInfo;

/**
 * ---
 * description: >
 *   This example show how the UI-Elements itself looks like. For a full
 *   example use the example of the UI-Component Layout\Page\Standard.
 *
 * expected output: >
 *   Instead of but one message, ILIAS will display three messages in differently
 *   colored boxes. The intensity of the colors decreases from top to bottom.
 * ---
 */
function multiple()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $first = $f->mainControls()
        ->systemInfo('This is the first Message!', 'content of the first message...')
        ->withDenotation(SystemInfo::DENOTATION_BREAKING);
    $second = $f->mainControls()
        ->systemInfo('This is the second Message!', 'content of the second message...')
        ->withDenotation(SystemInfo::DENOTATION_IMPORTANT);
    $third = $f->mainControls()
        ->systemInfo('This is the third Message!', 'content of the third message...');

    return $renderer->render([$first, $second, $third]);
}
