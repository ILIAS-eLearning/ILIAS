<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Deck;

interface Deck extends \ILIAS\UI\Component\Component {
	/**
	 * Different sizes of the card.
	 */
	const SIZE_XS = 1; //12 Cards per row
	const SIZE_S = 2; //6 Cards per row
	const SIZE_M = 3; //4 Cards per row
	const SIZE_L = 4; //3 Cards per row
	const SIZE_XL = 6; //2 Cards per row
	const SIZE_FULL = 12; //1 Card per row

	/**
	 * Set the cards to be displayed in the deck
	 * @param \ILIAS\UI\Component\Card\Card[] $cards
	 * @return Deck
	 */
	public function withCards($cards);

	/***
	 * Get the cards to be displayed in the deck
	 * @return \ILIAS\UI\Component\Card\Card[]
	 */
	public function getCards();

	/**
	 * Set the cards size
	 * @return Deck
	 */
	public function withCardsSize($size);

	/**
	 * Get the cards size
	 * @return int
	 */
	public function getCardsSize();
}
