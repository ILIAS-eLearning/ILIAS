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

namespace ILIAS\UI\Component\Prompt;

use ILIAS\UI\Component;
use ILIAS\Data\URI;

/**
 * Interface Factory
 *
 * @package ILIAS\UI\Component\Prompt
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *      A Prompt interrupts a user to focus on a certain task or/and
     *      prompts for information without the user losing the current context.
     *      The Prompt is async by default and merely provides a wrapper,
     *      its contents are defined by the Prompt Response.
     *   composition: >
     *      The Prompt uses the HTML dialog tag.
     *   effect: >
     *      The contents of Prompt are loaded asynchronously by default;
     *      actions of Forms and targets of Links are "wrapped" to RPCs and thus
     *      stay in context of the Prompt, i.e. you may take roundtrips to the
     *      server and modify the Prompt's content without closing it.
     * context:
     *   - The Prompt requires a Prompt Response.
     *
     * rules:
     *   usage:
     *     1: >
     *      The server MUST answer with a PromptResponse Component
     *      to a request to the url provided to the Prompt.
     * ---
     * @return \ILIAS\UI\Component\Prompt\Prompt
     */
    public function standard(URI $async_url): Prompt;

    /**
     * ---
     * description:
     *   purpose: >
     *      A Prompt Response serves as a formalized wrapper around output of
     *      asynchrounous requests in order to provide contents for a Prompt.
     *      It allows for dedicated changes to recurring parts of Prompts,
     *      such as Title, Content or Buttons.
     *   composition: >
     *      The Prompt Response accepts Prompt Content to be handled by
     *      the Prompt.
     *   effect: >
     *       The sections of the Prompt Response are rendered to their respective
     *       parts of the Prompt.
     *       Forms and Links are automatically turned into async requests to
     *       stay in context of the Prompt.
     *       You may also tell the Prompt to close or redirect - after the
     *       request has been processed.
     * context:
     *   - The Prompt Response is used for Prompts.
     *
     * ---
     * @return \ILIAS\UI\Component\Prompt\Response
     */
    public function response(
        \ILIAS\UI\Component\Prompt\PromptContent $content
    ): Response;

    /**
     * ---
     * description:
     *   purpose: >
     *      Factors a Prompt Response without contents, but with a 'close'-command
     *      for the Prompt.
     *   composition: >
     *      The Close Response does not have any relevant manifestation.
     *   effect: >
     *      Tells the Prompt to close: when the Close Response is retrieved
     *      from the server, the Prompt is closed.
     * context:
     *   - The Close Response is used for Prompts.
     *
     * ---
     * @return \ILIAS\UI\Component\Prompt\Response
     */
    public function close(): Response;

    /**
     * ---
     * description:
     *   purpose: >
     *      Factors a Prompt Response without contents, but with a 'redirect'-command
     *      for the Prompt.
     *   composition: >
     *      The Redirect Response does not have any relevant manifestation.
     *   effect: >
     *      Tells the Prompt to redirect the page. When the response is called
     *      asynchronously, the server-side redirect will do so for the
     *      async call only.
     *      Use Redirect to redirect the client to the given URL.
     *
     * context:
     *   - The Redirect Response is used for Prompts.
     *
     * ---
     * @return \ILIAS\UI\Component\Prompt\Response
     */
    public function redirect(URI $redirect): Response;

}
