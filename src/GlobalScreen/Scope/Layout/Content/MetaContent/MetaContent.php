<?php namespace ILIAS\GlobalScreen\Scope\Layout\Content\MetaContent;

use ILIAS\GlobalScreen\Scope\Layout\Content\MetaContent\Media\Css;
use ILIAS\GlobalScreen\Scope\Layout\Content\MetaContent\Media\CssCollection;
use ILIAS\GlobalScreen\Scope\Layout\Content\MetaContent\Media\InlineCss;
use ILIAS\GlobalScreen\Scope\Layout\Content\MetaContent\Media\InlineCssCollection;
use ILIAS\GlobalScreen\Scope\Layout\Content\MetaContent\Media\Js;
use ILIAS\GlobalScreen\Scope\Layout\Content\MetaContent\Media\JsCollection;
use ILIAS\GlobalScreen\Scope\Layout\Content\MetaContent\Media\OnLoadCode;
use ILIAS\GlobalScreen\Scope\Layout\Content\MetaContent\Media\OnLoadCodeCollection;

/**
 * Class MetaContent
 *
 * @package ILIAS\GlobalScreen\Scope\LayoutDefinition\MetaContent
 */
class MetaContent {

	const MEDIA_SCREEN = "screen";
	/**
	 * @var InlineCssCollection
	 */
	private $inline_css;
	/**
	 * @var OnLoadCodeCollection
	 */
	private $on_load_code;
	/**
	 * @var JsCollection
	 */
	private $js;
	/**
	 * @var CssCollection
	 */
	private $css;
	/**
	 * @var string
	 */
	private $base_url = "";


	/**
	 * MetaContent constructor.
	 */
	public function __construct() {
		$this->css = new CssCollection();
		$this->js = new JsCollection();
		$this->on_load_code = new OnLoadCodeCollection();
		$this->inline_css = new InlineCssCollection();
	}


	/**
	 * @param string $path
	 * @param string $media
	 */
	public function addCss(string $path, string $media = self::MEDIA_SCREEN) {
		$this->css->addItem(new Css($path, $media));
	}


	/**
	 * @param string $path
	 * @param bool   $add_version_number
	 * @param int    $batch
	 */
	public function addJs(string $path, bool $add_version_number = false, int $batch = 2) {
		$this->js->addItem(new Js($path, $add_version_number, $batch));
	}


	/**
	 * @param string $content
	 * @param string $media
	 */
	public function addInlineCss(string $content, string $media = self::MEDIA_SCREEN) {
		$this->inline_css->addItem(new InlineCss($content, $media));
	}


	/**
	 * @param string $content
	 * @param int    $batch
	 */
	public function addOnloadCode(string $content, int $batch = 2) {
		$this->on_load_code->addItem(new OnLoadCode($content, $batch));
	}


	/**
	 * @return InlineCssCollection
	 */
	public function getInlineCss(): InlineCssCollection {
		return $this->inline_css;
	}


	/**
	 * @return OnLoadCodeCollection
	 */
	public function getOnLoadCode(): OnLoadCodeCollection {
		return $this->on_load_code;
	}


	/**
	 * @return JsCollection
	 */
	public function getJs(): JsCollection {
		return $this->js;
	}


	/**
	 * @return CssCollection
	 */
	public function getCss(): CssCollection {
		return $this->css;
	}


	/**
	 * @param string $base_url
	 */
	public function setBaseURL(string $base_url) {
		$this->base_url = $base_url;
	}


	/**
	 * @return string
	 */
	public function getBaseURL(): string {
		return $this->base_url;
	}
}
