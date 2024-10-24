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

/**
 * ---
 * description: >
 *   Example for rendering an interruptive modal.
 *
 * expected output: >
 *   ILIAS shows no example because the modal is not called. This behaviour is expected.
 * ---
 */
function base()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $message = 'Are you sure you want to delete the following items?';
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
    $modal = $factory->modal()->interruptive('My Title', $message, $form_action);

    // Note: This modal is just rendered in the DOM but not displayed
    // because its show/close signals are not triggered by any components
    return $renderer->render($modal);
}
