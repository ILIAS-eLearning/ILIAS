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
 
require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "../../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as I;

class DataNode
{
    protected string $label;
    protected array $children;

    public function __construct(string $label, array $children = [])
    {
        $this->label = $label;
        $this->children = $children;
    }
    public function getLabel() : string
    {
        return $this->label;
    }
    public function getChildren() : array
    {
        return $this->children;
    }
}

class Recursion implements C\Tree\TreeRecursion
{
    public function getChildren($record, $environment = null) : array
    {
        return $record->getChildren();
    }

    public function build(
        C\Tree\Node\Factory $factory,
        $record,
        $environment = null
    ) : C\Tree\Node\Node {
        return $factory->simple($record->getLabel());
    }
}

/**
 * Tests for the Expandable Tree.
 */
class ExpandableTreeTest extends ILIAS_UI_TestBase
{
    protected C\Tree\Tree $tree;

    public function getUIFactory() : NoUIFactory
    {
        return new class extends NoUIFactory {
            public function tree() : C\Tree\Factory
            {
                return new I\Tree\Factory();
            }
        };
    }

    public function setUp() : void
    {
        $n11 = new DataNode('1.1');
        $n12 = new DataNode('1.2', array(new DataNode('1.2.1')));
        $n1 = new DataNode('1', [$n11, $n12]);
        $n2 = new DataNode('2');
        $data = [$n1, $n2];

        $label = "label";
        $recursion = new Recursion();
        $f = $this->getUIFactory();
        $this->tree = $f->tree()->expandable($label, $recursion)
            ->withData($data);
    }

    public function brutallyTrimHTML(string $html) : string
    {
        $html = str_replace(["\n", "\r", "\t"], "", $html);
        $html = preg_replace('# {2,}#', " ", $html);
        return trim($html);
    }

    public function testRendering() : void
    {
        $r = $this->getDefaultRenderer();
        $html = $r->render($this->tree);

        $expected = '<ul id="id_1" class="il-tree" role="tree" aria-label="label">' . $this->getInnerTreePart() . '</ul>';

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderingAsSubTree() : void
    {
        $r = $this->getDefaultRenderer();
        $html = $r->render($this->tree->withIsSubTree(true));

        $expected = $this->getInnerTreePart();

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    protected function getInnerTreePart() : string
    {
        return '<li id="" class="il-tree-node node-simple expandable" role="treeitem" aria-expanded="false">
				<span class="node-line"><span class="node-label">1</span></span>

				<ul role="group">
					<li id="" class="il-tree-node node-simple" role="treeitem">
						<span class="node-line"><span class="node-label">1.1</span></span>
					</li>
					<li id="" class="il-tree-node node-simple expandable" role="treeitem" aria-expanded="false">
						<span class="node-line"><span class="node-label">1.2</span></span>

						<ul role="group">
							<li id="" class="il-tree-node node-simple" role="treeitem">
								<span class="node-line"><span class="node-label">1.2.1</span></span>
							</li>
						</ul>
					</li>
				</ul>
			</li>
			<li id="" class="il-tree-node node-simple" role="treeitem">
				<span class="node-line"><span class="node-label">2</span></span>
			</li>';
    }
}
