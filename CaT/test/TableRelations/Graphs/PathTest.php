<?php

require_once "CaT/test/TableRelations/Graphs/GraphTestFactory.php";
use CaT\TableRelations\Graphs as Graphs;
class PathTest extends PHPUnit_Framework_TestCase {

	/**
	 * @expectedException CaT\TableRelations\Graphs\GraphException
	 */
	public function test_path_double_node() {
		$path = Graphs\Path::getInstanceBySequence(array("1"=>array(),"2"=>array(),"3"=>array()));
		$path->addNode("2",array());
	}

	public function test_contains() {
		$path = Graphs\Path::getInstanceBySequence(array("1"=>array(),"2"=>array(),"3"=>array()));
		$this->assertTrue($path->contains("2"));
		$this->assertFalse($path->contains("2a"));
	}

	public function test_path_crossing() {
		$path1 = Graphs\Path::getInstanceBySequence(array("1"=>array(),"2"=>array(),"3"=>array()));
		$path2 = Graphs\Path::getInstanceBySequence(array("1a"=>array(),"2"=>array(),"3a"=>array()));
		$this->assertEquals($path1->intersectsPathAt($path2),"2");
	}

	public function test_append() {
		$path1 = Graphs\Path::getInstanceBySequence(array("1"=>array(),"2"=>array(),"3"=>array()));
		$path2 = Graphs\Path::getInstanceBySequence(array("1a"=>array(),"2a"=>array(),"3a"=>array()));
		$seq = $path1->append($path2)->sequence();
		$this->assertSame($seq,
			array("1"=>array(),"2"=>array(),"3"=>array(),"1a"=>array(),"2a"=>array(),"3a"=>array()));
	}

	public function test_append_up_to_id() {
		$path1 = Graphs\Path::getInstanceBySequence(array("1"=>array(),"2"=>array(),"3"=>array()));
		$path2 = Graphs\Path::getInstanceBySequence(array("1a"=>array(),"2a"=>array(),"3a"=>array(),"2"=>array(),"4a"=>array()));
		$seq = $path1->appendUpTo($path2,"3a")->sequence();
		$this->assertSame($seq,
			array("1"=>array(),"2"=>array(),"3"=>array(),"1a"=>array(),"2a"=>array()));
	}

	public function test_append_up_to_crossing() {
		$path1 = Graphs\Path::getInstanceBySequence(array("1"=>array(),"2"=>array(),"3"=>array()));
		$path2 = Graphs\Path::getInstanceBySequence(array("1a"=>array(),"2a"=>array(),"3a"=>array(),"2"=>array(),"4a"=>array()));
		$seq = $path1->appendUpTo($path2)->sequence();
		$this->assertSame($seq,
			array("1"=>array(),"2"=>array(),"3"=>array(),"1a"=>array(),"2a"=>array(),"3a"=>array()));
	}

	public function test_subpath_to_inc() {
		$seq = Graphs\Path::getInstanceBySequence(array("1a"=>array(),"2a"=>array(),"3a"=>array(),"4a"=>array(),"5a"=>array()))
				->getSubpathUpToIncluding("3a")
				->sequence();
		$this->assertSame($seq,array("1a"=>array(),"2a"=>array(),"3a"=>array()));
	}
}