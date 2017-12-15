<?php
declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\Validation\Factory as ValidationFactory;

/**
 * Class TagInput
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TagInput extends Input implements C\Input\Field\TagInput {

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
	protected $options = [];
	/**
	 * @var array
	 */
	protected $value = [];


	/**
	 * TagInput constructor.
	 *
	 * @param \ILIAS\Data\Factory           $data_factory
	 * @param \ILIAS\Validation\Factory     $validation_factory
	 * @param \ILIAS\Transformation\Factory $transformation_factory
	 * @param string                        $label
	 * @param string                        $byline
	 */
	public function __construct(
		DataFactory $data_factory,
		ValidationFactory $validation_factory,
		\ILIAS\Transformation\Factory $transformation_factory,
		$label,
		$byline,
		array $options
	) {
		parent::__construct($data_factory, $validation_factory, $transformation_factory, $label, $byline);
		$this->options = $options;
		$this->setAdditionalTransformation($this->transformation_factory->custom(function ($raw_value) {
			$json_decode = json_decode($raw_value);
			$values = [];
			foreach ($json_decode as $item) {
				$values[] = trim($item);
			}

			return $values;
		}

		));
		$this->setAdditionalConstraint($this->validation_factory->isArray());
	}


	/**
	 * @return \stdClass
	 */
	public function getConfiguration(): \stdClass {
		$configuration = new \stdClass();
		$configuration->options = $this->getOptions();
		$configuration->selected_options = $this->getValue();
		$configuration->extendable = $this->areTagsExtendable();
		$configuration->suggestion_starts = $this->getSuggestionsStartAfter();
		$configuration->max_chars = 2000;
		$configuration->suggestion_limit = 50;
		$configuration->debug = true;

		return $configuration;
	}


	/**
	 * @inheritDoc
	 */
	protected function getConstraintForRequirement() {
		throw new \LogicException("NYI: What could 'required' mean here?");
	}


	/**
	 * @inheritDoc
	 */
	protected function isClientSideValueOk($value) {
		return $this->validation_factory->isArray()->accepts($value);
	}


	/**
	 * @inheritDoc
	 */
	public function getOptions(): array {
		return $this->options;
	}


	/**
	 * @inheritDoc
	 */
	public function withTagsAreExtendable(bool $extendable): C\Input\Field\TagInput {
		$clone = clone $this;
		$clone->extendable = $extendable;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function areTagsExtendable(): bool {
		return $this->extendable;
	}


	/**
	 * @inheritDoc
	 */
	public function withSuggestionsStartAfter(int $characters): C\Input\Field\TagInput {
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
	public function withTagMaxLength(int $max_length): C\Input\Field\TagInput {
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
	public function withMaxTags(int $max_tags): C\Input\Field\TagInput {
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

	// Events


	/**
	 * @inheritDoc
	 */
	public function withAdditionalOnOptionAdded(Signal $signal): C\Input\Field\TagInput {
		return $this->appendTriggeredSignal($signal, C\Input\Field\TagInput::EVENT_ITEM_ADDED);
	}


	/**
	 * @inheritDoc
	 */
	public function withAdditionalOnBeforeOptionAdded(Signal $signal): C\Input\Field\TagInput {
		return $this->appendTriggeredSignal($signal, C\Input\Field\TagInput::EVENT_BEFORE_ITEM_ADD);
	}


	/**
	 * @inheritDoc
	 */
	public function withAdditionalOnOptionRemoved(Signal $signal): C\Input\Field\TagInput {
		return $this->appendTriggeredSignal($signal, C\Input\Field\TagInput::EVENT_ITEM_REMOVED);
	}


	/**
	 * @inheritDoc
	 */
	public function withAdditionalOnBeforeOptionRemoved(Signal $signal): C\Input\Field\TagInput {
		return $this->appendTriggeredSignal($signal, C\Input\Field\TagInput::EVENT_BEFORE_ITEM_REMOVE);
	}
}
