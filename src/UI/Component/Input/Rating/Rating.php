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
	 * Set captions for scale-items
	 *
	 * @param 	string[] 	$captions 	array with exactly 5 entries
	 * @return	Rating
	 */
	public function withCaptions($captions);

	/**
	 * get captions for scale-items
	 *
	 * @return	string[]
	 */
	public function captions();

	/**
	 * Set average rating
	 *
	 * @param 	integer 	$average 	a number between 0 and 5
	 * @throws 	\InvalidArgumentException	if $average < 0 or $average > 5
	 * @return	Rating
	 */
	public function withAverage($average);

	/**
	 * get the average-value of the input
	 *
	 * @return	integer
	 */
	public function average();


}
