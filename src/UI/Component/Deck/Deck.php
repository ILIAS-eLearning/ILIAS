<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Deck;

interface Deck extends \ILIAS\UI\Component\Component
{
    /**
     * Different sizes of the card. Those values will be returned by getCardsSize.
     */
    const SIZE_XS = 1; //12 Cards per row on normal screen, 6 cards on small screens, 1 card on very small screens.
    const SIZE_S = 2; //6 Cards per row, 3 cards on small screens, 1 card on very small screens
    const SIZE_M = 3; //4 Cards per row,
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
     * Set the cards size to extra small:
     *  - 12 Cards on normal screens
     *  - 6 Cards on small screens
     *  - 1 Card on very small screens
     *
     * @param int Size of the card
     * @return Deck
     */
    public function withExtraSmallCardsSize();

    /**
     * Set the cards size to small:
     *  - 6 Cards on normal screens
     *  - 3 Cards on small screens
     *  - 1 Card on very small screens
     *
     * @param int Size of the card
     * @return Deck
     */
    public function withSmallCardsSize();

    /**
     * Set the cards size to normal:
     *  - 4 Cards on normal screens
     *  - 2 Cards on small screens
     *  - 1 Card on very small screens
     *
     * @param int Size of the card
     * @return Deck
     */
    public function withNormalCardsSize();

    /**
     * Set the cards size to large:
     *  - 3 Cards on normal screens
     *  - 1 Cards on small screens
     *  - 1 Card on very small screens
     *
     * @param int Size of the card
     * @return Deck
     */
    public function withLargeCardsSize();

    /**
     * Set the cards size to extra large:
     *  - 2 Cards on normal screens
     *  - 1 Cards on small screens
     *  - 1 Card on very small screens
     *
     * @param int Size of the card
     * @return Deck
     */
    public function withExtraLargeCardsSize();

    /**
     * Set the cards size to full:
     *  - 1 Cards on normal screens
     *  - 1 Cards on small screens
     *  - 1 Card on very small screens
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
