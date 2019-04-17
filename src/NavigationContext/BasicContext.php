<?php namespace ILIAS\NavigationContext;

use ILIAS\Data\ReferenceId;
use ILIAS\GlobalScreen\Scope\View\View;
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
	 * @param string $context_identifier
	 * @param View   $view
	 */
	public function __construct(string $context_identifier, View $view) {
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
	public function getUniqueContextIdentifier(): string {
		return $this->context_identifier;
	}


	/**
	 * @inheritDoc
	 */
	public function getView(): View {
		return $this->view;
	}


	/**
	 * @inheritDoc
	 */
	public function replaceView(View $view) {
		$this->view = $view;
	}
}
