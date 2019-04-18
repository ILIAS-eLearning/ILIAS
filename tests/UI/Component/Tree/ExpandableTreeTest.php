<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."../../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Component as I;


class DataNode
{
	public function __construct(string $label, array $children = [])
	{
		$this->label = $label;
		$this->children = $children;
	}
	public function getLabel()
	{
		return $this->label;
	}
	public function getChildren()
	{
		return $this->children;
	}
}

class Recursion implements C\Tree\TreeRecursion
{
	public function getChildren($record, $environment = null): array
	{
		return $record->getChildren();
	}

	public function build(
		C\Tree\Node\Factory $factory,
		$record,
		$environment = null
	): C\Tree\Node\Node {
		return $factory->simple($record->getLabel());
	}
}

/**
 * Tests for the Expandable Tree.
 */
class ExpandableTreeTest extends ILIAS_UI_TestBase
{

	public function getUIFactory() {
		$factory = new class extends NoUIFactory {
			public function tree() {
				return new I\Tree\Factory();
			}
		};
		return $factory;
	}

	public function setUp(): void
	{
		$n11 = new DataNode('1.1');
		$n12 = new DataNode('1.2', array(new DataNode('1.2.1')));
		$n1 = new DataNode('1', [$n11, $n12]);
		$n2 = new DataNode('2');
		$data = [$n1, $n2];

		$recursion = new Recursion();
		$f = $this->getUIFactory();
		$this->tree = $f->tree()->expandable($recursion)
			->withData($data);
	}

	public function brutallyTrimHTML($html)
	{
		$html = str_replace(["\n", "\t"], "", $html);
		$html = preg_replace('# {2,}#', " ", $html);
		return trim($html);
	}

	public function testRendering()
	{
		$r = $this->getDefaultRenderer();
		$html = $r->render($this->tree);

		$expected = <<<EOT
		<ul id="id_1" class="il-tree">
			<li id=""class="il-tree-node node-simple expandable">
				<span class="node-line"><span class="node-label">1</span></span>

				<ul>
					<li id=""class="il-tree-node node-simple">
						<span class="node-line"><span class="node-label">1.1</span></span>
					</li>
					<li id=""class="il-tree-node node-simple expandable">
						<span class="node-line"><span class="node-label">1.2</span></span>

						<ul>
							<li id=""class="il-tree-node node-simple">
								<span class="node-line"><span class="node-label">1.2.1</span></span>
							</li>
						</ul>
					</li>
				</ul>
			</li>
			<li id=""class="il-tree-node node-simple">
				<span class="node-line"><span class="node-label">2</span></span>
			</li>
		</ul>
EOT;

		$this->assertEquals(
			$this->brutallyTrimHTML($expected),
			$this->brutallyTrimHTML($html)
		);
	}

}