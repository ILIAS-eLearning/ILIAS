<?php
require_once('./libs/composer/vendor/autoload.php');

use org\bovigo\vfs;

/**
 * TestCase for the ilWACCheckingInstanceTest
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
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
		$this->root = vfs\vfsStream::setup('ilias.de');
		$this->file_one = vfs\vfsStream::newFile('data/client_name/mobs/mm_123/dummy.jpg')->at($this->root)->setContent('dummy');
		ilWACToken::setSALT('d48024096ba3abe92341ad7aaba45351');
		parent::setUp();
	}


	public function testWithoutSigning() {
	}
}