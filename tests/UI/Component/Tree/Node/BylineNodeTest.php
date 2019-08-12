<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."../../../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\Input\Field\FieldRendererFactory;
use ILIAS\UI\Implementation\Component\Symbol\Glyph\GlyphRendererFactory;
use ILIAS\UI\Implementation\Render\DefaultRendererFactory;
use ILIAS\UI\Implementation\Render\JavaScriptBinding;

/**
 * Tests for the SimpleNode.
 */
class BylineNodeTest extends ILIAS_UI_TestBase
{
    /**
     * @var I\Tree\Node\Factory
     */
    private $node_factory;

    /**
     * @var C\Symbol\Icon\Standard|I\Symbol\Icon\Standard
     */
    private $icon;

    public function setUp(): void
	{
		$this->node_factory = new I\Tree\Node\Factory();
		$icon_factory = new I\Symbol\Icon\Factory();
		$this->icon = $icon_factory->standard("", '');
	}

	public function testCreateBylineNode()
	{
		$node = $this->node_factory->bylined('My Label', 'This is my byline', $this->icon);
		$this->assertEquals('My Label', $node->getLabel());
		$this->assertEquals('This is my byline', $node->getByline());
		$this->assertEquals($this->icon, $node->getIcon());
	}

	public function testRendering()
	{
		$node = $this->node_factory->bylined('My Label', 'This is my byline');

		$r = $this->getDefaultRenderer();
		$html = $r->render($node);

		$expected = <<<EOT
			<li id=""class="il-tree-node node-simple">
				<span class="node-line">
					<span class="node-label">My Label</span>
					<span class="node-byline">This is my byline</span>
				</span>
			</li>
EOT;

		$this->assertEquals(
			$this->brutallyTrimHTML($expected),
			$this->brutallyTrimHTML($html)
		);
	}

	public function testRenderingWithIcon()
	{
		$node = $this->node_factory->bylined('My Label', 'This is my byline', $this->icon);

		$r = $this->getDefaultRenderer();
		$html = $r->render($node);

		$expected = <<<EOT
			<li id=""class="il-tree-node node-simple">
				<span class="node-line">
					<div class="icon small" aria-label=""></div>
					<span class="node-label">My Label</span>
					<span class="node-byline">This is my byline</span>
				</span>
			</li>
EOT;

		$this->assertEquals(
			$this->brutallyTrimHTML($expected),
			$this->brutallyTrimHTML($html)
		);
	}

	public function testRenderingWithAsync()
	{
		$node = $this->node_factory->bylined('My Label', 'This is my byline');
		$node = $node->withAsyncURL('something.de');

		$r = $this->getDefaultRenderer();
		$html = $r->render($node);

		$expected = <<<EOT
			<li id=""
				class="il-tree-node node-simple expandable" data-async_url="something.de" data-async_loaded="false">
				<span class="node-line">
					<span class="node-label">My Label</span>
					<span class="node-byline">This is my byline</span>
				</span>
			</li>
EOT;

		$this->assertEquals(
			$this->brutallyTrimHTML($expected),
			$this->brutallyTrimHTML($html)
		);
	}

	public function getDefaultRenderer(JavaScriptBinding $js_binding = null) {
		$ui_factory = $this->getUIFactory();
		$tpl_factory = $this->getTemplateFactory();
		$resource_registry = $this->getResourceRegistry();
		$lng = $this->getLanguage();
		if(!$js_binding){
			$js_binding = $this->getJavaScriptBinding();
		}

		$defaultRendererFactory = new DefaultRendererFactory(
			$ui_factory,
			$tpl_factory,
			$lng,
			$js_binding
		);

		$glyphRendererFactory   = new GlyphRendererFactory(
			$ui_factory,
			$tpl_factory,
			$lng,
			$js_binding
		);

		$fieldRendererFactory = new FieldRendererFactory(
			$ui_factory,
			$tpl_factory,
			$lng,
			$js_binding
		);

		$fsLoader               = new \ILIAS\UI\Implementation\Render\FSLoader(
			$defaultRendererFactory,
			$glyphRendererFactory,
			$fieldRendererFactory
		);

		$loaderResourceRegistryWrapper = new \ILIAS\UI\Implementation\Render\LoaderResourceRegistryWrapper(
			$resource_registry,
			$fsLoader
		);

		$component_renderer_loader
		                               = new \ILIAS\UI\Implementation\Render\LoaderCachingWrapper(
			$loaderResourceRegistryWrapper
		);
		return new TestDefaultRenderer($component_renderer_loader);
	}

	private function brutallyTrimHTML($html)
	{
		$html = str_replace(["\n", "\r", "\t"], "", $html);
		$html = preg_replace('# {2,}#', " ", $html);
		return trim($html);
	}
}
