<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Factory;

use ILIAS\UI\Component\Symbol\Glyph\Glyph;

/**
 * Interface hasGlyph
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface hasGlyph extends isItem {

	/**
	 * @param Glyph $glyph
	 *
	 * @return hasGlyph
	 */
	public function withGlyph(Glyph $glyph): hasGlyph;


	/**
	 * @return Glyph
	 */
	public function getGlyph(): Glyph;


	/**
	 * @return bool
	 */
	public function hasGlyph(): bool;
}
