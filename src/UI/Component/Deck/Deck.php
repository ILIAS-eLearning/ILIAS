<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\UI\Component\Deck;

use ILIAS\UI\Component\Component;

interface Deck extends Component
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
    public function withCards(array $cards): Deck;

    /***
     * Get the cards to be displayed in the deck
     * @return \ILIAS\UI\Component\Card\Card[]
     */
    public function getCards(): array;

    /**
     * Set the cards size to extra small:
     *  3 (xs-4)  6 (sm-2)  6 (md-2) 12 (lg-1)
     */
    public function withExtraSmallCardsSize(): Deck;

    /**
     * Set the cards size to small:
     *  2 (xs-6)  4 (sm-3)  4 (md-3) 6 (lg-2)
     */
    public function withSmallCardsSize(): Deck;

    /**
     * Set the cards size to normal:
     *   1 (xs-12) 2 (sm-6)  3 (md-4) 4 (lg-3)
     */
    public function withNormalCardsSize(): Deck;

    /**
     * Set the cards size to large:
     *  1 (xs-12) 2 (sm-6)  2 (md-6) 3 (lg-4)
     */
    public function withLargeCardsSize(): Deck;

    /**
     * Set the cards size to extra large:
     *   1 (xs-12) 1 (sm-12) 2 (md-6) 2 (lg-6)
     */
    public function withExtraLargeCardsSize(): Deck;

    /**
     * Set the cards size to full:
     *  - 1 Cards on all screen sizes
     */
    public function withFullSizedCardsSize(): Deck;

    /**
     * Get the cards size. Note that this size tells how much space the card is using.
     * The number of cards displayed by normal screen size is 12/size.
     */
    public function getCardsSize(): int;
}
