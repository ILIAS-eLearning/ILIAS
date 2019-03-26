<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Implementation\Component\Tree\Tree;


/**
 * Dummy-implementation for testing
 */
class TestingTree extends Tree
{
}

/**
 * Tests for the (Base-)Tree.
 */
class TreeTest extends ILIAS_UI_TestBase
{
	public function testWrongConstruction()
	{
		$this->expectException(\ArgumentCountError::class);
		$tree = new TestingTree();
	}

	public function testWrongTypeConstruction()
	{
		$this->expectException(\TypeError::class);
		$tree = new TestingTree('something');
	}

	public function testConstruction()
	{
		$recursion = new class implements \ILIAS\UI\Component\Tree\TreeRecursion
		{
			public function getChildren($record, $environment = null): array
			{
				return [];
			}

			public function build(
				\ILIAS\UI\Component\Tree\Node\Factory $factory,
				$record,
				$environment = null
			): \ILIAS\UI\Component\Tree\Node\Node {

			}
		};

		$tree = new TestingTree($recursion);
		$this->assertInstanceOf(
			"ILIAS\\UI\\Component\\Tree\\Tree",
			$tree
		);

		return $tree;
	}

	/**
	 * @depends testConstruction
	 */
	public function testGetRecursion($tree)
	{
		$env = ['key1'=>'val1', 'key2'=>2];
		$this->assertInstanceOf(
			"ILIAS\\UI\\Component\\Tree\\TreeRecursion",
			$tree->getRecursion()
		);
	}

	/**
	 * @depends testConstruction
	 */
	public function testWithEnvironment($tree)
	{
		$env = ['key1'=>'val1', 'key2'=>2];
		$this->assertEquals(
			$env,
			$tree->withEnvironment($env)->getEnvironment()
		);
	}

	/**
	 * @depends testConstruction
	 */
	public function testWithData($tree)
	{
		$data = ['entry1', 'entry2'];
		$this->assertEquals(
			$data,
			$tree->withData($data)->getData()
		);
	}

	/**
	 * @depends testConstruction
	 */
	public function testWithHighlightOnNodeClick($tree)
	{
		$this->assertFalse(
			$tree->getHighlightOnNodeClick()
		);
		$this->assertTrue(
			$tree->withHighlightOnNodeClick(true)->getHighlightOnNodeClick()
		);
	}

}
