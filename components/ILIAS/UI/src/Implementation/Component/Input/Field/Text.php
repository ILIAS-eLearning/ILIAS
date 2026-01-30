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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Constraint;
use Closure;
use Generator;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use InvalidArgumentException;
use stdClass;

/**
 * This implements the text input.
 */
class Text extends FormInput implements C\Input\Field\Text
{
    private ?int $max_length = null;
    private bool $strip_tags_from_input = true;
    protected ?URLBuilder $async_autocomplete_endpoint = null;
    protected ?URLBuilderToken $async_autocomplete_token = null;
    protected int $suggestion_starts_with = 3;

    /**
     * @inheritdoc
     */
    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        string $label,
        ?string $byline
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);
    }

    /**
     * @inheritDoc
     */
    public function withMaxLength(int $max_length): C\Input\Field\Text
    {
        $clone = $this->withAdditionalTransformation(
            $this->refinery->string()->hasMaxLength($max_length)
        );
        $clone->max_length = $max_length;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getMaxLength(): ?int
    {
        return $this->max_length;
    }

    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        if ($this->max_length !== null &&
            strlen($value) > $this->max_length) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement(): ?Constraint
    {
        if ($this->requirement_constraint !== null) {
            return $this->requirement_constraint;
        }

        return $this->refinery->string()->hasMinLength(1);
    }

    /**
     * @inheritdoc
     */
    public function getUpdateOnLoadCode(): Closure
    {
        return fn($id) => "$('#$id').on('input', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());
			});
			il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());";
    }

    /**
     * @inheritdoc
     */
    public function isComplex(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getOperations(): Generator
    {
        if ($this->strip_tags_from_input) {
            yield $this->refinery->custom()->transformation(fn($v) => strip_tags($v));
        }
        yield from parent::getOperations();
    }

    /**
     * @inheritdoc
     */
    public function withoutStripTags(): Text
    {
        $clone = clone $this;
        $clone->strip_tags_from_input = false;
        return $clone;
    }

    public function withAsyncAutocomplete(
        URLBuilder $autocomplete_endpoint,
        URLBuilderToken $term_token
    ): self {
        $clone = clone $this;
        $clone->async_autocomplete_endpoint = $autocomplete_endpoint;
        $clone->async_autocomplete_token = $term_token;
        return $clone;
    }

    public function getAsyncAutocompleteEndpoint(): ?URLBuilder
    {
        return $this->async_autocomplete_endpoint;
    }

    public function getAsyncAutocompleteToken(): ?URLBuilderToken
    {
        return $this->async_autocomplete_token;
    }

    public function withSuggestionsStartAfter(int $characters): self
    {
        if ($characters < 1) {
            throw new InvalidArgumentException('The amount of characters must be at least 1, $characters given.');
        }
        $clone = clone $this;
        $clone->suggestion_starts_with = $characters;

        return $clone;
    }

    public function getSuggestionsStartAfter(): int
    {
        return $this->suggestion_starts_with;
    }

    public function getConfiguration(): stdClass
    {
        $configuration = new stdClass();
        $configuration->suggestionStarts = $this->getSuggestionsStartAfter();
        $configuration->autocompleteTriggerTimeout = 200;

        return $configuration;
    }
}
