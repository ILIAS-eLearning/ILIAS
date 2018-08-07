<?php namespace ILIAS\UX\MainMenu;

use ILIAS\UX\Identification\IdentificationInterface;

/**
 * Class AbstractBaseEntry
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractBaseEntry implements EntryInterface {

	/**
	 * @var
	 */
	protected $available = true;
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
	 * AbstractBaseEntry constructor.
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
	public function withVisibilityCallable(callable $is_visible): EntryInterface {
		$clone = clone($this);
		$clone->visiblility_callable = $is_visible;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function isVisible(): bool {
		if (!$this->isActive()) {
			return false;
		}
		if (is_callable($this->visiblility_callable)) {
			$callable = $this->visiblility_callable;

			return $callable();
		}

		return true;
	}


	/**
	 * @inheritDoc
	 */
	public function withActiveCallable(callable $is_active): EntryInterface {
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

			return $callable();
		}

		return true;
	}


	/**
	 * @inheritDoc
	 */
	public function withAvailable(bool $available): EntryInterface {
		$clone = clone($this);
		$clone->available = $available;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function isAvailable(): bool {
		return $this->available;
	}
}
