<?php namespace ILIAS\GlobalScreen\MainMenu;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\UI\Implementation\Component\Legacy\Legacy;

/**
 * Class AbstractBaseItem
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractBaseItem implements isItem {

	/**
	 * @var Legacy
	 */
	protected $non_available_reason;
	/**
	 * @var
	 */
	protected $available_callable = true;
	/**
	 * @var callable
	 */
	protected $active_callable;
	/**
	 * @var IdentificationInterface
	 */
	protected $provider_identification;
	/**
	 * @var callable
	 */
	protected $visiblility_callable;


	/**
	 * AbstractBaseItem constructor.
	 *
	 * @param IdentificationInterface $provider_identification
	 */
	public function __construct(IdentificationInterface $provider_identification) {
		$this->provider_identification = $provider_identification;
	}


	/**
	 * @inheritDoc
	 */
	public function getProviderIdentification(): IdentificationInterface {
		return $this->provider_identification;
	}


	/**
	 * @inheritDoc
	 */
	public function withVisibilityCallable(callable $is_visible): isItem {
		$clone = clone($this);
		$clone->visiblility_callable = $is_visible;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function isVisible(): bool {
		if (!$this->isAvailable()) {
			return false;
		}
		if (is_callable($this->visiblility_callable)) {
			$callable = $this->visiblility_callable;

			$value = $callable();

			return $value;
		}

		return true;
	}


	/**
	 * @inheritDoc
	 */
	public function withActiveCallable(callable $is_active): isItem {
		$clone = clone($this);
		$clone->active_callable = $is_active;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function isActive(): bool {
		if (is_callable($this->active_callable)) {
			$callable = $this->active_callable;

			$value = $callable();

			return $value;
		}

		return false;
	}


	/**
	 * @inheritDoc
	 */
	public function withAvailableCallable(callable $is_available): isItem {
		$clone = clone($this);
		$clone->available_callable = $is_available;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function isAvailable(): bool {
		if (is_callable($this->available_callable)) {
			$callable = $this->available_callable;

			$value = $callable();

			return $value;
		}

		return true;
	}


	/**
	 * @inheritDoc
	 */
	public function withNonAvailableReason(Legacy $element): isItem {
		$clone = clone $this;
		$clone->non_available_reason = $element;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getNonAvailableReason(): Legacy {
		return $this->non_available_reason;
	}
}
