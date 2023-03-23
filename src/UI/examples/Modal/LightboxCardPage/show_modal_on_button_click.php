<?php

declare(strict_types=1);

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

namespace ILIAS\UI\examples\Modal\LightboxCardPage;

function show_modal_on_button_click()
{
    global $DIC;

    $card = $DIC->ui()->factory()->card()->standard("Some card title");
    $box = $DIC->ui()->factory()->modal()->lightboxCardPage($card);
    $modal = $DIC->ui()->factory()->modal()->lightbox($box);

    $button = $DIC->ui()->factory()
        ->button()
        ->standard('Show Modal', '')
        ->withOnClick($modal->getShowSignal());

    return $DIC->ui()->renderer()->render([$button, $modal]);
}
