<?php

/* Copyright (c) 2016 Amstutz Timon <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Deck;

use ILIAS\UI\Component\Deck as D;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class Deck implements D\Deck {
	use ComponentHelper;

	/**
	 * @var \ILIAS\UI\Component\Card\Card[]
	 */
	protected $cards;

	/**
	 * @var int
	 */
	protected $size;
	/**
	 * Deck constructor.
	 * @param $cards
	 * @param $size
	 */
	public function __construct($cards, $size){
		$this->checkArgListElements("sections",$cards,array(\ILIAS\UI\Component\Card\Card::class));
		$this->checkArgIsElement("size", $size, self::$sizes, "size type");

		$this->cards = $cards;
		$this->size = $size;
	}

	/**
	 * @inheritdoc
	 */
	public function withCards($cards){
		checkArgListElements("sections",$cards,array(\ILIAS\UI\Component\Card\Card::class));

		$clone = clone $this;
		$clone->cards = $cards;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getCards(){
		return $this->cards;
	}

	/**
	 * @inheritdoc
	 */
	public function withCardsSize($size){
		$this->checkArgIsElement("size", $size, self::$sizes, "size type");

		$clone = clone $this;
		$clone->size = $size;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getCardsSize(){
		return $this->size;
	}

	private static $sizes = array
	(self::SIZE_FULL
	, self::SIZE_XL
	, self::SIZE_L
	, self::SIZE_M
	, self::SIZE_S
	, self::SIZE_XS
	);
}
?>
