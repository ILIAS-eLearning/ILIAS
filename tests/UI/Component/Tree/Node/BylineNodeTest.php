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
class BylineNodeTest extends ILIAS_UI_TestBase
{
    private I\Tree\Node\Factory $node_factory;
    private C\Symbol\Icon\Standard $icon;

    public function setUp(): void
    {
        $this->node_factory = new I\Tree\Node\Factory();
        $icon_factory = new I\Symbol\Icon\Factory();
        $this->icon = $icon_factory->standard("", '');
    }

    public function testCreateBylineNode(): void
    {
        $node = $this->node_factory->bylined('My Label', 'This is my byline', $this->icon);
        $this->assertEquals('My Label', $node->getLabel());
        $this->assertEquals('This is my byline', $node->getByline());
        $this->assertEquals($this->icon, $node->getIcon());
    }

    public function testRendering(): void
    {
        $node = $this->node_factory->bylined('My Label', 'This is my byline');

        $r = $this->getDefaultRenderer();
        $html = $r->render($node);

        $expected = <<<EOT
			<li id="" class="il-tree-node node-simple" role="treeitem">
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

    public function testRenderingWithIcon(): void
    {
        $node = $this->node_factory->bylined('My Label', 'This is my byline', $this->icon);

        $r = $this->getDefaultRenderer();
        $html = $r->render($node);

        $expected = <<<EOT
			<li id="" class="il-tree-node node-simple" role="treeitem">
				<span class="node-line">
					<span class="node-label">
						<img class="icon small" src="./templates/default/images/icon_default.svg" alt=""/>
						My Label
					</span>
					<span class="node-byline">This is my byline</span>
				</span>
			</li>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderingWithAsync(): void
    {
        $node = $this->node_factory->bylined('My Label', 'This is my byline');
        $node = $node->withAsyncURL('something.de');

        $r = $this->getDefaultRenderer();
        $html = $r->render($node);

        $expected = <<<EOT
			<li id=""
				 class="il-tree-node node-simple expandable"
				 role="treeitem" aria-expanded="false"
				 data-async_url="something.de" data-async_loaded="false">
				<span class="node-line">
					<span class="node-label">My Label</span>
					<span class="node-byline">This is my byline</span>
				</span>
				<ul role="group"></ul>
			</li>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderingExpanded(): void
    {
        $node = $this->node_factory->bylined('My Label', 'This is my byline');
        $node = $node->withAsyncURL('something.de')->withExpanded(true);

        $r = $this->getDefaultRenderer();
        $html = $r->render($node);

        $expected = <<<EOT
			<li id=""
				 class="il-tree-node node-simple expandable"
				 role="treeitem" aria-expanded="true"
				 data-async_url="something.de" data-async_loaded="false">
				<span class="node-line">
					<span class="node-label">My Label</span>
					<span class="node-byline">This is my byline</span>
				</span>
				<ul role="group"></ul>
			</li>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }
}
