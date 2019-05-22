<?php namespace ILIAS\NavigationContext;

use ILIAS\Data\ReferenceId;
use ILIAS\GlobalScreen\Scope\Layout\Definition\LayoutDefinition;
use ILIAS\NavigationContext\AdditionalData\Collection;

/**
 * Class BasicContext
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BasicContext implements ContextInterface {

	/**
	 * @var
	 */
	protected $view;
	/**
	 * @var ReferenceId
	 */
	protected $reference_id;
	/**
	 * @var Collection
	 */
	protected $additional_data;
	/**
	 * @var string
	 */
	protected $context_identifier = '';


	/**
	 * BasicContext constructor.
	 *
	 * @param string           $context_identifier
	 * @param LayoutDefinition $view
	 */
	public function __construct(string $context_identifier, LayoutDefinition $view) {
		static $initialised;
		if ($initialised !== null) {
			throw new \LogicException("only one instance of a view can exist");
		}
		$this->context_identifier = $context_identifier;
		$this->additional_data = new Collection();
		$this->reference_id = new ReferenceId(0);
		$this->view = $view;
	}


	/**
	 * @inheritDoc
	 */
	public function hasReferenceId(): bool {
		return $this->reference_id instanceof ReferenceId && $this->reference_id->toInt() > 0;
	}


	/**
	 * @inheritDoc
	 */
	public function getReferenceId(): ReferenceId {
		return $this->reference_id;
	}


	/**
	 * @inheritDoc
	 */
	public function withReferenceId(ReferenceId $reference_id): ContextInterface {
		$clone = clone $this;
		$clone->reference_id = $reference_id;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function withAdditionalData(Collection $collection): ContextInterface {
		$clone = clone $this;
		$clone->additional_data = $collection;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getAdditionalData(): Collection {
		return $this->additional_data;
	}


	/**
	 * @inheritDoc
	 */
	public function addAdditionalData(string $key, $value): ContextInterface {
		$this->additional_data->add($key, $value);

		return $this;
	}


	/**
	 * @inheritDoc
	 */
	public function getUniqueContextIdentifier(): string {
		return $this->context_identifier;
	}


	/**
	 * @inheritDoc
	 */
	public function getLayoutDefinition(): LayoutDefinition {
		return $this->view;
	}


	/**
	 * @inheritDoc
	 */
	public function replaceLayoutDefinition(LayoutDefinition $view) {
		$this->view = $view;
	}
}
