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
		$classes = [\ILIAS\UI\Component\Card\Card::class];
		$this->checkArgListElements("cards",$cards,$classes);
		$this->checkArgIsElement("size", $size, self::$sizes, "size type");

		$this->cards = $cards;
		$this->size = $size;
	}

	/**
	 * @inheritdoc
	 */
	public function withCards($cards){
		$classes = [\ILIAS\UI\Component\Card\Card::class];
		$this->checkArgListElements("sections",$cards,$classes);

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
	public function withExtraSmallCards(){
		return $this->withCardsSize(self::SIZE_XS);
	}

	/**
	 * @inheritdoc
	 */
	public function withSmallCards(){
		return $this->withCardsSize(self::SIZE_S);
	}
	/**
	 * @inheritdoc
	 */
	public function withNormalCards(){
		return $this->withCardsSize(self::SIZE_M);
	}

	/**
	 * @inheritdoc
	 */
	public function withLargeCards(){
		return $this->withCardsSize(self::SIZE_L);
	}

	/**
	 * @inheritdoc
	 */
	public function withExtraLargeCards(){
		return $this->withCardsSize(self::SIZE_XL);
	}

	/**
	 * @inheritdoc
	 */
	public function withFullSizedCards(){
		return $this->withCardsSize(self::SIZE_FULL);
	}

	/***
	 * @param $size
	 * @return Deck
	 */
	protected function withCardsSize($size){
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

	/**
	 * This function is only internal and returns the size of the cards for small displays.
	 * Note that this size tells how much space the card is using. The number of cards displayed by normal screen size is 12/size.
	 *
	 * @return int
	 */
	public function getCardsSizeSmallDisplays(){
		switch($this->getCardsSize()){
			case self::SIZE_XS:
				return 2;
			case self::SIZE_S:
				return 4;
			case self::SIZE_M:
				return 6;
			case self::SIZE_L:
			case self::SIZE_XL:
			case self::SIZE_FULL:
				return 12;
		}
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