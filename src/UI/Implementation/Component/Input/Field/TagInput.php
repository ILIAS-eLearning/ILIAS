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
	 * @param array                         $options
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
	public function withAsyncOptionsURL(string $async_option_url): C\Input\Field\TagInput {
		$clone = clone $this;
		$clone->async_option_url = $async_option_url;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function withExtendableOptions(bool $extendable): C\Input\Field\TagInput {
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


	/**
	 * @inheritDoc
	 */
	public function getAsyncOptionsURL(): string {
		return $this->async_option_url;
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
