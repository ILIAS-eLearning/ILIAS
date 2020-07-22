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
* @group needsInstalledILIAS
* @ingroup ServicesTree
*/
class ilCacheTest extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;

    protected function setUp()
    {
        include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        ilUnitUtil::performInitialisation();
    }

    /**
     * Cache tests
     * @group IL_Init
     */
    public function testCache()
    {
        include_once './Services/Cache/classes/class.ilExampleCache.php';
        
        $cache = new ilExampleCache();
        $get = $cache->getEntry("test_id");
        $stat = $cache->getLastAccessStatus();
        $value = $stat . "-" . $get . "-";
        
        $get = $cache->storeEntry("test_id", "test_value");
        $get = $cache->getEntry("test_id");
        $stat = $cache->getLastAccessStatus();
        $value .= $stat . "-" . $get . "-";
        
        sleep(6);

        $get = $cache->getEntry("test_id");
        $stat = $cache->getLastAccessStatus();
        $value .= $stat . "-" . $get . "-";
        
        $this->assertEquals("miss--hit-test_value-miss--", $value);
    }
}
