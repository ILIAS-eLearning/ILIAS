<?php
require_once('./Services/WebAccessChecker/classes/class.ilWACSignedPath.php');
require_once('./libs/composer/vendor/autoload.php');
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/
use org\bovigo\vfs;

/**
 * TestCase for the ilWACTokenTest
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilWACTokenTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var bool
	 */
	protected $backupGlobals = false;
	/**
	 * @var vfs\vfsStreamFile
	 */
	protected $file_one;
	/**
	 * @var vfs\vfsStreamFile
	 */
	protected $file_two;
	/**
	 * @var vfs\vfsStreamFile
	 */
	protected $file_three;
	/**
	 * @var vfs\vfsStreamDirectory
	 */
	protected $root;


	/**
	 * Setup
	 */
	protected function setUp() {
		require_once('./Services/WebAccessChecker/classes/class.ilWebAccessChecker.php');
		require_once('./Services/WebAccessChecker/classes/class.ilWACSignedPath.php');
		require_once('./Services/WebAccessChecker/classes/class.ilWACToken.php');
		require_once('./Services/WebAccessChecker/test/mock/class.ilWACDummyCookie.php');
		require_once('./libs/composer/vendor/autoload.php');
		$this->root = vfs\vfsStream::setup('ilias.de');
		$this->file_one = vfs\vfsStream::newFile('data/client_name/mobs/mm_123/dummy.jpg')->at($this->root)->setContent('dummy');
		$this->file_two = vfs\vfsStream::newFile('data/client_name/mobs/mm_123/dummy2.jpg')->at($this->root)->setContent('dummy2');
		$this->file_three = vfs\vfsStream::newFile('data/client_name/mobs/mm_124/dummy.jpg')->at($this->root)->setContent('dummy');
		ilWACToken::setSALT('d48024096ba3abe92341ad7aaba45351');
		parent::setUp();
	}


	public function testWithoutSigning() {
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($this->file_one->url()), new ilWACDummyCookie());

		$this->assertFalse($ilWACSignedPath->isSignedPath());
		$this->assertFalse($ilWACSignedPath->isSignedPathValid());
		$this->assertFalse($ilWACSignedPath->isFolderSigned());
		$this->assertFalse($ilWACSignedPath->isFolderTokenValid());
	}


	public function testFileToken() {
		ilWACSignedPath::setTokenMaxLifetimeInSeconds(1);
		$lifetime = ilWACSignedPath::getTokenMaxLifetimeInSeconds();

		// Request within lifetime
		$signed_path = ilWACSignedPath::signFile($this->file_one->url());
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($signed_path));

		$this->assertTrue($ilWACSignedPath->isSignedPath());
		$this->assertTrue($ilWACSignedPath->isSignedPathValid());
		$this->assertEquals($ilWACSignedPath->getPathObject()->getClient(), 'client_name');
		$this->assertFalse($ilWACSignedPath->getPathObject()->isInSecFolder());
		$this->assertTrue($ilWACSignedPath->getPathObject()->isImage());
		$this->assertFalse($ilWACSignedPath->getPathObject()->isAudio());
		$this->assertFalse($ilWACSignedPath->getPathObject()->isVideo());
		$this->assertTrue($ilWACSignedPath->getPathObject()->hasTimestamp());
		$this->assertTrue($ilWACSignedPath->getPathObject()->hasToken());

		// Request after lifetime
		$signed_path = ilWACSignedPath::signFile($this->file_one->url());
		sleep($lifetime + 0.5);
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($signed_path));
		$this->assertTrue($ilWACSignedPath->isSignedPath());
		$this->assertFalse($ilWACSignedPath->isSignedPathValid());
	}


	public function testFolderToken() {
		ilWACDummyCookie::clear();
		ilWACSignedPath::setCookieMaxLifetimeInSeconds(3);
		$lifetime = ilWACSignedPath::getCookieMaxLifetimeInSeconds();

		$signed_path = $this->file_one->url();
		ilWACSignedPath::signFolderOfStartFile($signed_path, new ilWACDummyCookie());

		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($signed_path), new ilWACDummyCookie());
		$this->assertTrue($ilWACSignedPath->isFolderSigned());
		$this->assertTrue($ilWACSignedPath->isFolderTokenValid());
		$this->assertEquals($ilWACSignedPath->getPathObject()->getClient(), 'client_name');
		$this->assertFalse($ilWACSignedPath->getPathObject()->isInSecFolder());
		$this->assertTrue($ilWACSignedPath->getPathObject()->isImage());
		$this->assertFalse($ilWACSignedPath->getPathObject()->isAudio());
		$this->assertFalse($ilWACSignedPath->getPathObject()->isVideo());
		$this->assertTrue($ilWACSignedPath->getPathObject()->hasTimestamp());
		$this->assertTrue($ilWACSignedPath->getPathObject()->hasToken());

		// Request after lifetime
		ilWACSignedPath::signFolderOfStartFile($signed_path, new ilWACDummyCookie());
		sleep($lifetime + 1);
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($signed_path), new ilWACDummyCookie());
		$this->assertTrue($ilWACSignedPath->isFolderSigned());
		$this->assertFalse($ilWACSignedPath->isFolderTokenValid());

		// Revalidating cookie
		$ilWACSignedPath->revalidatingFolderToken();
		$this->assertTrue($ilWACSignedPath->isFolderSigned());
		$this->assertTrue($ilWACSignedPath->isFolderTokenValid());

		// Check other file
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($this->file_three->url()), new ilWACDummyCookie());
		$this->assertFalse($ilWACSignedPath->isFolderSigned());
		$this->assertFalse($ilWACSignedPath->isFolderTokenValid());
	}


	public function testFolderTokenWithSecondFile() {
		ilWACSignedPath::setCookieMaxLifetimeInSeconds(3);
		$lifetime = ilWACSignedPath::getCookieMaxLifetimeInSeconds();
		// Sign File One
		ilWACSignedPath::signFolderOfStartFile($this->file_one->url(), new ilWACDummyCookie());
		// Check File Two
		$file_two = $this->file_two->url();
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($file_two), new ilWACDummyCookie());
		$this->assertTrue($ilWACSignedPath->isFolderSigned());
		$this->assertTrue($ilWACSignedPath->isFolderTokenValid());

		// Request after lifetime
		ilWACSignedPath::signFolderOfStartFile($file_two, new ilWACDummyCookie());
		sleep($lifetime + 1);
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($file_two), new ilWACDummyCookie());
		$this->assertTrue($ilWACSignedPath->isFolderSigned());
		$this->assertFalse($ilWACSignedPath->isFolderTokenValid());

		// Revalidating cookie
		$ilWACSignedPath->revalidatingFolderToken();
		$this->assertTrue($ilWACSignedPath->isFolderSigned());
		$this->assertTrue($ilWACSignedPath->isFolderTokenValid());

		// Check other file
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($this->file_three->url()), new ilWACDummyCookie());
		$this->assertFalse($ilWACSignedPath->isFolderSigned());
		$this->assertFalse($ilWACSignedPath->isFolderTokenValid());
	}
}