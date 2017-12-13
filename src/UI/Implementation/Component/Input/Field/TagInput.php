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

	use JavaScriptBindable;
	use Triggerer;
	/**
	 * @var int
	 */
	protected $suggestion_starts_with = 1;
	/**
	 * @var bool
	 */
	protected $extendable = false;
	/**
	 * @var array
	 */
	protected $options = [];
	/**
	 * @var string
	 */
	protected $async_option_url = '';


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
		$byline
	) {
		parent::__construct($data_factory, $validation_factory, $transformation_factory, $label, $byline);
		$this->setAdditionalConstraint($this->validation_factory->isArray());
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
	public function withOptionsProviderURL(string $async_option_url): C\Input\Field\TagInput {
		$clone = clone $this;
		$clone->async_option_url = $async_option_url;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getOptionsProviderURL(): string {
		return $this->async_option_url;
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
	public function withOptions(array $options): C\Input\Field\TagInput {
		$clone = clone $this;
		$clone->options = $options;

		return $clone;
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
	public function withOptionsAreExtendable(bool $extendable): C\Input\Field\TagInput {
		$clone = clone $this;
		$clone->extendable = $extendable;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function areOptionsExtendable(): bool {
		return $this->extendable;
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
