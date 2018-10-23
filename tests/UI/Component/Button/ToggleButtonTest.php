<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Component\Signal;

/**
 * Test Toggle Button
 */
class ToggleButtonTest extends ILIAS_UI_TestBase {
	public function getFactory() {
		return new \ILIAS\UI\Implementation\Factory();
	}

	public function test_implements_factory_interface() {
		$f = $this->getFactory();

		$this->assertInstanceOf
		( "ILIAS\\UI\\Component\\Button\\Toggle"
			, $f->button()->toggle("label", "action_on_string", "action_off_string")
		);
	}

	public function test_construction_label_type_wrong() {
		$f = $this->getFactory();
		try {
			$f->button()->toggle(1, "action_on_string", "action_off_string");
			$this->assertFalse("This should not happen");
		}
		catch (\InvalidArgumentException $e) {
			$this->assertTrue(true);
		}
	}

	public function test_construction_action_on_type_wrong() {
		$f = $this->getFactory();
		try {
			$f->button()->toggle("label", 1, "action_off_string");
			$this->assertFalse("This should not happen");
		}
		catch (\InvalidArgumentException $e) {
			$this->assertTrue(true);
		}
	}

	public function test_construction_action_off_type_wrong() {
		$f = $this->getFactory();
		try {
			$f->button()->toggle("label", "action_on_string", 2);
			$this->assertFalse("This should not happen");
		}
		catch (\InvalidArgumentException $e) {
			$this->assertTrue(true);
		}
	}

	public function test_construction_activation_type_wrong() {
		$f = $this->getFactory();
		try {
			$f->button()->toggle("label", "action_on_string", "action_off_string", "true");
			$this->assertFalse("This should not happen");
		}
		catch (\InvalidArgumentException $e) {
			$this->assertTrue(true);
		}
	}

	public function test_setOn_on_default() {
		$f = $this->getFactory();
		$button = $f->button()->toggle("label", "action_on_string", "action_off_string", true);

		$this->assertTrue($button->isActive());
	}

	public function test_append_OnAction() {
		$f = $this->getFactory();
		$signal_on1 = $this->createMock(Signal::class);
		$signal_on2 = $this->createMock(Signal::class);
		$signal_off = $this->createMock(Signal::class);
		$button = $f->button()->toggle("label", $signal_on1, $signal_off);
		$this->assertEquals([$signal_on1], $button->getActionOn());

		$button = $button->withAdditionalToggleOnSignal($signal_on2);
		$this->assertEquals([$signal_on1, $signal_on2], $button->getActionOn());
	}

	public function test_append_OffAction() {
		$f = $this->getFactory();
		$signal_off1 = $this->createMock(Signal::class);
		$signal_off2 = $this->createMock(Signal::class);
		$signal_on = $this->createMock(Signal::class);
		$button = $f->button()->toggle("label", $signal_off1, $signal_on);
		$this->assertEquals([$signal_off1], $button->getActionOff());

		$button = $button->withAdditionalToggleOffSignal($signal_off2);
		$this->assertEquals([$signal_off1, $signal_off2], $button->getActionOff());
	}

	public function test_render_with_label() {
		$r = $this->getDefaultRenderer();
		$button = $this->getFactory()->button()->toggle("label", "action_on_string", "action_off_string");

		$expected = ''
			.'<label>label</label>'
			.'<button class="il-toggle-button" id="id_1" aria-pressed="false">'
			.'    <div class="il-toggle-switch"></div>'
			.'</button>';

		$this->assertHTMLEquals($expected, $r->render($button));   //error in this test, "label" seems to be the reason
	}

	public function test_render_setOn_on_default() {
		$r = $this->getDefaultRenderer();
		$button = $this->getFactory()->button()->toggle("", "action_on_string", "action_off_string", true);

		$expected = ''
			.'<button class="il-toggle-button on" id="id_1" aria-pressed="false">'    //aria-pressed is set to "true" by JS
			.'    <div class="il-toggle-switch"></div>'
			.'</button>';

		$this->assertHTMLEquals($expected, $r->render($button));
	}

	public function test_render_with_signals() {
		$r = $this->getDefaultRenderer();
		$signal_on = $this->createMock(Signal::class);
		$signal_on->method("__toString")
			->willReturn("MOCK_SIGNAL");
		$signal_off = $this->createMock(Signal::class);
		$signal_off->method("__toString")
			->willReturn("MOCK_SIGNAL");
		$button = $this->getFactory()->button()->toggle("label", $signal_on, $signal_off);

		$expected = ''   //same html as in the first render test. Do we need this test?
			.'<label>label</label>'
			.'<button class="il-toggle-button" id="id_1" aria-pressed="false">'
			.'    <div class="il-toggle-switch"></div>'
			.'</button>';

		$this->assertHTMLEquals($expected, $r->render($button));
	}
}
