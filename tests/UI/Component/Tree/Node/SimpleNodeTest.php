<?php

declare(strict_types=1);

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
require_once(__DIR__ . "../../../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as I;

/**
 * Tests for the SimpleNode.
 */
class SimpleNodeTest extends ILIAS_UI_TestBase
{
    private I\Tree\Node\Factory $node_factory;
    private C\Symbol\Icon\Standard $icon;

    public function setUp(): void
    {
        $this->node_factory = new I\Tree\Node\Factory();
        $icon_factory = new I\Symbol\Icon\Factory();
        $this->icon = $icon_factory->standard("", '');
    }

    public function brutallyTrimHTML(string $html): string
    {
        $html = str_replace(["\n", "\r", "\t"], "", $html);
        $html = preg_replace('# {2,}#', " ", $html);
        return trim($html);
    }

    public function testConstruction(): C\Tree\Node\Simple
    {
        $node = $this->node_factory->simple('simple');
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Tree\\Node\\Simple",
            $node
        );
        return $node;
    }

    public function testWrongConstruction(): void
    {
        $this->expectException(ArgumentCountError::class);
        $this->node_factory->simple();
    }

    public function testConstructionWithIcon(): C\Tree\Node\Simple
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
    public function testGetLabel(C\Tree\Node\Simple $node): void
    {
        $this->assertEquals("label", $node->getLabel());
    }

    /**
     * @depends testConstructionWithIcon
     */
    public function testGetIcon(C\Tree\Node\Simple $node): C\Tree\Node\Simple
    {
        $this->assertEquals($this->icon, $node->getIcon());
        return $node;
    }

    /**
     * @depends testConstruction
     */
    public function testDefaultAsyncLoading(C\Tree\Node\Simple $node): void
    {
        $this->assertFalse($node->getAsyncLoading());
    }

    /**
     * @depends testConstruction
     */
    public function testWithAsyncURL(C\Tree\Node\Simple $node): C\Tree\Node\Simple
    {
        $url = 'something.de';
        $node = $node->withAsyncURL($url);
        $this->assertTrue($node->getAsyncLoading());
        $this->assertEquals($url, $node->getAsyncURL());
        return $node;
    }

    /**
     * @depends testConstruction
     */
    public function testRendering(C\Tree\Node\Simple $node): void
    {
        $r = $this->getDefaultRenderer();
        $html = $r->render($node);

        $expected = <<<EOT
			<li id="" class="il-tree-node node-simple" role="treeitem">
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
    public function testRenderingWithAsync(C\Tree\Node\Simple $node): void
    {
        $r = $this->getDefaultRenderer();
        $html = $r->render($node);

        $expected = <<<EOT
			<li id=""
				 class="il-tree-node node-simple expandable"
				 role="treeitem" aria-expanded="false"
				 data-async_url="something.de" data-async_loaded="false">
				<span class="node-line">
					<span class="node-label">simple</span>
				</span>
				<ul role="group"></ul>
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
    public function testRenderingWithIcon(C\Tree\Node\Simple $node): void
    {
        $r = $this->getDefaultRenderer();
        $html = $r->render($node);

        $expected = <<<EOT
			<li id="" class="il-tree-node node-simple" role="treeitem">
				<span class="node-line">
					<span class="node-label">
						<img class="icon small" src="./templates/default/images/icon_default.svg" alt=""/>
						label
					</span>
				</span>
			</li>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }
}
