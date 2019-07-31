<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."../../../../Base.php");

use \ILIAS\UI\Implementation\Component\Tree\Node\Node;
use \ILIAS\UI\Implementation\Component as I;

/**
 * Dummy-implementation for testing
 */
class TestingNode extends Node
{
}

/**
 * Tests for the (Base-)Node.
 */
class NodeTest extends ILIAS_UI_TestBase
{
	public function testConstruction()
	{
		$node = new TestingNode("");
		$this->assertInstanceOf(
			"ILIAS\\UI\\Component\\Tree\\Node\\Node",
			$node
		);

		return $node;
	}

	/**
	 * @depends testConstruction
	 */
	public function testDefaults($node)
	{
		$this->assertFalse($node->isExpanded());
		$this->assertFalse($node->isHighlighted());
		$this->assertEquals([], $node->getSubnodes());
	}

	/**
	 * @depends testConstruction
	 */
	public function testWithExpanded($node)
	{
		$this->assertTrue(
			$node->withExpanded(true)->isExpanded()
		);
	}

	/**
	 * @depends testConstruction
	 */
	public function testWithHighlighted($node)
	{
		$this->assertTrue(
			$node->withHighlighted(true)->isHighlighted()
		);
	}

	/**
	 * @depends testConstruction
	 */
	public function testWithOnClick($node)
	{
		$sig_gen = 	new I\SignalGenerator();
		$sig = $sig_gen->create();

		$node = $node->withOnClick($sig);
		$check = $node->getTriggeredSignals()[0]->getSignal();
		$this->assertEquals($sig, $check);
		return $node;
	}

	/**
	 * @depends testWithOnClick
	 */
	public function testWithAppendOnClick($node)
	{
		$sig_gen = 	new I\SignalGenerator();
		$sig = $sig_gen->create();

		$node = $node->appendOnClick($sig);
		$check = $node->getTriggeredSignals()[1]->getSignal();
		$this->assertEquals($sig, $check);
	}

}
