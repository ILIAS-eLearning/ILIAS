<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Card;

/**
 * Interface Card
 * @package ILIAS\UI\Component\Card
 */
interface Card extends \ILIAS\UI\Component\Component {

	/**
	 * Sets the title in the heading section of the card
	 * @param $title
	 * @return Card
	 */
	public function withTitle($title);

	/**
	 * Gets the title in the heading section of the card
	 * @return string
	 */
	public function getTitle();

	/**
	 * Get a Card like this with a title action
	 * @param string $url
	 * @return Card
	 */
	public function withTitleAction($url);

	/**
	 * Returns the title action if given, otherwise null
	 * @return string|null
	 */
	public function getTitleAction();

	/**
	 * Set multiple sections of the card as array
	 * @param \ILIAS\UI\Component\Component[] $sections
	 * @return Card
	 */
	public function withSections(array $sections);

	/**
	 * Get the multiple sections of the card as array
	 * @return \ILIAS\UI\Component\Component[]
	 */
	public function getSections();

	/**
	 * Set the image of the card
	 * @param \ILIAS\UI\Component\Image\Image $image
	 * @return Card
	 */
	public function withImage(\ILIAS\UI\Component\Image\Image $image);

	/**
	 * Get the image of the card
	 * @return mixed
	 */
	public function getImage();

	/**
	 * Get a Card like this with a highlight
	 * @param bool $status
	 * @return Card
	 */
	public function withHighlight($status);

	/**
	 * Returns whether or not the Card is highlighted
	 * @return bool
	 */
	public function isHighlighted();
}
