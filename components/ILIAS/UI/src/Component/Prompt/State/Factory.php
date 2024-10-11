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

namespace ILIAS\UI\Component\Prompt\State;

use ILIAS\UI\Component;
use ILIAS\Data\URI;

interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *      Build a Prompt State to show the Contents given here.
     *   composition: >
     *      The Prompt State accepts Prompt Content to be handled by
     *      the Prompt.
     *   effect: >
     *       The sections of the Prompt State are rendered to their respective
     *       parts of the Prompt.
     *       Forms and Links are automatically turned into async requests to
     *       stay in context of the Prompt.
     *       You may also tell the Prompt to close or redirect - after the
     *       request has been processed.
     *
     * context:
     *   - The Prompt State is used for Prompts.
     *
     * ---
     * @return \ILIAS\UI\Component\Prompt\State\State
     */
    public function show(
        \ILIAS\UI\Component\Prompt\IsPromptContent $content
    ): State;

    /**
     * ---
     * description:
     *   purpose: >
     *      Factors a Prompt State without contents, but with a 'close'-command
     *      for the Prompt.
     *   composition: >
     *      The Close State does not have any relevant manifestation.
     *   effect: >
     *      Tells the Prompt to close: when the Close State is retrieved
     *      from the server, the Prompt is closed.
     *
     * context:
     *   - The Close State is used for Prompts.
     *
     * ---
     * @return \ILIAS\UI\Component\Prompt\State\State
     */
    public function close(): State;

    /**
     * ---
     * description:
     *   purpose: >
     *      Factors a Prompt State without contents, but with a 'redirect'-command
     *      for the Prompt.
     *   composition: >
     *      The Redirect State does not have any relevant manifestation.
     *   effect: >
     *      Tells the Prompt to redirect the page. When the State is called
     *      asynchronously, the server-side redirect will do so for the
     *      async call only.
     *      Use Redirect to redirect the client to the given URL.
     *
     * context:
     *   - The Redirect State is used for Prompts.
     *
     * ---
     * @return \ILIAS\UI\Component\Prompt\State\State
     */
    public function redirect(URI $redirect): State;

}
