<?php
require_once "CaT/test/TableRelations/Graphs/GraphTestFactory.php";
use CaT\TableRelations\Graphs as Graphs;
class PathTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->gtf = new GraphTestFactory();
	}


	public function test_path_create() {
		$gtf = $this->gtf;
		$path = Graphs\Path::getInstanceBySequence(array($gtf->Node("a1"),$gtf->Node("a2"),$gtf->Node("a3")));
		$seq = array();
		foreach($path->sequence() as $node) {
			$seq[] = $node->id();
		}
		$this->assertSame($seq,array("a1","a2","a3"));
	}
	/**
	 * @expectedException CaT\TableRelations\Graphs\GraphException
	 */
	public function test_path_double_node() {
		$gtf = $this->gtf;
		$path = Graphs\Path::getInstanceBySequence(array($gtf->Node("a1"),$gtf->Node("a2"),$gtf->Node("a3")));
		$path->addNode($gtf->Node("a2"));
	}

	public function test_contains() {
		$gtf = $this->gtf;
		$path = Graphs\Path::getInstanceBySequence(array($gtf->Node("a1"),$gtf->Node("a2"),$gtf->Node("a3")));
		$this->assertTrue($path->contains("a2"));
		$this->assertFalse($path->contains("2a"));
	}

	public function test_path_crossing() {
		$gtf = $this->gtf;
		$path1 = Graphs\Path::getInstanceBySequence(array($gtf->Node("a1"),$gtf->Node("a2"),$gtf->Node("a3")));
		$path2 = Graphs\Path::getInstanceBySequence(array($gtf->Node("1a"),$gtf->Node("a2"),$gtf->Node("3a")));
		$this->assertEquals($path1->intersectsPathAt($path2),"a2");
	}

	public function test_append() {
		$gtf = $this->gtf;
		$path1 = Graphs\Path::getInstanceBySequence(array($gtf->Node("a1"),$gtf->Node("a2"),$gtf->Node("a3")));
		$path2 = Graphs\Path::getInstanceBySequence(array($gtf->Node("1a"),$gtf->Node("2a"),$gtf->Node("3a")));
		$seq_n = $path1->append($path2)->sequence();
		$seq = array();
		foreach($seq_n as $node) {
			$seq[] = $node->id();
		}
		$this->assertSame($seq,
			array("a1","a2","a3","1a","2a","3a"));
	}

	public function test_append_up_to_id() {
		$gtf = $this->gtf;
		$path1 = Graphs\Path::getInstanceBySequence(array($gtf->Node("a1"),$gtf->Node("a2"),$gtf->Node("a3")));
		$path2 = Graphs\Path::getInstanceBySequence(array($gtf->Node("1a"),$gtf->Node("2a"),$gtf->Node("3a"),$gtf->node("4a")));
		$seq_n = $path1->appendUpTo($path2,"3a")->sequence();
		$seq = array();
		foreach($seq_n as $node) {
			$seq[] = $node->id();
		}
		$this->assertSame($seq,
			array("a1","a2","a3","1a","2a"));
	}

	public function test_append_up_to_crossing() {
		$gtf = $this->gtf;
		$path1 = Graphs\Path::getInstanceBySequence(array($gtf->Node("a1"),$gtf->Node("a2"),$gtf->Node("a3")));
		$path2 = Graphs\Path::getInstanceBySequence(array($gtf->Node("1a"),$gtf->Node("2a"),$gtf->Node("a2"),$gtf->node("4a")));
		$seq_n = $path1->appendUpTo($path2)->sequence();
		$seq = array();
		foreach($seq_n as $node) {
			$seq[] = $node->id();
		}
		$this->assertSame($seq,
			array("a1","a2","a3","1a","2a"));
	}

	public function test_subpath_to_inc() {
		$gtf = $this->gtf;
		$seq_n = Graphs\Path::getInstanceBySequence(
			array($gtf->Node("1a"),$gtf->Node("2a"),$gtf->Node("3a"),$gtf->node("4a"),$gtf->node("5a")))
				->getSubpathUpToIncluding("3a")
				->sequence();
		$seq = array();
		foreach($seq_n as $node) {
			$seq[] = $node->id();
		}
		$this->assertSame($seq,array("1a","2a","3a"));
	}
}
