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
	 * Setup
	 */
	protected function setUp() {
		require_once('./Services/WebAccessChecker/classes/class.ilWACSignedPath.php');
		require_once('./libs/composer/vendor/autoload.php');
		vfs\vfsStream::setup();
		parent::setUp();
	}


	public function testFileToken() {
		ilWACSignedPath::setTokenMaxLifetimeInSeconds(2);
		$lifetime = ilWACSignedPath::getTokenMaxLifetimeInSeconds();
		vfs\vfsStream::newFile('data/client_name/mobs/mm_123/dummy.jpg')->setContent('dummy');

		// Request within lifetime
		$signed_path = ilWACSignedPath::signFile(vfs\vfsStream::url('data/client_name/mobs/mm_123/dummy.jpg'));
		sleep($lifetime - 1);
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($signed_path));
		$this->assertTrue($ilWACSignedPath->isSignedPath());
		$this->assertTrue($ilWACSignedPath->isSignedPathValid());

		// Request after lifetime
		$signed_path = ilWACSignedPath::signFile(vfs\vfsStream::url('data/client_name/mobs/mm_123/dummy.jpg'));
		sleep($lifetime + 1);
		$ilWACSignedPath = new ilWACSignedPath(new ilWACPath($signed_path));
		$this->assertTrue($ilWACSignedPath->isSignedPath());
		$this->assertFalse($ilWACSignedPath->isSignedPathValid());
	}
}