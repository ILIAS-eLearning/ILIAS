<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Rating;

use ILIAS\UI\Component\Component;

/**
 * This describes how a rating-input could be modified during construction of UI.
 */
interface Rating extends Component{

	/**
	 * get topic of rating input
	 *
	 * @return	string
	 */
	public function topic();

	/**
	 * Sets the byline, elaboration on the topic
	 *
	 * @param	string 	$byline
	 * @return	Rating
	 */
	public function withByline($byline);

	/**
	 * get the byline for this rating input
	 *
	 * @return	string
	 */
	public function byline();

	/**
	 * get captions for scale-items
	 *
	 * @return	string[]
	 */
	public function captions();


}
