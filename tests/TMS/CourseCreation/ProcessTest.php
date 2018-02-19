<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

use ILIAS\TMS\CourseCreation;

if (!class_exists(\ilTree::class)) {
	require_once("Services/Tree/classes/class.ilTree.php");
}
if (!class_exists(\ilDBInterface::class)) {
	require_once("Services/Database/interfaces/interface.ilDBInterface.php");
}
if (!class_exists(\ilObject::class)) {
	require_once("Services/Object/classes/class.ilObject.php");
}

class _SpecialObject extends \ilObject {
	public function afterCourseCreation() {
	}
	function setOfflineStatus($a_value)	{
	}
}

class _CourseCreationProcess extends CourseCreation\Process {
	public function _getCopyWizardOptions($request) {
		return $this->getCopyWizardOptions($request);
	}
	public function _adjustCourseTitle($request) {
		return $this->adjustCourseTitle($request);
	}
	public function _setCourseOnline($request) {
		return $this->setCourseOnline($request);
	}
	public function _configureCopiedObjects($request) {
		return $this->configureCopiedObjects($request);
	}
}

class TMS_CourseCreation_ProcessTest extends PHPUnit_Framework_TestCase {
	public function test_getCopyWizardOptions() {
		$tree = $this->createMock(\ilTree::class);
		$db = $this->createMock(\ilDBInterface::class);
		$request = $this->createMock(CourseCreation\Request::class);

		$crs_id = 42;

		$request
			->expects($this->once())
			->method("getCourseRefId")
			->willReturn($crs_id);

		$tree
			->expects($this->once())
			->method("getSubTreeIds")
			->with($crs_id)
			->willReturn([1,2,3]);

		$request
			->expects($this->exactly(3))
			->method("getCopyOptionFor")
			->withConsecutive([1], [2], [3])
			->will($this->onConsecutiveCalls(10,20,30));

		$process = new _CourseCreationProcess($tree, $db);

		$expected = [1 => [ "type" => 10], 2 => ["type" => 20], 3 => ["type" => 30]];
		$options = $process->_getCopyWizardOptions($request);

		$this->assertEquals($expected, $options);
	}

	public function test_configureCopiedObjects() {
		$tree = $this->createMock(\ilTree::class);
		$db = $this->createMock(\ilDBInterface::class);
		$request = $this->createMock(CourseCreation\Request::class);

		$process = $this->getMockBuilder(_CourseCreationProcess::class)
			->setMethods(["getCopyMappings", "getObjectByRefId"])
			->setConstructorArgs([$tree, $db])
			->getMock();

		$target_ref_id = 23;
		$source_ref_id = 42;
		$request
			->expects($this->once())
			->method("getTargetRefId")
			->willReturn($target_ref_id);

		$ref_ids = [3,5,7];
		$tree
			->expects($this->once())
			->method("getSubTreeIds")
			->with($target_ref_id)
			->willReturn($ref_ids);

		$mapping = [ $target_ref_id => $source_ref_id, 3 => 1, 5 => 2, 7 => 3];
		$process
			->expects($this->once())
			->method("getCopyMappings")
			->with(array_merge([23], [3, 5, 7]))  // original obj_id is added
			->willReturn($mapping);

		$object = $this->createMock(_SpecialObject::class);
		$process
			->expects($this->exactly(4))
			->method("getObjectByRefId")
			->withConsecutive([23], [3], [5], [7])
			->willReturn($object);

		$c0 = new \stdClass();
		$c1 = new \stdClass();
		$c2 = new \stdClass();
		$c3 = new \stdClass();
		$c4 = new \stdClass();
		$request
			->expects($this->exactly(4))
			->method("getConfigurationFor")
			->withConsecutive([$source_ref_id], [1], [2], [3])
			->will($this->onConsecutiveCalls([$c0], [$c1], [$c2, $c3], [$c4]));

		$object
			->expects($this->exactly(5))
			->method("afterCourseCreation")
			->withConsecutive([$c0], [$c1], [$c2], [$c3], [$c4]);

		$process->_configureCopiedObjects($request);
	}

	public function test_don_t_configureCopiedObjects() {
		$tree = $this->createMock(\ilTree::class);
		$db = $this->createMock(\ilDBInterface::class);
		$request = $this->createMock(CourseCreation\Request::class);

		$process = $this->getMockBuilder(_CourseCreationProcess::class)
			->setMethods(["getCopyMappings", "getObjectByRefId"])
			->setConstructorArgs([$tree, $db])
			->getMock();

		$target_ref_id = 23;
		$source_ref_id = 42;
		$request
			->expects($this->once())
			->method("getTargetRefId")
			->willReturn($target_ref_id);

		$ref_ids = [3];
		$tree
			->expects($this->once())
			->method("getSubTreeIds")
			->with($target_ref_id)
			->willReturn($ref_ids);

		$mapping = [ $target_ref_id => $source_ref_id, 3 => 1];
		$process
			->expects($this->once())
			->method("getCopyMappings")
			->with([23, 3])  // original obj_id is added
			->willReturn($mapping);

		$process
			->expects($this->never())
			->method("getObjectByRefId");

		$request
			->expects($this->exactly(2))
			->method("getConfigurationFor")
			->withConsecutive([$source_ref_id], [1])
			->will($this->onConsecutiveCalls(null, null));

		$process->_configureCopiedObjects($request);
	}

	public function test_adjustCourseTitle() {
		$tree = $this->createMock(\ilTree::class);
		$db = $this->createMock(\ilDBInterface::class);
		$request = $this->createMock(CourseCreation\Request::class);

		$process = $this->getMockBuilder(_CourseCreationProcess::class)
			->setMethods(["getObjectByRefId"])
			->setConstructorArgs([$tree, $db])
			->getMock();

		$target_ref_id = 23;
		$request
			->expects($this->once())
			->method("getTargetRefId")
			->willReturn($target_ref_id);

		$object = $this->createMock(\ilObject::class);
		$process
			->expects($this->once())
			->method("getObjectByRefId")
			->with($target_ref_id)
			->willReturn($object);

		$title = "blablabla - foo - Kopie (2)";
		$object
			->expects($this->once())
			->method("getTitle")
			->willReturn($title);

		$object
			->expects($this->once())
			->method("setTitle")
			->with("blablabla - foo");

		$object
			->expects($this->once())
			->method("update");

		$process->_adjustCourseTitle($request);
	}

	public function test_setCourseOnline() {
		$tree = $this->createMock(\ilTree::class);
		$db = $this->createMock(\ilDBInterface::class);
		$request = $this->createMock(CourseCreation\Request::class);

		$process = $this->getMockBuilder(_CourseCreationProcess::class)
			->setMethods(["getObjectByRefId"])
			->setConstructorArgs([$tree, $db])
			->getMock();

		$target_ref_id = 23;
		$request
			->expects($this->once())
			->method("getTargetRefId")
			->willReturn($target_ref_id);

		$object = $this->createMock(_SpecialObject::class);
		$process
			->expects($this->once())
			->method("getObjectByRefId")
			->with($target_ref_id)
			->willReturn($object);

		$object
			->expects($this->once())
			->method("setOfflineStatus")
			->with(false);

		$object
			->expects($this->once())
			->method("update");

		$process->_setCourseOnline($request);
	}
}
