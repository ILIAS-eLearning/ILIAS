<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Result\Ok;
use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\Input\InputData;
use stdClass;
use ILIAS\Refinery\Constraint;
use InvalidArgumentException;
use Closure;
use LogicException;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * Class TagInput
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Tag extends Input implements FormInputInternal, C\Input\Field\Tag
{
    use JavaScriptBindable;
    use Triggerer;

    public const EVENT_ITEM_ADDED = 'itemAdded';
    public const EVENT_BEFORE_ITEM_REMOVE = 'beforeItemRemove';
    public const EVENT_BEFORE_ITEM_ADD = 'beforeItemAdd';
    public const EVENT_ITEM_REMOVED = 'itemRemoved';
    public const INFINITE = 0;

    protected int $max_tags = self::INFINITE;
    protected int $tag_max_length = self::INFINITE;
    protected bool $extendable = true;
    protected int $suggestion_starts_with = 1;
    protected array $tags = [];

    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        string $label,
        ?string $byline,
        array $tags
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);
        $this->tags = $tags;

        $this->addAdditionalTransformations();
    }

    protected function addAdditionalTransformations(): void
    {
        $this->setAdditionalTransformation($this->refinery->string()->splitString(','));
        $this->setAdditionalTransformation($this->refinery->custom()->transformation(function (array $v) {
            if (count($v) == 1 && $v[0] === '') {
                return [];
            }
            return array_map("urldecode", $v);
        }));
    }

    public function getConfiguration(): stdClass
    {
        $options = array_map(
            fn ($tag) => [
                'value' => urlencode(trim($tag)),
                'display' => $tag,
                'searchBy' => $tag
            ],
            $this->getTags()
        );

        $configuration = new stdClass();
        $configuration->id = null;
        $configuration->options = $options;
        $configuration->selectedOptions = $this->getValue();
        $configuration->maxItems = 20;
        $configuration->dropdownMaxItems = 200;
        $configuration->dropdownCloseOnSelect = false;
        $configuration->readonly = $this->isDisabled();
        $configuration->userInput = $this->areUserCreatedTagsAllowed();
        $configuration->dropdownSuggestionsStartAfter = $this->getSuggestionsStartAfter();
        $configuration->suggestionStarts = $this->getSuggestionsStartAfter();
        $configuration->maxChars = 2000;
        $configuration->suggestionLimit = 50;
        $configuration->debug = false;
        $configuration->allowDuplicates = false;
        $configuration->highlight = true;
        $configuration->tagClass = "input-tag";
        $configuration->tagTextProp = "displayValue";

        return $configuration;
    }

    /**
     * @inheritDoc
     */
    protected function getConstraintForRequirement(): ?Constraint
    {
        return $this->refinery->logical()->sequential([
            $this->refinery->logical()->not($this->refinery->null()),
            $this->refinery->string()->hasMinLength(1)
        ])->withProblemBuilder(function ($txt) {
            return $txt('ui_tag_required');
        });
    }

    /**
     * @inheritDoc
     */
    protected function isClientSideValueOk($value): bool
    {
        if ($this->getMaxTags() > 0) {
            $max_tags = $this->getMaxTags();
            $max_tags_ok = $this->refinery->custom()->constraint(
                fn ($value) => is_array($value) && count($value) <= $max_tags,
                'Too many Tags'
            );
            if (!$max_tags_ok->accepts($value)) {
                return false;
            }
        }

        if ($this->getTagMaxLength() > 0) {
            $tag_max_length = $this->getTagMaxLength();
            $tag_max_length_ok = $this->refinery->custom()->constraint(
                function ($value) use ($tag_max_length) {
                    if (!is_array($value)) {
                        return false;
                    }
                    foreach ($value as $item) {
                        if (strlen($item) > $tag_max_length) {
                            return false;
                        }
                    }

                    return true;
                },
                'Too long Tags'
            );
            if (!$tag_max_length_ok->accepts($value)) {
                return false;
            }
        }

        $valueCanBeAddedAsStringToList = $this->refinery
            ->to()
            ->listOf($this->refinery->to()->string())
            ->applyTo(new Ok($value))
            ->isOK();

        return ($this->refinery->null()->accepts($value) || $valueCanBeAddedAsStringToList);
    }


    /**
     * @inheritDoc
     */
    public function getTags(): array
    {
        return $this->tags;
    }


    /**
     * @inheritDoc
     */
    public function withUserCreatedTagsAllowed(bool $extendable): C\Input\Field\Tag
    {
        $clone = clone $this;
        $clone->extendable = $extendable;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function areUserCreatedTagsAllowed(): bool
    {
        return $this->extendable;
    }

    /**
     * @inheritDoc
     */
    public function withSuggestionsStartAfter(int $characters): C\Input\Field\Tag
    {
        if ($characters < 1) {
            throw new InvalidArgumentException("The amount of characters must be at least 1, $characters given.");
        }
        $clone = clone $this;
        $clone->suggestion_starts_with = $characters;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getSuggestionsStartAfter(): int
    {
        return $this->suggestion_starts_with;
    }

    /**
     * @inheritDoc
     */
    public function withTagMaxLength(int $max_length): C\Input\Field\Tag
    {
        $clone = clone $this;
        $clone->tag_max_length = $max_length;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getTagMaxLength(): int
    {
        return $this->tag_max_length;
    }

    /**
     * @inheritDoc
     */
    public function withMaxTags(int $max_tags): C\Input\Field\Tag
    {
        $clone = clone $this;
        $clone->max_tags = $max_tags;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getMaxTags(): int
    {
        return $this->max_tags;
    }

    /**
     * @inheritDoc
     */
    public function withInput(InputData $input): C\Input\Field\Input
    {
        // ATTENTION: This is a slightly modified copy of parent::withInput, which
        // fixes #27909 but makes the Tag Input unusable in Filter Containers.
        if ($this->getName() === null) {
            throw new LogicException("Can only collect if input has a name.");
        }

        $clone = clone $this;
        //TODO: Discuss, is this correct here. If there is no input contained in this post
        //We assign null. Note that unset checkboxes are not contained in POST.
        if (!$this->isDisabled()) {
            $value = $input->getOr($this->getName(), null);
            $clone->content = $this->applyOperationsTo($value);
        }

        if ($clone->content->isError()) {
            return $clone->withError("" . $clone->content->error());
        }

        return $clone->withValue($clone->content->value());
    }

    // Events
    public function withAdditionalOnTagAdded(Signal $signal): C\Input\Field\Tag
    {
        return $this->appendTriggeredSignal($signal, self::EVENT_ITEM_ADDED);
    }

    public function withAdditionalOnTagRemoved(Signal $signal): C\Input\Field\Tag
    {
        return $this->appendTriggeredSignal($signal, self::EVENT_ITEM_REMOVED);
    }

    /**
     * @inheritdoc
     */
    public function getUpdateOnLoadCode(): Closure
    {
        return fn ($id) => "$('#$id').on('add', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());
			});
			$('#$id').on('remove', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());
			});
			il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());";
    }
}
