<?php
use ILIAS\TMS\TableRelations\Graphs as Graphs;
use ILIAS\TMS\TableRelations\TestFixtures\GraphTestFactory as GraphTestFactory;
class GraphTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->gtf = new GraphTestFactory();
	}

	public function circleGraph() {
		$gtf = $this->gtf;
		$g = $gtf->Graph();
		$g->addNode($gtf->Node("a1",1));
		$g->addNode($gtf->Node("a2",1));
		$g->addNode($gtf->Node("a3",1));
		$g->connectNodesSymmetric($gtf->Edge("a1","a2"));
		$g->connectNodesSymmetric($gtf->Edge("a2","a3"));
		$g->connectNodesSymmetric($gtf->Edge("a3","a1"));
		return $g;
	}

	public function starGraph() {
		$gtf = $this->gtf;
		$g = $gtf->Graph();
		$g->addNode($gtf->Node("a1",1));
		$g->addNode($gtf->Node("a2",1));
		$g->addNode($gtf->Node("a3",2));
		$g->addNode($gtf->Node("a4",1));
		$g->addNode($gtf->Node("a5",1));
		$g->addNode($gtf->Node("a6",2));
		$g->connectNodesSymmetric($gtf->Edge("a1","a2"));
		$g->connectNodesSymmetric($gtf->Edge("a2","a3"));
		$g->connectNodesSymmetric($gtf->Edge("a3","a1"));
		$g->connectNodesDirected($gtf->Edge("a1","a4"));
		$g->connectNodesDirected($gtf->Edge("a2","a5"));
		$g->connectNodesDirected($gtf->Edge("a3","a6"));
		return $g;
	}

	public function twoRectGraph($connected = true) {
		$gtf = $this->gtf;
		$g = $gtf->Graph();
		$g->addNode($gtf->Node("a1",1));
		$g->addNode($gtf->Node("a2",1));
		$g->addNode($gtf->Node("a3",1));
		$g->addNode($gtf->Node("a4",1));
		$g->addNode($gtf->Node("b1",1));
		$g->addNode($gtf->Node("b2",1));
		$g->addNode($gtf->Node("b3",1));
		$g->addNode($gtf->Node("b4",1));		
		$g->connectNodesSymmetric($gtf->Edge("a1","a2"));
		$g->connectNodesSymmetric($gtf->Edge("a2","a3"));
		$g->connectNodesSymmetric($gtf->Edge("a3","a4"));
		$g->connectNodesSymmetric($gtf->Edge("a4","a1"));
		$g->connectNodesSymmetric($gtf->Edge("b1","b2"));
		$g->connectNodesSymmetric($gtf->Edge("b2","b3"));
		$g->connectNodesSymmetric($gtf->Edge("b3","b4"));
		$g->connectNodesSymmetric($gtf->Edge("b4","b1"));
		if($connected) {
			$g->connectNodesSymmetric($gtf->Edge("a3","b1"));
		}
		return $g;
	}

	public function test_add_nodes() {
		$g = $this->circleGraph();
		$g_edges = array("a1" => "a2"
						,"a2" => "a3"
						,"a3" => "a1");
		$nodes = $g->nodes();
		$edges = $g->edges();

		foreach ($nodes as $node) {
			$node_ids[] = $node->id();
		}
		$this->assertCount(3, $node_ids);
		$this->assertCount(0,array_diff($node_ids, array("a1","a2","a3")));
		$this->assertCount(0,array_diff(array("a1","a2","a3"), $node_ids));

		$this->assertCount(3, $edges);
		foreach ($edges as $edge) {
			$from_id = $edge->fromId();
			$to_id = $edge->toId();
			$this->assertEquals($g_edges[$from_id], $to_id);
		}
	}

	public function test_nodes_between() {
		$g = $this->starGraph();
		$nodes = $g->getNodesBetween("a1","a5");
		$node_ids = array();
		foreach ($nodes as $node) {
			$node_ids[] = $node->id();
		}
		$this->assertCount(0,array_diff($node_ids, array("a1","a2","a3","a5")));
		$this->assertCount(0,array_diff(array("a1","a2","a3","a5"), $node_ids));

		$g = $this->twoRectGraph();
		$nodes = $g->getNodesBetween("a1","b1");
		$node_ids = array();
		foreach ($nodes as $node) {
			$node_ids[] = $node->id();
		}
		$this->assertCount(0,array_diff($node_ids, array("a1","a2","a3","a4","b1")));
		$this->assertCount(0,array_diff(array("a1","a4","a3","a4","b1"), $node_ids));

		$g = $this->twoRectGraph(false);
		$nodes = $g->getNodesBetween("a1","b1");
		$node_ids = array();
		foreach ($nodes as $node) {
			$node_ids[] = $node->id();
		}
		$this->assertCount(0,$node_ids);
	}

	public function test_nodes_within_subgraph_between() {
		$g = $this->starGraph();
		$nodes = $g->getNodesWithinSubgraphBetween("a1","a5",1);

		$node_ids = array();
		foreach ($nodes as $node) {
			$node_ids[] = $node->id();

		}
		$this->assertCount(3,$node_ids);
		$this->assertCount(0,array_diff($node_ids, array("a1","a2","a5")));
		$this->assertCount(0,array_diff(array("a1","a2","a5"), $node_ids));
	}

	/**
	 * @expectedException ILIAS\TMS\TableRelations\Graphs\GraphException
	 */
	public function test_no_double_nodes_in_graph() {
		$g = $this->circleGraph();
		$g->addNode($this->gtf->Node("a3",1));
	}

	public function test_connection_fron_to() {
		$g = $this->starGraph();
		$gtf = $this->gtf;
		$this->assertNull($g->edgeBetween("a1","a5"));
		$this->assertEquals($g->edgeBetween("a1","a4"), $gtf->Edge("a1","a4"));
	}

	public function test_graph_connected() {
		$gtf = $this->gtf;
		$g = $this->starGraph();
		$this->assertTrue($g->connected("a1","a5"));
		$g->addNode($gtf->Node("a7"));
		$g->addNode($gtf->Node("a8"));
		$g->connectNodesDirected($gtf->Edge("a7","a8"));
		$this->assertFalse($g->connected("a1","a7"));
		$this->assertTrue($g->connected("a1","a1"));
	}
}
