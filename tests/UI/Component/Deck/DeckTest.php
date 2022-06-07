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
 
require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;

/**
 * Test on deck implementation.
 */
class DeckTest extends ILIAS_UI_TestBase
{
    public function getFactory() : NoUIFactory
    {
        return new class extends NoUIFactory {
            public function card() : C\Card\Factory
            {
                return new I\Component\Card\Factory();
            }
            public function deck(array $cards) : C\Deck\Deck
            {
                return new I\Component\Deck\Deck($cards, C\Deck\Deck::SIZE_S);
            }
        };
    }

    public function test_implements_factory_interface() : void
    {
        $f = $this->getFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Factory", $f);
        $c = $f->card()->standard("Card Title");
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Deck\\Deck", $f->deck(array($c)));
    }

    public function test_get_cards() : void
    {
        $f = $this->getFactory();
        $c = $f->card()->standard("Card Title");
        $d = $f->deck(array($c));

        $this->assertEquals($d->getCards(), array($c));
    }

    public function test_with_cards() : void
    {
        $f = $this->getFactory();
        $c = $f->card()->standard("Card Title");
        $d = $f->deck(array($c));

        $d = $d->withCards(array($c,$c));
        $this->assertEquals($d->getCards(), array($c,$c));
    }

    public function test_get_size() : void
    {
        $f = $this->getFactory();

        $c = $f->card()->standard("Card Title");
        $d = $f->deck(array($c));

        $this->assertEquals(C\Deck\Deck::SIZE_S, $d->getCardsSize());
    }

    public function test_with_size() : void
    {
        $f = $this->getFactory();

        $c = $f->card()->standard("Card Title");
        $d = $f->deck(array($c));

        $d = $d->withExtraSmallCardsSize();
        $this->assertEquals(C\Deck\Deck::SIZE_XS, $d->getCardsSize());

        $d = $d->withSmallCardsSize();
        $this->assertEquals(C\Deck\Deck::SIZE_S, $d->getCardsSize());

        $d = $d->withNormalCardsSize();
        $this->assertEquals(C\Deck\Deck::SIZE_M, $d->getCardsSize());

        $d = $d->withLargeCardsSize();
        $this->assertEquals(C\Deck\Deck::SIZE_L, $d->getCardsSize());

        $d = $d->withExtraLargeCardsSize();
        $this->assertEquals(C\Deck\Deck::SIZE_XL, $d->getCardsSize());

        $d = $d->withFullSizedCardsSize();
        $this->assertEquals(C\Deck\Deck::SIZE_FULL, $d->getCardsSize());
    }

    public function test_render_content() : void
    {
        $r = $this->getDefaultRenderer();
        $f = $this->getFactory();
        $c = $f->card()->standard("Card Title");
        $d = $f->deck(array($c));

        $d = $d->withCards(array($c,$c,$c,$c,$c,$c,$c))->withLargeCardsSize();

        $html = $this->brutallyTrimHTML($r->render($d));

        $expected_html =
                '<div class="il-deck"><div class="row row-eq-height">
						<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4"><div class="il-card thumbnail"><div class="card-no-highlight"></div><div class="caption card-title">Card Title</div></div></div>
						<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4"><div class="il-card thumbnail"><div class="card-no-highlight"></div><div class="caption card-title">Card Title</div></div></div>
						<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4"><div class="il-card thumbnail"><div class="card-no-highlight"></div><div class="caption card-title">Card Title</div></div></div>
						<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4"><div class="il-card thumbnail"><div class="card-no-highlight"></div><div class="caption card-title">Card Title</div></div></div>
						<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4"><div class="il-card thumbnail"><div class="card-no-highlight"></div><div class="caption card-title">Card Title</div></div></div>
						<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4"><div class="il-card thumbnail"><div class="card-no-highlight"></div><div class="caption card-title">Card Title</div></div></div>
						<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4"><div class="il-card thumbnail"><div class="card-no-highlight"></div><div class="caption card-title">Card Title</div></div></div>
					</div>
				</div>';

        $this->assertHTMLEquals($this->brutallyTrimHTML($expected_html), $html);
    }
}
