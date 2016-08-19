<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");

require_once(__DIR__."/Renderer/ilIndependentTemplate.php");
require_once(__DIR__."/../../Services/Language/classes/class.ilLanguage.php");

use ILIAS\UI\Implementation\Render\TemplateFactory;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Implementation\Render\JavaScriptBinding;
use ILIAS\UI\Implementation\DefaultRenderer;
use ILIAS\UI\Factory;

class ilIndependentTemplateFactory implements TemplateFactory {
	public function getTemplate($path, $purge_unfilled_vars, $purge_unused_blocks) {
		return new ilIndependentTemplate($path, $purge_unfilled_vars, $purge_unused_blocks);
	}
}

class NoUIFactory implements Factory {
	public function counter() {}
	public function glyph() {}
	public function button() {}
	public function card($title, \ILIAS\UI\Component\Image\Image $image = null) {}
	public function deck(array $cards) {}
	public function listing() {}
	public function image() {}
	public function legacy($content) {}
	public function panel() {}
}

class LoggingRegistry implements ResourceRegistry {
	public $resources = array();

	public function register($name) {
		$this->resources[] = $name;
	}
}

class ilLanguageMock extends \ilLanguage {
	public $requested = array();
	public function __construct() {}
	public function txt($a_topic, $a_default_lang_fallback_mod = "") {
		$this->requested[] = $a_topic;
		return $a_topic;
	}
}

class LoggingJavaScriptBinding implements JavaScriptBinding {
	private $count = 0;
	public $ids = array();
	public function createId() {
		$this->count++;
		$id = "id_".$this->count;
		$this->ids[] = $id;
		return $id;
	}
	public $on_load_code = array();
	public function addOnLoadCode($code) {
		$this->on_load_code[] = $code;
	}
}

/**
 * Provides common functionality for UI tests.
 */
abstract class ILIAS_UI_TestBase extends PHPUnit_Framework_TestCase {
	public function setUp() {
		assert_options(ASSERT_WARNING, 0);
		assert_options(ASSERT_CALLBACK, null);
	}

	public function tearDown() {
		assert_options(ASSERT_WARNING, 1);
		assert_options(ASSERT_CALLBACK, null);
	}

	public function getUIFactory() {
		return new NoUIFactory();
	}

	public function getTemplateFactory() {
		return new ilIndependentTemplateFactory();
	}

	public function getResourceRegistry() {
		return new LoggingRegistry();
	}

	public function getLanguage() {
		return new ilLanguageMock();
	}

	public function getJavaScriptBinding() {
		return new LoggingJavaScriptBinding();
	}

	public function getDefaultRenderer() {
		$ui_factory = $this->getUIFactory();
		$tpl_factory = $this->getTemplateFactory();
		$resource_registry = $this->getResourceRegistry();
		$lng = $this->getLanguage();
		$js_binding = $this->getJavaScriptBinding();
		return new DefaultRenderer(
				$ui_factory, $tpl_factory, $resource_registry, $lng, $js_binding);
	}

	public function normalizeHTML($html) {
		return trim(str_replace("\n", "", $html));
	}

	/**
	 * @param string $expected_html_as_string
	 * @param string $html_as_string
	 */
	public function assertHTMLEquals($expected_html_as_string,$html_as_string){
		$html = new DOMDocument();
		$html->formatOutput = true;
		$html->preserveWhiteSpace = false;
		$expected = new DOMDocument();
		$expected->formatOutput = true;
		$expected->preserveWhiteSpace = false;
		$html->loadXML($this->normalizeHTML($html_as_string));
		$expected->loadXML($this->normalizeHTML($expected_html_as_string));
		$this->assertEquals($expected->saveHTML(), $html->saveHTML());
	}
}
