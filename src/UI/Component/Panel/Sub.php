<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel;

/**
 * This describes a Sub Panel.
 */
interface Sub extends Panel {
	/**
	 * @param mixed $content \ILIAS\UI\Component\Component[] | \ILIAS\UI\Component\Component
	 * @return \ILIAS\UI\Component\Panel\Sub
	 */
	public function withContent($content);

	/**
	 * @return mixed content \ILIAS\UI\Component\Component[] | \ILIAS\UI\Component\Component
	 */
	public function getContent();

	/**
	 * Sets the card to be displayed on the right of the Sub Panel
	 * @param \ILIAS\UI\Component\Card\Card $card
	 * @return Sub
	 */
	public function withCard($card);

	/**
	 * Gets the card to be displayed on the right of the Sub Panel
	 * @return \ILIAS\UI\Component\Card\Card
	 */
	public function getCard();
}
