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

namespace ILIAS\UI\examples\Modal\Interruptive;

function show_modal_on_button_click_async_rendered()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();
    $post_wrapper = $DIC->http()->wrapper()->post();
    $ctrl = $DIC->ctrl();

    $message = 'Are you sure you want to delete the following item?';
    $ctrl->setParameterByClass('ilsystemstyledocumentationgui', 'modal_nr', "2");
    $form_action = $ctrl->getFormActionByClass('ilsystemstyledocumentationgui');
    $items = ['First Item', 'Second Item', 'Third Item'];

    // Check if this is the ajax request to deliver the new modal showing the affected item
    if ($request_wrapper->has('item')) {
        $id = $request_wrapper->retrieve('item', $refinery->kindlyTo()->string());
        $item = $items[$id];
        $affected_item = $factory->modal()->interruptiveItem()->standard($id, $item);
        $modal = $factory->modal()->interruptive('Delete Items', $message, $form_action)
            ->withAffectedItems([$affected_item]);
        echo $renderer->render($modal);
        exit();
    }

    // Create a button per item
    $out = [];
    foreach ($items as $i => $item) {
        $ajax_url = $_SERVER['REQUEST_URI'] . '&item=' . $i;
        $modal = $factory->modal()->interruptive('', '', '')
            ->withAsyncRenderUrl($ajax_url);
        $button = $factory->button()->standard('Delete ' . $item, '#')
            ->withOnClick($modal->getShowSignal());
        $out[] = $button;
        $out[] = $modal;
    }

    // Display POST data of affected items in a panel
    if (
        $post_wrapper->has('interruptive_items') &&
        $request_wrapper->has('modal_nr') && $request_wrapper->retrieve('modal_nr', $refinery->kindlyTo()->string()) === '2'
    ) {
        $out[] = $post_wrapper->retrieve('interruptive_items', $refinery->custom()->transformation(
            function ($v) use ($factory, $post_wrapper, $items) {
                return $factory->panel()->standard('Affected Item', $factory->legacy($items[$v[0]]));
            }
        ));
    }

    return $renderer->render($out);
}
