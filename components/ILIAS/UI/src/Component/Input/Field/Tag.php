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

use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Signal;
use InvalidArgumentException;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

/**
 * Interface Tag
 *
 * This describes Tag Inputs
 *
 * @package ILIAS\UI\Component\Input\Field
 */
interface Tag extends FormInput
{
    /**
     * Get an input like this, but decide whether the user can provide own
     * tags or not. (Default: Allowed)
     */
    public function withUserCreatedTagsAllowed(bool $extendable): self;

    /**
     * Get an input like this, but change the amount of characters the
     * user has to provide before the suggestions start (Default: 1)
     *
     * @param int $characters defaults to 1
     * @throws InvalidArgumentException
     */
    public function withSuggestionsStartAfter(int $characters): self;

    /**
     * Get an input like this, but limit the amount of characters one tag can be. (Default: unlimited)
     */
    public function withTagMaxLength(int $max_length): self;

    /**
     * Get an input like this, but limit the amount of tags a user can select or provide. (Default: unlimited)
     */
    public function withMaxTags(int $max_tags): self;

    /**
     * Get an input like this, but add an endpoint to get a list of possible options.
     * The $autocomplete_endpoint MUST answer to a query with the provided text
     * handed over in the parameter defined in $term_token.
     * It MUST answer with a json array containing the options in the form of objects
     * containing three properties "value", "display", and "searchBy". The property
     * "value" MUST be save to transmit as url-parameter.
     */
    public function withAsyncAutocomplete(URLBuilder $autocomplete_endpoint, URLBuilderToken $term_token): self;

    /**
     * Disable stripping tags from user input.
     */
    public function withoutStripTags(): self;

    // Events

    public function withAdditionalOnTagAdded(Signal $signal): self;

    public function withAdditionalOnTagRemoved(Signal $signal): self;
}
