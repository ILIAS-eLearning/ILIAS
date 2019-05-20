<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Factory;

use ILIAS\UI\Component\Glyph\Glyph;

/**
 * Class LinkItem
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LinkItem extends AbstractChildItem implements isItem, hasTitle, hasGlyph, isChild {

	/**
	 * @var Glyph
	 */
	protected $glyph;
	/**
	 * @var string
	 */
	protected $title = "";
	/**
	 * @var string
	 */
	protected $action = "";


	/**
	 * @param string $action
	 *
	 * @return LinkItem
	 */
	public function withAction(string $action): LinkItem {
		$clone = clone($this);
		$clone->action = $action;

		return $clone;
	}


	/**
	 * @return string
	 */
	public function getAction(): string {
		return $this->action;
	}


	/**
	 * @inheritDoc
	 */
	public function withGlyph(Glyph $glyph): hasGlyph {
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
	public function hasGlyph(): bool {
		return ($this->glyph instanceof Glyph);
	}


	/**
	 * @inheritDoc
	 */
	public function withTitle(string $title): hasTitle {
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
