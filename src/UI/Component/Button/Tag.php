<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

use \ILIAS\UI\Component\Counter\Counter;

/**
 * This describes a tag(-button).
 */
interface Tag extends Button {
	const RELLOW  		= 'btn-tag-relevance-verylow';
	const RELVERYLOW 	= 'btn-tag-relevance-low';
	const RELMID  		= 'btn-tag-relevance-middle';
	const RELHIGH  		= 'btn-tag-relevance-high';
	const RELVERYHIGH  	= 'btn-tag-relevance-veryhigh';

	/**
	 * Set relevance of Tag (to distinguis visually)
	 *
	 * @param	int	 $relevance  a value between 1 and 5
	 * @throws 	\InvalidArgumentException
	 * @return	Tag
	 */
	public function withRelevance($relevance);

	/**
	 * Get the relevance of the Tag.
	 *
	 * @return	int
	 */
	public function getRelevance();

	/**
	 * Get CSS-class according to the relevance of the Tag.
	 *
	 * @return	string
	 */
	public function getRelevanceClass();

	/**
	 * Set a fix background-color.
	 *
	 * @param	Color $col
	 * @return	Tag
	 */
	public function withBackgroundColor($col);

	/**
	 * Get the fix background-color.
	 *
	 * @return	Color|null
	 */
	public function getBackgroundColor();

	/**
	 * Set the fix foreground-color
	 *
	 * @param	Color $col
	 * @return	Tag
	 */
	public function withForegroundColor($col);

	/**
	 * Get the fix foreground-color.
	 *
	 * @return	Color|null
	 */
	public function getForegroundColor();

}
