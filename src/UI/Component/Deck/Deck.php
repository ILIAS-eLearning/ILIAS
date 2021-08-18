<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Deck;

interface Deck extends \ILIAS\UI\Component\Component
{
    /**
     * Different sizes of the card. Those values will be returned by getCardsSize.
     *
     * Rationale
     *
     *  (1) Breakpoints:        768 - 992 - 1200
     *  (2) Center Points:      384 (xs) - 880 (sm) - 1096 (md) - 1400 (lg)
     *  (3) Normalized Ratio:	27% (xs) - 63% (sm) - 78% (md) - 100% (lg)
     *  (4) Card sizes respecting ratio:
     *      Extra Small:   3 (xs-4)  6 (sm-2)  6 (md-2) 12 (lg-1)
     *      Small: 	       2 (xs-6)  4 (sm-3)  4 (md-3) 6 (lg-2)
     *      Normal:        1 (xs-12) 2 (sm-6)  3 (md-4) 4 (lg-3)
     *      Large:         1 (xs-12) 2 (sm-6)  2 (md-6) 3 (lg-4)
     *      Extra Large:   1 (xs-12) 1 (sm-12) 2 (md-6) 2 (lg-6)
     */
    public const SIZE_XS = 1;
    public const SIZE_S = 2;
    public const SIZE_M = 3;
    public const SIZE_L = 4;
    public const SIZE_XL = 6;
    public const SIZE_FULL = 12;

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
     * Set the cards size to extra small:
     *  3 (xs-4)  6 (sm-2)  6 (md-2) 12 (lg-1)
     *
     * @param int Size of the card
     * @return Deck
     */
    public function withExtraSmallCardsSize();

    /**
     * Set the cards size to small:
     *  2 (xs-6)  4 (sm-3)  4 (md-3) 6 (lg-2)
     *
     * @param int Size of the card
     * @return Deck
     */
    public function withSmallCardsSize();

    /**
     * Set the cards size to normal:
     *   1 (xs-12) 2 (sm-6)  3 (md-4) 4 (lg-3)
     *
     * @param int Size of the card
     * @return Deck
     */
    public function withNormalCardsSize();

    /**
     * Set the cards size to large:
     *  1 (xs-12) 2 (sm-6)  2 (md-6) 3 (lg-4)
     *
     * @param int Size of the card
     * @return Deck
     */
    public function withLargeCardsSize();

    /**
     * Set the cards size to extra large:
     *   1 (xs-12) 1 (sm-12) 2 (md-6) 2 (lg-6)
     *
     * @param int Size of the card
     * @return Deck
     */
    public function withExtraLargeCardsSize();

    /**
     * Set the cards size to full:
     *  - 1 Cards on all screen sizes
     *
     * @param int Size of the card
     * @return Deck
     */
    public function withFullSizedCardsSize();

    /**
     * Get the cards size. Note that this size tells how much space the card is using.
     * The number of cards displayed by normal screen size is 12/size.
     *
     * @return int
     */
    public function getCardsSize();
}
