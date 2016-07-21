<?php
require_once "test/TableRelations/Graphs/GraphTestFactory.php";

class GraphTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->gtf = new GraphTestFactory();
	}
}