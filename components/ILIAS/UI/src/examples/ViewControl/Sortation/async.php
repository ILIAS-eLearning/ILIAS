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

namespace ILIAS\UI\examples\ViewControl\Sortation;

/**
 * ---
 * description: >
 *   Async example show-casing how this control can be used, without reloading the page
 *
 * expected output: >
 *   ILIAS shows a base sortation symbol. Clicking the control will open a dropdown menu with three shy
 *   buttons "Default Ordering", "Most Recent Ordering" and "Oldest Ordering". Clicking the buttons will open a modal.
 *   The control now is labeled the same as the clicked button.
 * ---
 */
function async()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    //Initializing the options, note that the label is taken care of by JS
    $options = [
        'default_option' => 'Default',
        'latest' => 'Most Recent',
        'oldest' => 'Oldest'
    ];

    //Note that the selected option needs to be displayed in the label
    $select_option = 'default_option';
    if ($request_wrapper->has('sortation') && $request_wrapper->retrieve('sortation', $refinery->kindlyTo()->string())) {
        $select_option = $request_wrapper->retrieve('sortation', $refinery->kindlyTo()->string());
    }

    //Generation of the UI Component
    $modal = $f->modal()->lightbox($f->modal()->lightboxTextPage('Note: This is just used to show case, how 
        this control can be used,to change an other components content.', "Sortation has changed: " . $options[$select_option]));
    $s = $f->viewControl()->sortation($options, $select_option)
            ->withTargetURL($DIC->http()->request()->getRequestTarget(), 'sortation')
            ->withOnSort($modal->getShowSignal());

    //Rendering
    return $renderer->render([$s,$modal]);
}
