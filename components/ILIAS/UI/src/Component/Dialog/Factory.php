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

namespace ILIAS\UI\Component\Dialog;

use ILIAS\UI\Component;
use ILIAS\Data\URI;

/**
 * Interface Factory
 *
 * @package ILIAS\UI\Component\Dialog
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *      A Dialog interrupts a user to focus on a certain task or/and
     *      prompts for information without the user losing the current context.
     *      The Dialog is async by default and merely provides a wrapper,
     *      its contents are defined by the Dialog Response.
     *   composition: >
     *      The Dialog uses the HTML dialog tag.
     *   effect: >
     *      The contents of Dialog are loaded asynchronously by default;
     *      actions of Forms and targets of Links are "wrapped" to RPCs and thus
     *      stay in context of the Dialog, i.e. you may take roundtrips to the
     *      server and modify the Dialog's content without closing it.
     * context:
     *   - The Dialog requires a Dialog Response.
     *
     * rules:
     *   usage:
     *     1: >
     *      The server MUST answer with a DialogResponse Component
     *      to a request to the url provided to the Dialog.
     * ---
     * @return \ILIAS\UI\Component\Dialog\Dialog
     */
    public function standard(URI $async_url): Dialog;

    /**
     * ---
     * description:
     *   purpose: >
     *      A Dialog Response serves as a formalized wrapper around output of
     *      asynchrounous requests in order to provide contents for a Dialog.
     *      It allows for dedicated changes to recurring parts of Dialogs,
     *      such as Title, Content or Buttons.
     *   composition: >
     *      The Dialog Response accepts Dialog Content to be handled by
     *      the Dialog.
     *   effect: >
     *       The sections of the Dialog Response are rendered to their respective
     *       parts of the Dialog.
     *       Forms and Links are automatically turned into async requests to
     *       stay in context of the Dialog.
     *       You may also tell the Dialog to close - after the request has been processed.
     * context:
     *   - The Dialog Response is used for Dialogs.
     *
     * ---
     * @return \ILIAS\UI\Component\Dialog\Response
     */
    public function response(
        \ILIAS\UI\Component\Dialog\DialogContent $content
    ): Response;

    /**
     * ---
     * description:
     *   purpose: >
     *      Factors a Dialog Response without contents, but with a 'close'-command
     *      for the Dialog.
     *   composition: >
     *      The Close Response does not have any relevant manifestation.
     *   effect: >
     *      Tells the Dialog to close: when the Close Response is retrieved
     *      from the server, the Dialog is closed.
     * context:
     *   - The Close Response is used for Dialogs.
     *
     * ---
     * @return \ILIAS\UI\Component\Dialog\Response
     */
    public function close(): Response;
}
