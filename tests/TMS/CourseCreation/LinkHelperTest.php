<?php

if (!class_exists(\ilCourseCreationPlugin::class)) {
	require_once("tests/TMS/CourseCreation/ilCourseCreationPlugin.php");
}

use ILIAS\TMS\CourseCreation\LinkHelper;
use ILIAS\TMS\CourseCreation\Request;
use PHPUnit\Framework\TestCase;

class LinkHelperMock {
	use  LinkHelper;

	protected function getCtrl() {
	}

	protected function getLng() {
	}

	protected function getUser() {
	}

	protected function sendInfo() {
	}

	public function _maybeShowRequestInfo(\ilCourseCreationPlugin $xccr_plugin = null, $waiting_time = 30000) {
		return $this->maybeShowRequestInfo($xccr_plugin, $waiting_time);
	}

	public function _getUsersDueRequests($user, \ilCourseCreationPlugin $plugin = null) {
		return $this->getUsersDueRequests($user, $plugin);
	}

	public function _getTrainingTitleByRequest(\ILIAS\TMS\CourseCreation\Request $request) {
		return $this->getTrainingTitleByRequest($request);
	}
}


/**
 * @group needsInstalledILIAS
 */
class LinkHelperTest extends TestCase {
	public function test_user_has_no_open_request() {
		$usr = $this->getMockBuilder("ilObjUser")
			->disableOriginalConstructor()
			->getMock();

		$link_helper = $this->getMockBuilder(LinkHelperMock::class)
			->setMethods(array("getUsersDueRequests", "getUser", "sendInfo"))
			->getMock();

		$link_helper->expects($this->never())
			->method("sendInfo");

		$link_helper->expects($this->once())
			->method("getUsersDueRequests")
			->will($this->returnValue(array()));

		$link_helper->expects($this->once())
			->method("getUser")
			->will($this->returnValue($usr));

		$this->assertFalse($link_helper->_maybeShowRequestInfo());
	}

	public function test_user_has_open_requests() {
		$txt_message = "This is the user info";

		$usr = $this->getMockBuilder("ilObjUser")
			->disableOriginalConstructor()
			->getMock();

		$link_helper = $this->getMockBuilder(LinkHelperMock::class)
			->setMethods(
				array(
					"getUsersDueRequests"
					, "getUser"
					, "sendInfo"
					, "getMessage"
				)
			)
			->getMock();

		$request = $this->getMockBuilder(\ILIAS\TMS\CourseCreation\Request::class)
			->disableOriginalConstructor()
			->getMock();

		$link_helper->expects($this->once())
			->method("getUsersDueRequests")
			->will($this->returnValue(array($request)));

		$link_helper->expects($this->once())
			->method("getMessage")
			->will($this->returnValue($txt_message));

		$link_helper->expects($this->once())
			->method("sendInfo")
			->with($this->equalTo($txt_message));

		$link_helper->expects($this->once())
			->method("getUser")
			->will($this->returnValue($usr));

		$this->assertTrue($link_helper->_maybeShowRequestInfo());
	}

	public function test_user_has_open_cached_requests() {
		$usr = $this->getMockBuilder("ilObjUser")
			->disableOriginalConstructor()
			->getMock();

		$link_helper = $this->getMockBuilder(LinkHelperMock::class)
			->setMethods(
				array(
					"getCachedRequests"
					, "setCachedRequests"
				)
			)
			->getMock();

		$request = $this->getMockBuilder(\ILIAS\TMS\CourseCreation\Request::class)
			->disableOriginalConstructor()
			->getMock();

		$link_helper->expects($this->once())
			->method("getCachedRequests")
			->will($this->returnValue(array($request)));

		$link_helper->expects($this->never())
			->method("setCachedRequests");

		$this->assertEquals(array($request), $link_helper->_getUsersDueRequests($usr));
	}

	public function test_user_has_no_cached_request_and_no_plugin() {
		$usr = $this->getMockBuilder("ilObjUser")
			->setMethods(array("getId"))
			->disableOriginalConstructor()
			->getMock();

		$usr->expects($this->once())
			->method("getId")
			->will($this->returnValue(10));

		$link_helper = $this->getMockBuilder(LinkHelperMock::class)
			->setMethods(
				array(
					"getCachedRequests"
					, "setCachedRequests"
				)
			)
			->getMock();

		$request = $this->getMockBuilder(\ILIAS\TMS\CourseCreation\Request::class)
			->disableOriginalConstructor()
			->getMock();

		$link_helper->expects($this->once())
			->method("getCachedRequests")
			->with($this->equalTo(10))
			->will($this->returnValue(null));

		$link_helper->expects($this->never())
			->method("setCachedRequests");

		$this->assertEquals(array(), $link_helper->_getUsersDueRequests($usr));
	}

	public function test_set_request_from_plugin_object() {
		$usr = $this->getMockBuilder("ilObjUser")
			->setMethods(array("getId"))
			->disableOriginalConstructor()
			->getMock();

		$xccr_plugin = $this->getMockBuilder("ilCourseCreationPlugin")
			->disableOriginalConstructor()
			->setMethods(array("getActions"))
			->getMock();

		$xccr_actions = $this->getMockBuilder("CourseCreationActions")
			->disableOriginalConstructor()
			->setMethods(array("getDueRequestsOf"))
			->getMock();

		$link_helper = $this->getMockBuilder(LinkHelperMock::class)
			->setMethods(
				array(
					"getCachedRequests"
					, "setCachedRequests"
				)
			)
			->getMock();

		$link_helper->expects($this->exactly(2))
			->method("getCachedRequests")
			->with($this->equalTo(10))
			->will($this->onConsecutiveCalls(null, array()));

		$link_helper->expects($this->once())
			->method("setCachedRequests")
			->with($this->equalTo(10), $this->equalTo(array()));

		$usr->expects($this->exactly(3))
			->method("getId")
			->will($this->returnValue(10));

		$xccr_actions->expects($this->once())
			->method("getDueRequestsOf")
			->will($this->returnValue(array()));

		$xccr_plugin->expects($this->once())
			->method("getActions")
			->will($this->returnValue($xccr_actions));

		$this->assertEquals(array(), $link_helper->_getUsersDueRequests($usr, $xccr_plugin));
	}
}