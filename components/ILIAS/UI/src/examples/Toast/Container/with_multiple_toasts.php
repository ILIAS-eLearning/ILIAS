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

namespace ILIAS\UI\examples\Toast\Container;

/**
 * ---
 * description: >
 *   Example for rendring a toast container with multiple toasts.
 *
 * expected output: >
 *   ILIAS shows a blue button. Clicking that button opens three different messages in the right top edge which
 *   disappear after a few seconds.
 * ---
 */
function with_multiple_toasts(): string
{
    global $DIC;
    $tc = $DIC->ui()->factory()->toast()->container();

    $toasts = [
        $DIC->ui()->factory()->toast()->standard(
            'Example 1',
            $DIC->ui()->factory()->symbol()->icon()->standard('mail', 'Test')
        ),
        $DIC->ui()->factory()->toast()->standard(
            'Example 2',
            $DIC->ui()->factory()->symbol()->icon()->standard('mail', 'Test')
        ),
        $DIC->ui()->factory()->toast()->standard(
            'Example 3',
            $DIC->ui()->factory()->symbol()->icon()->standard('mail', 'Test')
        )
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
