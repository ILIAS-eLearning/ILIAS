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

use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\URLBuilder;
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
     *      Build a Prompt State to confirm an action on a set of entities.
     *   composition: >
     *      The UI framework composes a confirmation message box, an entity
     *      listing below it and a Standard Form with Kitchen Sink Hidden Inputs
     *      (one per entity id, grouped under the URLBuilderToken name).
     *      IDs are never attached to the form action URL.
     *      The Prompt renders the form without its own submit buttons and places
     *      Confirm/Cancel in the Prompt footer via the form submit signal.
     *      Consumers provide an EntityRetrieval and the ids to confirm.
     *   effect: >
     *      The Prompt shows the confirmation question, lists affected entities
     *      and posts the entity ids in the request body when confirmed.
     *      Consumers retrieve them via getConfirmedData() on this state.
     *
     * context:
     *   - The Prompt State is used for Prompts.
     *
     * ---
     * @param Component\Entity\EntityRetrieval $entity_retrieval
     * @param URLBuilder                       $post_url
     * @param URLBuilderToken                  $post_parameter
     * @param array<string|int>                $entity_ids
     * @return \ILIAS\UI\Component\Prompt\State\State
     */
    public function confirm(
        Component\Entity\EntityRetrieval $entity_retrieval,
        URLBuilder $post_url,
        URLBuilderToken $post_parameter,
        array $entity_ids,
        string $question,
        string $title,
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
