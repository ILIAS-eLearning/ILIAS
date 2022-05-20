<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;

/**
 * Test on deck implementation.
 */
class DeckTest extends ILIAS_UI_TestBase
{
    /**
     * @return \ILIAS\UI\Implementation\Factory
     */
    public function getFactory()
    {
        $factory = new class extends NoUIFactory {
            public function card()
            {
                return new I\Component\Card\Factory();
            }
            public function deck(array $cards)
            {
                return new I\Component\Deck\Deck($cards, I\Component\Deck\Deck::SIZE_S);
            }
        };
        return $factory;
    }

    public function test_implements_factory_interface()
    {
        $f = $this->getFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Factory", $f);
        $c = $f->card()->standard("Card Title");
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Deck\\Deck", $f->deck(array($c)));
    }

    public function test_get_cards()
    {
        $f = $this->getFactory();
        $c = $f->card()->standard("Card Title");
        $d = $f->deck(array($c));

        $this->assertEquals($d->getCards(), array($c));
    }

    public function test_with_cards()
    {
        $f = $this->getFactory();
        $c = $f->card()->standard("Card Title");
        $d = $f->deck(array($c));

        $d = $d->withCards(array($c,$c));
        $this->assertEquals($d->getCards(), array($c,$c));
    }

    public function test_get_size()
    {
        $f = $this->getFactory();

        $c = $f->card()->standard("Card Title");
        $d = $f->deck(array($c));

        $this->assertEquals($d->getCardsSize(), C\Deck\Deck::SIZE_S);
    }

    public function test_with_size()
    {
        $f = $this->getFactory();

        $c = $f->card()->standard("Card Title");
        $d = $f->deck(array($c));

        $d = $d->withExtraSmallCardsSize();
        $this->assertEquals($d->getCardsSize(), C\Deck\Deck::SIZE_XS);

        $d = $d->withSmallCardsSize();
        $this->assertEquals($d->getCardsSize(), C\Deck\Deck::SIZE_S);

        $d = $d->withNormalCardsSize();
        $this->assertEquals($d->getCardsSize(), C\Deck\Deck::SIZE_M);

        $d = $d->withLargeCardsSize();
        $this->assertEquals($d->getCardsSize(), C\Deck\Deck::SIZE_L);

        $d = $d->withExtraLargeCardsSize();
        $this->assertEquals($d->getCardsSize(), C\Deck\Deck::SIZE_XL);

        $d = $d->withFullSizedCardsSize();
        $this->assertEquals($d->getCardsSize(), C\Deck\Deck::SIZE_FULL);
    }

    public function test_render_content()
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
