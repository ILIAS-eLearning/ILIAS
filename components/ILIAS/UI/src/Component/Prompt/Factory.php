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
use ILIAS\UI\URLBuilder;

interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *      A Prompt interrupts a user to focus on a certain task or/and
     *      prompts for information without the user losing the current context.
     *      The Prompt is async by default and merely provides a wrapper,
     *      its contents are defined by the Prompt's State.
     *   composition: >
     *      The Prompt uses the HTML dialog tag.
     *   effect: >
     *      The contents of Prompt are loaded asynchronously by default;
     *      actions of Forms and targets of Links are "wrapped" to RPCs and thus
     *      stay in context of the Prompt, i.e. you may take roundtrips to the
     *      server and modify the Prompt's content without closing it.
     * context:
     *   - The Prompt requires a Prompt State.
     *
     * rules:
     *   usage:
     *     1: >
     *      The server MUST answer with an Prompt State Component
     *      to a request to the url provided to the Prompt via the URLBuilder.
     * ---
     * @return \ILIAS\UI\Component\Prompt\Prompt
     */
    public function standard(URLBuilder $url_builder): Prompt;

    /**
     * ---
     * description:
     *   purpose: >
     *      Prompt States serve as a formalized wrapper around output of
     *      asynchrounous requests in order to provide contents and commands
     *      for a Prompt.
     *      They allow for dedicated changes to recurring parts of Prompts,
     *      such as Title, Content or Buttons.
     *   composition: >
     *      The State will render a div-element containing sections for
     *      its respective parts.
     *   effect: >
     *       The sections of the Prompt State are rendered to their respective
     *       parts of the Prompt.
     *       Forms and Links are automatically turned into async requests to
     *       stay in context of the Prompt.
     *       You may also tell the Prompt to close or redirect - after the
     *       request has been processed.
     *
     * ---
     * @return \ILIAS\UI\Component\Prompt\State\Factory
     */
    public function state(): State\Factory;
}
