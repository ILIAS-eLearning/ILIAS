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

namespace ILIAS\UI\examples\Modal\Interruptive;

use ILIAS\UI\Component\Panel\Standard;

/**
 * ---
 * description: >
 *   Example for rendering a interruptive modal on a click onto a button.
 *
 * expected output: >
 *   Clicking "Show Modal" opens up a modal with some content.
 *   A click onto "Delete" will reload the page and displays a confirmation below the example.
 *   A click onto "Cancel" or Close Glyph will hide the modal.
 * ---
 */
function show_modal_on_button_click()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();
    $post_wrapper = $DIC->http()->wrapper()->post();
    $ctrl = $DIC->ctrl();

    $message = 'Are you sure you want to delete the following items?';
    $ctrl->setParameterByClass('ilsystemstyledocumentationgui', 'modal_nr', 1);
    $form_action = $ctrl->getFormActionByClass('ilsystemstyledocumentationgui');
    $icon = $factory->image()->standard('./assets/images/standard/icon_crs.svg', '');

    $items = [
        $factory->modal()->interruptiveItem()->standard('10', 'Course 1', $icon, 'Some description text'),
        $factory->modal()->interruptiveItem()->keyValue('20', 'Item Key', 'item value'),
        $factory->modal()->interruptiveItem()->standard('30', 'Course 3', $icon, 'Last but not least, a description'),
        $factory->modal()->interruptiveItem()->keyValue('50', 'Second Item Key', 'another item value'),
    ];
    $modal = $factory->modal()->interruptive('My Title', $message, $form_action)->withAffectedItems($items);
    $button = $factory->button()->standard('Show Modal', '')
        ->withOnClick($modal->getShowSignal());

    $out = [$button, $modal];

    // Display POST data of affected items in a panel
    if (
        $post_wrapper->has('interruptive_items') &&
        $request_wrapper->retrieve('modal_nr', $refinery->kindlyTo()->string()) === '1'
    ) {
        $out[] = $post_wrapper->retrieve('interruptive_items', $refinery->custom()->transformation(
            static fn(array $item_keys): Standard => $factory->panel()->standard(
                'Items deleted',
                $factory->legacy()->content('Number of items deleted: ' . count($item_keys))
            )
        ));
    }

    return $renderer->render($out);
}
