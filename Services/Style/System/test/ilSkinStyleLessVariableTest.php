<?php

include_once("./Services/Style/System/classes/Less/class.ilSystemStyleLessVariable.php");

/**
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSkinStyleLessVariableTest extends PHPUnit_Framework_TestCase {

	public function testConstruct() {
		$variable = new ilSystemStyleLessVariable("name", "value", "comment","category_name", ["references_id"]);
		$this->assertEquals("name", $variable->getName());
		$this->assertEquals("value", $variable->getValue());
		$this->assertEquals("comment", $variable->getComment());
		$this->assertEquals("category_name", $variable->getCategoryName());
		$this->assertEquals(["references_id"], $variable->getReferences());
	}

	public function testSetters() {
		$variable = new ilSystemStyleLessVariable("name", "value", "comment","category_name", ["references_id"]);

		$variable->setName("newName");
		$variable->setValue("newValue");
		$variable->setComment("newComment");
		$variable->setCategoryName("new_category_name");
		$variable->setReferences(["new_references_id"]);

		$this->assertEquals("newName", $variable->getName());
		$this->assertEquals("newValue", $variable->getValue());
		$this->assertEquals("newComment", $variable->getComment());
		$this->assertEquals("new_category_name", $variable->getCategoryName());
		$this->assertEquals(["new_references_id"], $variable->getReferences());
	}

	public function testNastyHack() {
		$variable = new ilSystemStyleLessVariable("name", "value", "comment","category_name", ["references_id"]);

		$variable->setValue("\"../../Services/UICore/lib/bootstrap-3.2.0/fonts/\"");
		$this->assertEquals("\"../../../../Services/UICore/lib/bootstrap-3.2.0/fonts/\"", $variable->getValue());
	}

	public function testToString(){
		$variable = new ilSystemStyleLessVariable("name", "value", "comment","category_name", ["references_id"]);
		$this->assertEquals("//** comment\n@name:\t\tvalue;\n",(string)$variable);
	}

}
