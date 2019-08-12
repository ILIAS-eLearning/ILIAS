<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."../../../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Component as I;

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

	public function createBylineNode()
    {
        $node = $this->node_factory->bylined('My Label', 'This is my byline', $this->icon);
        $this->assertEquals('My Label', $node->getBylined());
        $this->assertEquals('This is my byline', $node->getBylined());
        $this->assertEquals($this->icon, $node->getIcon());
    }

}
