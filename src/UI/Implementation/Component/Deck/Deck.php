<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Implementation\Component\Deck;

use ILIAS\UI\Component\Deck as D;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Card\Card;

class Deck implements D\Deck
{
    use ComponentHelper;

    private static array $sizes = [
        self::SIZE_FULL,
        self::SIZE_XL,
        self::SIZE_L,
        self::SIZE_M,
        self::SIZE_S,
        self::SIZE_XS
    ];

    /**
     * @var Card[]
     */
    protected array $cards;
    protected int $size;

    /**
     * @param \ILIAS\UI\Component\Card\Card[] $cards
     */
    public function __construct(array $cards, int $size)
    {
        $classes = [Card::class];
        $this->checkArgListElements("cards", $cards, $classes);
        $this->checkArgIsElement("size", $size, self::$sizes, "size type");

        $this->cards = $cards;
        $this->size = $size;
    }

    /**
     * @inheritdoc
     */
    public function withCards(array $cards) : D\Deck
    {
        $classes = [Card::class];
        $this->checkArgListElements("sections", $cards, $classes);

        $clone = clone $this;
        $clone->cards = $cards;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getCards() : array
    {
        return $this->cards;
    }

    /**
     * @inheritdoc
     */
    public function withExtraSmallCardsSize() : D\Deck
    {
        return $this->withCardsSize(self::SIZE_XS);
    }

    /**
     * @inheritdoc
     */
    public function withSmallCardsSize() : D\Deck
    {
        return $this->withCardsSize(self::SIZE_S);
    }
    /**
     * @inheritdoc
     */
    public function withNormalCardsSize() : D\Deck
    {
        return $this->withCardsSize(self::SIZE_M);
    }

    /**
     * @inheritdoc
     */
    public function withLargeCardsSize() : D\Deck
    {
        return $this->withCardsSize(self::SIZE_L);
    }

    /**
     * @inheritdoc
     */
    public function withExtraLargeCardsSize() : D\Deck
    {
        return $this->withCardsSize(self::SIZE_XL);
    }

    /**
     * @inheritdoc
     */
    public function withFullSizedCardsSize() : D\Deck
    {
        return $this->withCardsSize(self::SIZE_FULL);
    }

    protected function withCardsSize(int $size) : D\Deck
    {
        $this->checkArgIsElement("size", $size, self::$sizes, "size type");

        $clone = clone $this;
        $clone->size = $size;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getCardsSize() : int
    {
        return $this->size;
    }

    /**
     * @internal This function is only internal and returns the size of the cards for small displays.
     * Note that this size tells how much space the card is using. The number of cards displayed by normal screen size is 12/size.
     */
    public function getCardsSizeSmallDisplays() : int
    {
        return $this->getCardsSizeForDisplaySize(self::SIZE_S);
    }

    /**
     * @internal This function is only internal and returns the size of the cards for small displays.
     * Note that this size tells how much space the card is using. The number of cards displayed by normal screen size is 12/size.
     */
    public function getCardsSizeForDisplaySize(int $display_size) : int
    {
        /**
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
        $sizes = [
            self::SIZE_XS => [
                self::SIZE_XS => 4,
                self::SIZE_S => 2,
                self::SIZE_M => 2,
                self::SIZE_L => 1
            ],
            self::SIZE_S => [
                self::SIZE_XS => 6,
                self::SIZE_S => 3,
                self::SIZE_M => 3,
                self::SIZE_L => 2
            ],
            self::SIZE_M => [
                self::SIZE_XS => 12,
                self::SIZE_S => 6,
                self::SIZE_M => 4,
                self::SIZE_L => 3
            ],
            self::SIZE_L => [
                self::SIZE_XS => 12,
                self::SIZE_S => 6,
                self::SIZE_M => 6,
                self::SIZE_L => 4
            ],
            self::SIZE_XL => [
                self::SIZE_XS => 12,
                self::SIZE_S => 12,
                self::SIZE_M => 6,
                self::SIZE_L => 6
            ],
            self::SIZE_FULL => [
                self::SIZE_XS => 12,
                self::SIZE_S => 12,
                self::SIZE_M => 12,
                self::SIZE_L => 12
            ],
        ];

        return $sizes[$this->getCardsSize()][$display_size];
    }
}
