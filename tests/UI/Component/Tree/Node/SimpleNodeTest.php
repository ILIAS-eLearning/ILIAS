<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."../../../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Component as I;

/**
 * Tests for the SimpleNode.
 */
class SimpleNodeTest extends ILIAS_UI_TestBase
{
	public function setUp(): void
	{
		$this->node_factory = new I\Tree\Node\Factory();
		$icon_factory = new I\Symbol\Icon\Factory();
		$this->icon = $icon_factory->standard("", '');
	}

	public function brutallyTrimHTML($html)
	{
		$html = str_replace(["\n", "\r", "\t"], "", $html);
		$html = preg_replace('# {2,}#', " ", $html);
		return trim($html);
	}

	public function testConstruction()
	{
		$node = $this->node_factory->simple('simple');
		$this->assertInstanceOf(
			"ILIAS\\UI\\Component\\Tree\\Node\\Simple",
			$node
		);
		return $node;
	}

	public function testWrongConstruction()
	{
		$this->expectException(\ArgumentCountError::class);
		$node = $this->node_factory->simple();
	}

	public function testConstructionWithIcon()
	{
		$node = $this->node_factory->simple('label', $this->icon);
		$this->assertInstanceOf(
			"ILIAS\\UI\\Component\\Tree\\Node\\Simple",
			$node
		);
		return $node;
	}

	/**
	 * @depends testConstructionWithIcon
	 */
	public function testGetLabel($node)
	{
		$this->assertEquals(
			"label",
			$node->getLabel()
		);
	}

	/**
	 * @depends testConstructionWithIcon
	 */
	public function testGetIcon($node)
	{
		$this->assertEquals(
			$this->icon,
			$node->getIcon()
		);
		return $node;
	}

	/**
	 * @depends testConstruction
	 */
	public function testDefaultAsyncLoading($node)
	{
		$this->assertFalse(
			$node->getAsyncLoading()
		);
	}

	/**
	 * @depends testConstruction
	 */
	public function testWithAsyncURL($node)
	{
		$url = 'something.de';
		$node = $node->withAsyncURL($url);
		$this->assertTrue(
			$node->getAsyncLoading()
		);
		$this->assertEquals(
			$url,
			$node->getAsyncURL()
		);
		return $node;
	}

	/**
	 * @depends testConstruction
	 */
	public function testRendering($node)
	{
		$r = $this->getDefaultRenderer();
		$html = $r->render($node);

		$expected = <<<EOT
			<li id=""class="il-tree-node node-simple">
				<span class="node-line">
					<span class="node-label">simple</span>
				</span>
			</li>
EOT;

		$this->assertEquals(
			$this->brutallyTrimHTML($expected),
			$this->brutallyTrimHTML($html)
		);
	}

	/**
	 * @depends testWithAsyncURL
	 */
	public function testRenderingWithAsync($node)
	{
		$r = $this->getDefaultRenderer();
		$html = $r->render($node);

		$expected = <<<EOT
			<li id=""
				class="il-tree-node node-simple expandable" data-async_url="something.de" data-async_loaded="false">
				<span class="node-line">
					<span class="node-label">simple</span>
				</span>
			</li>
EOT;

		$this->assertEquals(
			$this->brutallyTrimHTML($expected),
			$this->brutallyTrimHTML($html)
		);
	}

	/**
	 * @depends testConstructionWithIcon
	 */
	public function testRenderingWithIcon($node)
	{
		$r = $this->getDefaultRenderer();
		$html = $r->render($node);

		$expected = <<<EOT
			<li id=""class="il-tree-node node-simple">
				<span class="node-line">
					<span class="node-label"><div class="icon small" aria-label=""></div>label</span>
				</span>
			</li>
EOT;

		$this->assertEquals(
			$this->brutallyTrimHTML($expected),
			$this->brutallyTrimHTML($html)
		);
	}

}
