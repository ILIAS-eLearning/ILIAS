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

use ILIAS\Data\URI;
use ILIAS\UI\Component\Entity\EntityRetrieval;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

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
     *      to a request to the url provided to the Prompt.
     * ---
     * @return \ILIAS\UI\Component\Prompt\Prompt
     */
    public function standard(URI $async_url): Prompt;

    /**
     * ---
     * description:
     *   purpose: >
     *      Confirmation content asks the user to confirm an action on a set of
     *      entities without leaving the current context. It is shown inside a
     *      Prompt via a show-state.
     *   composition: >
     *      A confirmation message box, an entity listing below it and a Standard
     *      Form with Kitchen Sink Hidden Inputs (one per entity id, grouped under
     *      the URLBuilderToken name). IDs are never attached to the form action URL.
     *      The Prompt renders the form without its own submit buttons and places
     *      Confirm/Cancel in the Prompt footer via the form submit signal.
     *   effect: >
     *      The Prompt shows the confirmation question, lists affected entities
     *      and posts the entity ids in the request body when confirmed.
     *      Consumers retrieve them via withRequest() and getData() on this content.
     *   rivals:
     *      Form in Prompt: A Form collects user input. Confirmation only transports already selected entity ids and does not ask for further fields.
     *      Interruptive Modal: A synchronous modal for a single interruptive action, not an async Prompt roundtrip with an entity listing.
     *      Message Box: A confirmation Message Box states the question only; it does not list entities or post their ids.
     *
     * context:
     *   - Shown in a Prompt via state()->show().
     *
     * rules:
     *   usage:
     *     1: >
     *        Confirmation MUST be returned to the Prompt as content of a show-state.
     *     2: >
     *        After POST, consumers MUST call withRequest() and getData() on the
     *        Confirmation, then answer with show, close or redirect.
     *     3: >
     *        Entity ids MUST be posted in the request body, never attached to the
     *        form action URL.
     *
     * ---
     * @param array<string|int> $entity_ids
     * @return \ILIAS\UI\Component\Prompt\Confirmation
     */
    public function confirmation(
        EntityRetrieval $entity_retrieval,
        URLBuilder $post_url,
        URLBuilderToken $post_parameter,
        array $entity_ids,
        string $question,
        string $title,
    ): Confirmation;

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
