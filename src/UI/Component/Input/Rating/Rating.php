<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Rating;

use ILIAS\UI\Component\Component;

/**
 * This describes how a rating-input could be modified during construction of UI.
 */

interface Rating extends Component{

	/**
	 * Sets the topic that should be rated
	 *
	 * @param	string 	$topic
	 * @return	Rating
	 */
	public function withTopic($topic);

	/**
	 * Sets the byline, elaboration on the topic
	 *
	 * @param	string 	$byline
	 * @return	Rating
	 */
	public function withByline($byline);

	/**
	 * Sets captions for scale positions.
	 *
	 * @param	string[] 	$scale_captions 	default is an array of 5 empty strings
	 * @return	Rating
	 */
	public function withCaptions(array $scale_captions);

	/**
	 * Do not display topic
	 *
	 * @param	Boolean 	$hidden
	 * @return	Rating
	 */
	public function withHiddenTopic($hidden=true);


}
