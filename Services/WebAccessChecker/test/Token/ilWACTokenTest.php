<?php
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
 * @author                 Fabian Schmid <fs@studer-raimann.ch>
 * @version                1.0.0
 *
 * @runInSeparateProcess
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class ilWACTokenTest extends PHPUnit_Framework_TestCase {

	const ADDITIONAL_TIME = 0.5;
	const LIFETIME = 1;
	const SALT = 'SALT';
	const CLIENT_NAME = 'client_name';
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
	 * @var vfs\vfsStreamFile
	 */
	protected $file_four;
	/**
	 * @var vfs\vfsStreamDirectory
	 */
	protected $root;


	/**
	 * Setup
	 */
	protected function setUp() {
		require_once('./Services/WebAccessChecker/classes/class.ilWACSignedPath.php');
		require_once('./Services/WebAccessChecker/classes/class.ilWebAccessChecker.php');
		require_once('./Services/WebAccessChecker/classes/class.ilWACSignedPath.php');
		require_once('./Services/WebAccessChecker/classes/class.ilWACToken.php');
		require_once('./Services/WebAccessChecker/test/Token/mock/class.ilWACDummyCookie.php');
		require_once('./libs/composer/vendor/autoload.php');
		$this->root = vfs\vfsStream::setup('ilias.de');
		$this->file_one = vfs\vfsStream::newFile('data/client_name/mobs/mm_123/dummy.jpg')->at($this->root)->setContent('dummy');
		$this->file_two = vfs\vfsStream::newFile('data/client_name/mobs/mm_123/dummy2.jpg')->at($this->root)->setContent('dummy2');
		$this->file_three = vfs\vfsStream::newFile('data/client_name/mobs/mm_124/dummy.jpg')->at($this->root)->setContent('dummy');
		$this->file_four = vfs\vfsStream::newFile('data/client_name/sec/ilBlog/mm_124/dummy.jpg')->at($this->root)->setContent('dummy');
		ilWACToken::setSALT(self::SALT);
		parent::setUp();
	}


	public function testWithoutSigning() {
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($this->file_one->url()), new ilWACDummyCookie());

		$this->assertFalse($ilWACSignedPath->isSignedPath());
		$this->assertFalse($ilWACSignedPath->isSignedPathValid());
		$this->assertFalse($ilWACSignedPath->isFolderSigned());
		$this->assertFalse($ilWACSignedPath->isFolderTokenValid());
	}


	public function testSomeBasics() {
		$query = 'myparam=1234';
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($this->file_four->url() . '?' . $query), new ilWACDummyCookie());

		$this->assertEquals('dummy.jpg', $ilWACSignedPath->getPathObject()->getFileName());
		$this->assertEquals($query, $ilWACSignedPath->getPathObject()->getQuery());
		$this->assertEquals('./data/' . self::CLIENT_NAME . '/sec/ilBlog/', $ilWACSignedPath->getPathObject()->getSecurePath());
		$this->assertEquals('ilBlog', $ilWACSignedPath->getPathObject()->getSecurePathId());
		$this->assertFalse($ilWACSignedPath->getPathObject()->isStreamable());

		$this->assertEquals('ilWACLogDummy', get_class(ilWACLog::getInstance()));

		$this->assertFalse(ilWebAccessChecker::isDEBUG());
		$this->assertFalse(ilWACToken::DEBUG);
	}


	public function testTokenGeneration() {
		ilWebAccessChecker::setDEBUG(true);
		$ilWACToken = new ilWACToken($this->file_four->url(), self::CLIENT_NAME, 123456, 20);
		$ilWACToken->setIp('127.0.0.1');
		$ilWACToken->generateToken();
		$this->assertEquals('SALT-127.0.0.1-client_name-123456-20', $ilWACToken->getToken());
		$this->assertEquals('/data/client_name/sec/ilBlog/mm_124/dummy.jpg', $ilWACToken->getId());

		ilWebAccessChecker::setDEBUG(false);
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$this->assertEquals(self::SALT, ilWACToken::getSALT());
		$ilWACToken = new ilWACToken($this->file_four->url(), self::CLIENT_NAME, 123456, 20);
		$this->assertEquals('cd5a43304b232c785ef4f9796053b8bf5d6d829a', $ilWACToken->getToken());
		$this->assertEquals('3ebcc01c4d77508c685c55849e00cea6', $ilWACToken->getId());
	}


	public function testCookieGeneration() {
		ilWebAccessChecker::setDEBUG(true);
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$ilWACCookieInterface = new ilWACDummyCookie();
		ilWACSignedPath::signFolderOfStartFile($this->file_one->url(), $ilWACCookieInterface);
		$expected_cookies = array(
			'./data/client_name/mobs/mm_123',
			'./data/client_name/mobs/mm_123ts',
			'./data/client_name/mobs/mm_123ttl',
		);
		$this->assertEquals($expected_cookies, array_keys($ilWACCookieInterface->getAll()));

		ilWebAccessChecker::setDEBUG(false);
		ilWACDummyCookie::clear();
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$ilWACCookieInterface = new ilWACDummyCookie();
		ilWACSignedPath::signFolderOfStartFile($this->file_one->url(), $ilWACCookieInterface);
		$expected_cookies = array(
			'3a469ab7011fc500453d83dac14f3bfc',
			'3a469ab7011fc500453d83dac14f3bfcts',
			'3a469ab7011fc500453d83dac14f3bfcttl',
		);
		$this->assertEquals($expected_cookies, array_keys($ilWACCookieInterface->getAll()));
	}


	public function testFileToken() {
		ilWACSignedPath::setTokenMaxLifetimeInSeconds(self::LIFETIME);
		$lifetime = ilWACSignedPath::getTokenMaxLifetimeInSeconds();

		// Request within lifetime
		$signed_path = ilWACSignedPath::signFile($this->file_one->url());
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($signed_path));

		$this->assertTrue($ilWACSignedPath->isSignedPath());
		$this->assertTrue($ilWACSignedPath->isSignedPathValid());
		$this->assertEquals($ilWACSignedPath->getPathObject()->getClient(), self::CLIENT_NAME);
		$this->assertFalse($ilWACSignedPath->getPathObject()->isInSecFolder());
		$this->assertTrue($ilWACSignedPath->getPathObject()->isImage());
		$this->assertFalse($ilWACSignedPath->getPathObject()->isAudio());
		$this->assertFalse($ilWACSignedPath->getPathObject()->isVideo());
		$this->assertTrue($ilWACSignedPath->getPathObject()->hasTimestamp());
		$this->assertTrue($ilWACSignedPath->getPathObject()->hasToken());

		// Request after lifetime
		$signed_path = ilWACSignedPath::signFile($this->file_four->url());
		sleep($lifetime + self::ADDITIONAL_TIME);
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($signed_path));
		$this->assertTrue($ilWACSignedPath->isSignedPath());
		$this->assertFalse($ilWACSignedPath->isSignedPathValid());
	}


	public function testFolderToken() {
		ilWACDummyCookie::clear();
		ilWACSignedPath::setCookieMaxLifetimeInSeconds(self::LIFETIME);
		$lifetime = ilWACSignedPath::getCookieMaxLifetimeInSeconds();

		$signed_path = $this->file_one->url();
		ilWACSignedPath::signFolderOfStartFile($signed_path, new ilWACDummyCookie());

		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($signed_path), new ilWACDummyCookie());
		$this->assertTrue($ilWACSignedPath->isFolderSigned());
		$this->assertTrue($ilWACSignedPath->isFolderTokenValid());
		$this->assertEquals($ilWACSignedPath->getPathObject()->getClient(), self::CLIENT_NAME);
		$this->assertFalse($ilWACSignedPath->getPathObject()->isInSecFolder());
		$this->assertTrue($ilWACSignedPath->getPathObject()->isImage());
		$this->assertFalse($ilWACSignedPath->getPathObject()->isAudio());
		$this->assertFalse($ilWACSignedPath->getPathObject()->isVideo());
		$this->assertTrue($ilWACSignedPath->getPathObject()->hasTimestamp());
		$this->assertTrue($ilWACSignedPath->getPathObject()->hasToken());

		// Request after lifetime
		ilWACSignedPath::signFolderOfStartFile($signed_path, new ilWACDummyCookie());
		sleep($lifetime + self::ADDITIONAL_TIME);
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($signed_path), new ilWACDummyCookie());
		$this->assertTrue($ilWACSignedPath->isFolderSigned());
		$this->assertFalse($ilWACSignedPath->isFolderTokenValid());

		// Revalidating cookie
		$ilWACSignedPath->revalidatingFolderToken();
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($signed_path), new ilWACDummyCookie());
		$this->assertTrue($ilWACSignedPath->isFolderSigned());
		$this->assertTrue($ilWACSignedPath->isFolderTokenValid());

		// Check other file
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($this->file_three->url()), new ilWACDummyCookie());
		$this->assertFalse($ilWACSignedPath->isFolderSigned());
		$this->assertFalse($ilWACSignedPath->isFolderTokenValid());
	}


	public function testFolderTokenWithSecondFile() {
		ilWACSignedPath::setCookieMaxLifetimeInSeconds(self::LIFETIME);
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
		sleep($lifetime + self::ADDITIONAL_TIME);
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


	public function testModifiedTimestampNoMod() {
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($this->getModifiedSignedPath(0, 0)));
		$this->assertTrue($ilWACSignedPath->isSignedPath());
		$this->assertTrue($ilWACSignedPath->isSignedPathValid());
	}


	public function testModifiedTimestampAddTime() {
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($this->getModifiedSignedPath(self::ADDITIONAL_TIME, 0)));
		$this->assertTrue($ilWACSignedPath->isSignedPath());
		$this->assertFalse($ilWACSignedPath->isSignedPathValid());
	}


	public function testModifiedTimestampSubTime() {
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($this->getModifiedSignedPath(self::ADDITIONAL_TIME * - 1, 0)));
		$this->assertTrue($ilWACSignedPath->isSignedPath());
		$this->assertFalse($ilWACSignedPath->isSignedPathValid());
	}


	public function testModifiedTTL() {
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($this->getModifiedSignedPath(0, 1)));
		$this->assertTrue($ilWACSignedPath->isSignedPath());
		$this->assertFalse($ilWACSignedPath->isSignedPathValid());
	}


	public function testModifiedTTLAndTimestamp() {
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($this->getModifiedSignedPath(1, 1)));
		$this->assertTrue($ilWACSignedPath->isSignedPath());
		$this->assertFalse($ilWACSignedPath->isSignedPathValid());
	}


	public function testModifiedToken() {
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($this->getModifiedSignedPath(0, 0, md5('LOREM'))));
		$this->assertTrue($ilWACSignedPath->isSignedPath());
		$this->assertFalse($ilWACSignedPath->isSignedPathValid());
	}


	/**
	 * @param int $add_ttl
	 * @param int $add_timestamp
	 * @param null $override_token
	 * @return string
	 */
	protected function getModifiedSignedPath($add_ttl = 0, $add_timestamp = 0, $override_token = null) {
		ilWACSignedPath::setTokenMaxLifetimeInSeconds(self::LIFETIME);
		$signed_path = ilWACSignedPath::signFile($this->file_one->url());

		$parts = parse_url($signed_path);
		$path = $parts['path'];
		$query = $parts['query'];
		parse_str($query, $query_array);
		$token = $override_token ? $override_token : $query_array['il_wac_token'];
		$ttl = (int)$query_array['il_wac_ttl'];
		$ts = (int)$query_array['il_wac_ts'];
		$path_with_token = $path . '?il_wac_token=' . $token;

		$modified_ttl = $ttl + $add_ttl;
		$modified_ts = $ts + $add_timestamp;

		return $path_with_token . '&il_wac_ttl=' . $modified_ttl . '&il_wac_ts=' . $modified_ts;
	}
}