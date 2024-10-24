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

namespace ILIAS\UI\examples\Toast\Standard;

/**
 * ---
 * description: >
 *   Example for rendering a standard toast including a description.
 *
 * expected output: >
 *   ILIAS shows a blue button "Show". Clicking onto the button opens a message "Example" including a description on the
 *   right top edge and which disappears after a few seconds.
 * ---
 */
function with_description(): string
{
    global $DIC;
    $tc = $DIC->ui()->factory()->toast()->container();

    $toasts = [
        $DIC->ui()->factory()->toast()->standard(
            'Example',
            $DIC->ui()->factory()->symbol()->icon()->standard('info', 'Test')
        )->withDescription('This is an example description.')
    ];

    $toasts = base64_encode($DIC->ui()->renderer()->renderAsync($toasts));
    $button = $DIC->ui()->factory()->button()->standard($DIC->language()->txt('show'), '');
    $button = $button->withAdditionalOnLoadCode(function ($id) use ($toasts) {
        return "$id.addEventListener('click', () => {
            $id.parentNode.querySelector('.il-toast-container').innerHTML = atob('$toasts');
            $id.parentNode.querySelector('.il-toast-container').querySelectorAll('script').forEach(element => {
                let newScript = document.createElement('script');
                newScript.innerHTML = element.innerHTML;
                element.parentNode.appendChild(newScript);
            })
        });";
    });

    return $DIC->ui()->renderer()->render([$button,$tc]);
}
