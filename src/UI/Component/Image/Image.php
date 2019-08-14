<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Image;

use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Clickable;
use ILIAS\UI\Component\Signal;

/**
 * This describes how a glyph could be modified during construction of UI.
 *
 * Interface Image
 * @package ILIAS\UI\Component\Image
 */
interface Image extends \ILIAS\UI\Component\Component, JavaScriptBindable, Clickable {
	/**
	 * Types of images
	 */
	const STANDARD = "standard";
	const RESPONSIVE = "responsive";

	/**
	 * Set the source (path) of the image. The complete path to the image has to be provided.
	 * @param string $source
	 * @return \ILIAS\UI\Component\Image\Image
	 */
	public function withSource($source);

	/**
	 * Get the source (path) of the image.
	 * @return string
	 */
	public function getSource();

	/**
	 * Get the type of the image
	 * @return string
	 */
	public function getType();

	/**
	 * Set the alternative text for screen readers.
	 * @param string $alt
	 * @return \ILIAS\UI\Component\Image\Image
	 */
	public function withAlt($alt);


	/**
	 * Get the alternative text for screen readers.
	 * @return string
	 */
	public function getAlt();

	/**
	 * Get an image like this with an action
	 * @param string|Signal[] $action
	 * @return \ILIAS\UI\Component\Image\Image
	 */
	public function withAction($action);

	/**
	 * Get the action of the image
	 * @return string|Signal[]
	 */
	public function getAction();
}
