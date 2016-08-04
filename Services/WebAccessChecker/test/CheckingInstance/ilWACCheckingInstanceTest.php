<?php
require_once('./libs/composer/vendor/autoload.php');

use org\bovigo\vfs;

/**
 * TestCase for the ilWACCheckingInstanceTest
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 *
 * @group   needsInstalledILIAS
 */
class ilWACCheckingInstanceTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var vfs\vfsStreamFile
	 */
	protected $file_one;
	/**
	 * @var vfs\vfsStreamDirectory
	 */
	protected $root;


	/**
	 * Setup
	 */
	protected function setUp() {
		error_reporting(E_ALL);
		require_once('./Services/WebAccessChecker/classes/class.ilWebAccessChecker.php');
		require_once('./Services/WebAccessChecker/classes/class.ilWACSignedPath.php');
		require_once('./Services/WebAccessChecker/classes/class.ilWACToken.php');
		require_once('./Services/WebAccessChecker/test/Token/mock/class.ilWACDummyCookie.php');
		require_once('./libs/composer/vendor/autoload.php');
		//				require_once('./Services/PHPUnit/classes/class.ilUnitUtil.php');
		//								ilUnitUtil::performInitialisation();
		//		define('IL_PHPUNIT_TEST', true);
		$this->root = vfs\vfsStream::setup('ilias.de');
		$this->file_one = vfs\vfsStream::newFile('data/trunk/mobs/mm_123/dummy.jpg')->at($this->root)->setContent('dummy');
		ilWACToken::setSALT('d48024096ba3abe92341ad7aaba45351');
		parent::setUp();
	}


	public function testBasic() {
		$ilWebAccessChecker = new ilWebAccessChecker($this->file_one->url(), new ilWACDummyCookie());
		$check = false;
		try {
			$check = $ilWebAccessChecker->check();
		} catch (ilWACException $ilWACException) {
			$this->assertEquals($ilWACException->getCode(), ilWACException::ACCESS_DENIED_NO_PUB);
		}
		$this->assertFalse($check);
		$this->assertEquals(array(
			$ilWebAccessChecker::CM_CHECKINGINSTANCE,
		), $ilWebAccessChecker->getAppliedCheckingMethods());
	}


	public function testBasicWithFileSigning() {
		$signed_path = ilWACSignedPath::signFile($this->file_one->url());
		$ilWebAccessChecker = new ilWebAccessChecker($signed_path, new ilWACDummyCookie());
		$check = false;
		try {
			$check = $ilWebAccessChecker->check();
		} catch (ilWACException $ilWACException) {
			$this->assertEquals($ilWACException->getCode(), ilWACException::ACCESS_DENIED_NO_PUB);
		}
		$this->assertTrue($check);
		$this->assertEquals(array(
			$ilWebAccessChecker::CM_FILE_TOKEN,
		), $ilWebAccessChecker->getAppliedCheckingMethods());
	}


	public function testBasicWithFolderSigning() {
		ilWACSignedPath::signFolderOfStartFile($this->file_one->url(), new ilWACDummyCookie());
		$ilWebAccessChecker = new ilWebAccessChecker($this->file_one->url(), new ilWACDummyCookie());
		$check = false;
		try {
			$check = $ilWebAccessChecker->check();
		} catch (ilWACException $ilWACException) {
			$this->assertEquals($ilWACException->getCode(), ilWACException::ACCESS_DENIED_NO_PUB);
		}
		$this->assertTrue($check);
		$this->assertEquals(array(
			$ilWebAccessChecker::CM_FOLDER_TOKEN,
		), $ilWebAccessChecker->getAppliedCheckingMethods());
	}


	public function testNonCheckingInstanceNoSec() {

		$file = vfs\vfsStream::newFile('data/trunk/dummy/mm_123/dummy.jpg')->at($this->root)->setContent('dummy');
		$ilWebAccessChecker = new ilWebAccessChecker($file->url(), new ilWACDummyCookie());
		$check = false;
		try {
			if (!defined('IL_PHPUNIT_TEST')) {
				define('IL_PHPUNIT_TEST', true);
			}
			session_id('phpunittest');
			$_SESSION = array();
			include 'Services/PHPUnit/config/cfg.phpunit.php';

			$check = $ilWebAccessChecker->check();
		} catch (ilWACException $ilWACException) {
			$this->assertEquals($ilWACException->getCode(), ilWACException::ACCESS_DENIED_NO_PUB);
		}
		//		$this->assertTrue($check); // Currently not able to init ILIAS in WAC during PHPUnit
		//		$this->assertEquals(array(
		//			$ilWebAccessChecker::CM_SECFOLDER,
		//		), $ilWebAccessChecker->getAppliedCheckingMethods());
	}
}