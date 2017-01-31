<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;


/**
 * Test on deck implementation.
 */
class DeckTest extends ILIAS_UI_TestBase {

	/**
	 * @return \ILIAS\UI\Implementation\Factory
	 */
	public function getFactory() {
		return new \ILIAS\UI\Implementation\Factory();
	}

	public function test_implements_factory_interface() {
		$f = $this->getFactory();

		$this->assertInstanceOf("ILIAS\\UI\\Factory", $f);
		$c = $f->card("Card Title");
		$this->assertInstanceOf( "ILIAS\\UI\\Component\\Deck\\Deck", $f->deck(array($c)));
	}

	public function test_get_cards() {
		$f = $this->getFactory();
		$c = $f->card("Card Title");
		$d = $f->deck(array($c));

		$this->assertEquals($d->getCards(), array($c));
	}

	public function test_with_cards() {
		$f = $this->getFactory();
		$c = $f->card("Card Title");
		$d = $f->deck(array($c));

		$d = $d->withCards(array($c,$c));
		$this->assertEquals($d->getCards(), array($c,$c));
	}

	public function test_get_size() {
		$f = $this->getFactory();

		$c = $f->card("Card Title");
		$d = $f->deck(array($c));

		$this->assertEquals($d->getCardsSize(), C\Deck\Deck::SIZE_S);
	}

	public function test_with_size() {
		$f = $this->getFactory();

		$c = $f->card("Card Title");
		$d = $f->deck(array($c));
		$d = $d->withCardsSize(C\Deck\Deck::SIZE_L);

		$this->assertEquals($d->getCardsSize(), C\Deck\Deck::SIZE_L);
	}

	public function test_render_content() {
		$r = $this->getDefaultRenderer();
		$f = $this->getFactory();
		$c = $f->card("Card Title");
		$d = $f->deck(array($c));

		$d = $d->withCards(array($c,$c,$c,$c,$c,$c,$c));
		$d = $d->withCardsSize(C\Deck\Deck::SIZE_L);

		$html = $r->render($d);

		$expected_html =
				'<div class="il-deck">
					<div class="row">
						<div class="col-md-4"><div class="il-card thumbnail"><div class="caption"><h5 class="card-title">Card Title</h5></div></div></div>
						<div class="col-md-4"><div class="il-card thumbnail"><div class="caption"><h5 class="card-title">Card Title</h5></div></div></div>
						<div class="col-md-4"><div class="il-card thumbnail"><div class="caption"><h5 class="card-title">Card Title</h5></div></div></div>
					</div>
					<div class="row">
						<div class="col-md-4"><div class="il-card thumbnail"><div class="caption"><h5 class="card-title">Card Title</h5></div></div></div>
						<div class="col-md-4"><div class="il-card thumbnail"><div class="caption"><h5 class="card-title">Card Title</h5></div></div></div>
						<div class="col-md-4"><div class="il-card thumbnail"><div class="caption"><h5 class="card-title">Card Title</h5></div></div></div>
					</div>
					<div class="row">
						<div class="col-md-4"><div class="il-card thumbnail"><div class="caption"><h5 class="card-title">Card Title</h5></div></div></div>
					</div>
				</div>';

		$this->assertHTMLEquals($expected_html, $html);
	}
}
