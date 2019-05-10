<?php
declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Result\Ok;
use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\Refinery\Validation\Factory as ValidationFactory;

/**
 * Class TagInput
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Tag extends Input implements C\Input\Field\Tag {

	const EVENT_ITEM_ADDED = 'itemAdded';
	const EVENT_BEFORE_ITEM_REMOVE = 'beforeItemRemove';
	const EVENT_BEFORE_ITEM_ADD = 'beforeItemAdd';
	const EVENT_ITEM_REMOVED = 'itemRemoved';
	const INFINITE = 0;
	use JavaScriptBindable;
	use Triggerer;
	/**
	 * @var int
	 */
	protected $max_tags = self::INFINITE;
	/**
	 * @var int
	 */
	protected $tag_max_length = self::INFINITE;
	/**
	 * @var bool
	 */
	protected $extendable = true;
	/**
	 * @var int
	 */
	protected $suggestion_starts_with = 1;
	/**
	 * @var array
	 */
	protected $tags = [];
	/**
	 * @var array
	 */
	protected $value = [];


	/**
	 * TagInput constructor.
	 *
	 * @param \ILIAS\Data\Factory           $data_factory
	 * @param \ILIAS\Refinery\Validation\Factory     $validation_factory
	 * @param \ILIAS\Refinery\Factory $refinery
	 * @param string                        $label
	 * @param string                        $byline
	 * @param array                         $tags
	 */
	public function __construct(
		DataFactory $data_factory,
		ValidationFactory $validation_factory,
		\ILIAS\Refinery\Factory $refinery,
		$label,
		$byline,
		array $tags
	) {
		parent::__construct($data_factory, $validation_factory, $refinery, $label, $byline);
		$this->tags = $tags;
	}


	/**
	 * @return \stdClass
	 */
	public function getConfiguration(): \stdClass {
		$configuration = new \stdClass();
		$configuration->id = null;
		$configuration->options = $this->getTags();
		$configuration->selectedOptions = $this->getValue();
		$configuration->extendable = $this->areUserCreatedTagsAllowed();
		$configuration->suggestionStarts = $this->getSuggestionsStartAfter();
		$configuration->maxChars = 2000;
		$configuration->suggestionLimit = 50;
		$configuration->debug = false;
		$configuration->allowDuplicates = false;
		$configuration->highlight = true;
		$configuration->tagClass = "label label-primary il-input-tag-tag";
		$configuration->focusClass = 'il-input-tag-focus';

		return $configuration;
	}


	/**
	 * @inheritDoc
	 */
	protected function getConstraintForRequirement() {
		$constraint = $this->validation_factory->custom(
			function ($value) {
				return (is_array($value) && count($value) > 0);
			}, "Empty array"
		);

		$this->refinery
			->to()
			->listOf($this->refinery->to()->string());

		$listTransformation = $this->refinery
			->to()
			->listOf($this->refinery->to()->string());

		return $this->refinery->logical()->sequential(
			[$constraint, $listTransformation]
		);
	}


	/**
	 * @inheritDoc
	 */
	protected function isClientSideValueOk($value) {
		if ($this->getMaxTags() > 0) {
			$max_tags = $this->getMaxTags();
			$max_tags_ok = $this->validation_factory->custom(
				function ($value) use ($max_tags) {
					return (is_array($value) && count($value) <= $max_tags);
				}, 'Too many Tags'
			);
			if (!$max_tags_ok->accepts($value)) {
				return false;
			}
		}

		if ($this->getTagMaxLength() > 0) {
			$tag_max_length = $this->getTagMaxLength();
			$tag_max_length_ok = $this->validation_factory->custom(
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
				}, 'Too long Tags'
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

		return ($this->validation_factory->isNull()->accepts($value) || $valueCanBeAddedAsStringToList);
	}


	/**
	 * @inheritDoc
	 */
	public function getTags(): array {
		return $this->tags;
	}


	/**
	 * @inheritDoc
	 */
	public function withUserCreatedTagsAllowed(bool $extendable): C\Input\Field\Tag {
		$clone = clone $this;
		$clone->extendable = $extendable;
		/**
		 * @var $with_constraint C\Input\Field\Tag
		 */
		$with_constraint = $clone->withAdditionalConstraint(
			$this->validation_factory->custom(
				function ($value) use ($clone) {
					return (0 == count(array_diff($value, $clone->getTags())));
				}, function ($txt, $value) use ($clone) {
				return "user created tags are not allowed: " . implode(", ", array_diff($value, $clone->getTags()));
			}
			)
		);

		return $with_constraint;
	}


	/**
	 * @inheritDoc
	 */
	public function areUserCreatedTagsAllowed(): bool {
		return $this->extendable;
	}


	/**
	 * @inheritDoc
	 */
	public function withSuggestionsStartAfter(int $characters): C\Input\Field\Tag {
		if ($characters < 1) {
			throw new \InvalidArgumentException("The amount of characters must be at least 1, {$characters} given.");
		}
		$clone = clone $this;
		$clone->suggestion_starts_with = $characters;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getSuggestionsStartAfter(): int {
		return $this->suggestion_starts_with;
	}


	/**
	 * @inheritDoc
	 */
	public function withTagMaxLength(int $max_length): C\Input\Field\Tag {
		$clone = clone $this;
		$clone->tag_max_length = $max_length;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getTagMaxLength(): int {
		return $this->tag_max_length;
	}


	/**
	 * @inheritDoc
	 */
	public function withMaxTags(int $max_tags): C\Input\Field\Tag {
		$clone = clone $this;
		$clone->max_tags = $max_tags;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getMaxTags(): int {
		return $this->max_tags;
	}


	/**
	 * @inheritDoc
	 */
	public function withInput(InputData $input) {
		return parent::withInput($input);
	}



	// Events


	/**
	 * @inheritDoc
	 */
	public function withAdditionalOnTagAdded(Signal $signal): C\Input\Field\Tag {
		return $this->appendTriggeredSignal($signal, self::EVENT_ITEM_ADDED);
	}


	/**
	 * @inheritDoc
	 */
	public function withAdditionalOnTagRemoved(Signal $signal): C\Input\Field\Tag {
		return $this->appendTriggeredSignal($signal, self::EVENT_ITEM_REMOVED);
	}
}
