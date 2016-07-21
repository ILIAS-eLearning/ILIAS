<?php
require_once "test/TableRelations/Graphs/GraphTestFactory.php";
use \CaT\TableRelations\Graphs as Graphs;
class GraphTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->gtf = new GraphTestFactory();
	}

	public function circleGraph() {
		$gtf = $this->gtf();
		$g = $gtf->Graph();
		$g->addNode($gtf->Node("1"));
		$g->addNode($gtf->Node("2"));
		$g->addNode($gtf->Node("3"));
		$g->connectNodesSymmetric($gtf->Edge("1","2"));
		$g->connectNodesSymmetric($gtf->Edge("2","3"));
		$g->connectNodesSymmetric($gtf->Edge("3","1"));
		return $g;
	}

	public function starGraph() {
		$gtf = $this->gtf();
		$g = $gtf->Graph();
		$g->addNode($gtf->Node("1"),1);
		$g->addNode($gtf->Node("2"),1);
		$g->addNode($gtf->Node("3"),2);
		$g->addNode($gtf->Node("4"),1);
		$g->addNode($gtf->Node("5"),1);
		$g->addNode($gtf->Node("6"),2);
		$g->connectNodesSymmetric($gtf->Edge("1","2"));
		$g->connectNodesSymmetric($gtf->Edge("2","3"));
		$g->connectNodesSymmetric($gtf->Edge("3","1"));
		$g->connectNodesDirected($gtf->Edge("1","4"));
		$g->connectNodesDirected($gtf->Edge("2","5"));
		$g->connectNodesDirected($gtf->Edge("3","6"));
		return $g;
	}

	public function test_add_nodes() {
		$g = $this->circleGraph();

		$nodes = $g->nodes();
		$edges = $g->edges();

		foreach ($nodes as $node) {
			$node_ids[] = $node->id();
		}
		$this->asserCount(3, $node_ids);
		$this->assertCount(0,array_diff($node_ids, array("1","2","3")));
		$this->assertCount(0,array_diff(array("1","2","3"), $node_ids));

		$this->asserCount(3, $edges);
		foreach ($edges as $edge) {
			$from_id = $edge->from();
			$to_id = $edge->to();
			$this->assertTrue(in_array($from, array("1","2","3")));
			$this->assertTrue((string)((int)$from+1) === $to);
		}
	}

	public function test_nodes_between() {
		$g = $this->starGraph();
		$nodes = $g->getNodesBetween("1","5");
		foreach ($nodes as $node) {
			$node_ids[] = $node->id();
		}
		$this->asserCount(4,$node_ids);
		$this->assertCount(0,array_diff($node_ids, array("1","2","3","5")));
		$this->assertCount(0,array_diff(array("1","2","3","5"), $node_ids));
	}

	public function test_nodes_within_subgraph_between() {
		$g = $this->starGraph();
		$nodes = $g->getNodesWithinSubgraphBetween("1","5",1);
		foreach ($nodes as $node) {
			$node_ids[] = $node->id();
		}
		$this->asserCount(3,$node_ids);
		$this->assertCount(0,array_diff($node_ids, array("1","2","5")));
		$this->assertCount(0,array_diff(array("1","2","5"), $node_ids));
	}

	/**
	 * @expectedException Graphs\GraphException
	 */
	public function test_no_double_nodes_in_graph() {
		$g = $this->circleGraph();
		$g->addNode($this->gtf->Node("3"));
	}

	public function test_graph_connected() {
		$g = $this->starGraph();
		$this->assertTrue($g->isConnected());
		$g->addNode($gtf->Node("7"));
		$g->addNode($gtf->Node("8"));
		$g->connectNodesDirected($gtf->Edge("7","8"));
		$this->assertFalse($g->isConnected());
	}
}