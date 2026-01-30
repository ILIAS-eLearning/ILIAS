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

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\UI\Component\Input\Container\Filter\FilterInput;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use stdClass;

/**
 * This describes text inputs.
 */
interface Text extends FilterInput
{
    /**
     * Defines the Max Length of text that can be entered in the text input
     */
    public function withMaxLength(int $max_length): Text;

    /**
     * Gets the max length of the text input
     */
    public function getMaxLength(): ?int;

    /**
     * Disable stripping tags from user input
     */
    public function withoutStripTags(): Text;

    /**
     * Get an input like this, but add an endpoint to get a list of possible values.
     * The $autocomplete_endpoint MUST answer to a query with the provided text
     * handed over in the parameter defined in $term_token.
     * It MUST answer with a json array containing the values in the form of objects
     * containing three properties "value", "display", and "searchBy". The property
     * "value" MUST be safe to transmit as url-parameter.
     */
    public function withAsyncAutocomplete(URLBuilder $autocomplete_endpoint, URLBuilderToken $term_token): self;

    public function getAsyncAutocompleteEndpoint(): ?URLBuilder;

    public function getAsyncAutocompleteToken(): ?URLBuilderToken;

    public function withSuggestionsStartAfter(int $characters): self;

    public function getSuggestionsStartAfter(): int;

    public function getConfiguration(): stdClass;
}
