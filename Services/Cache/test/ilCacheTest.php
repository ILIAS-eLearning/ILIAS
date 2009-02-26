<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

/** 
* Unit tests for data cache
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesTree
*/
class ilCacheTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	protected function setUp()
	{
		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
	}
	
	/**
	 * Cache tests
	 * @param
	 * @return
	 */
	public function testCache()
	{
		include_once './Services/Cache/classes/class.ilCache.php';
		
		$module = md5(time());
		$cache = new ilCache($module);
		$cache->setValue(1,2);
		
		$val = $cache->getValue(1);
		$this->assertEquals($val,2);
		
		$val = $cache->getValueForModule($module,1);
		$this->assertEquals($val,2);

		$cache->deleteValue(1);
		$val = $cache->getValue(1);
		$this->assertEquals($val,null);

		$cache->setValue(1,2);
		$cache->deleteAll($module);
		$val = $cache->getValue(1);
		$this->assertEquals($val,null);
	}
}
?>
