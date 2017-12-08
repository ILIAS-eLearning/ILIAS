<?php

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component as C;
use ILIAS\Validation\Factory as ValidationFactory;
use ILIAS\Transformation\Factory as TransformationFactory;

/**
 * Class MultiSelect
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MultiSelect extends Input implements C\Input\Field\MultiSelect {

	/**
	 * @var array
	 */
	protected $options = [];
	/**
	 * @var string
	 */
	protected $async_option_url;


	/**
	 * MultiSelect constructor.
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
		TransformationFactory $transformation_factory,
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
	public function withAsyncOptionsURL($async_option_url): C\Input\Field\MultiSelect {
		$this->checkStringArg("async_option_url", $async_option_url);
		$clone = clone $this;
		$clone->async_option_url = $async_option_url;

		return $clone;
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
}
