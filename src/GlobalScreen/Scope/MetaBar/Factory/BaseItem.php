<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Glyph\Glyph;

/**
 * Class BaseItem
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BaseItem implements isItem {

	/**
	 * @var Glyph
	 */
	protected $glyph;
	/**
	 * @var string
	 */
	protected $title = "";
	/**
	 * @var Component
	 */
	protected $content;
	/**
	 * @var int
	 */
	protected $position = 0;
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
	 * @var bool
	 */
	protected $is_always_available = false;
	/**
	 * @var
	 */
	protected $type_information;


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
	public function getPosition(): int {
		return $this->position;
	}


	/**
	 * @inheritDoc
	 */
	public function withPosition(int $position): isItem {
		$clone = clone($this);
		$clone->position = $position;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function withContent(Component $content): isItem {
		$clone = clone($this);
		$clone->content = $content;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getContent(): Component {
		return $this->content;
	}


	/**
	 * @inheritDoc
	 */
	public function withGlyph(Glyph $glyph): isItem {
		$clone = clone($this);
		$clone->glyph = $glyph;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getGlyph(): Glyph {
		return $this->glyph;
	}


	/**
	 * @inheritDoc
	 */
	public function withTitle(string $title): isItem {
		$clone = clone($this);
		$clone->title = $title;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getTitle(): string {
		return $this->title;
	}
}
