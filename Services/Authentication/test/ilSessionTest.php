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
 * Class ilSessionTest
 * @group needsInstalledILIAS
 */
class ilSessionTest extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;

    protected function setUp()
    {
        include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        ilUnitUtil::performInitialisation();
    }

    /**
     * @group IL_Init
     */
    public function testBasicSessionBehaviour()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        include_once("./Services/Authentication/classes/class.ilSession.php");
        $result = "";
        ilSession::_writeData("123456", "Testdata");
        if (ilSession::_exists("123456")) {
            $result .= "exists-";
        }
        if (ilSession::_getData("123456") == "Testdata") {
            $result .= "write-get-";
        }
        $duplicate = ilSession::_duplicate("123456");
        if (ilSession::_getData($duplicate) == "Testdata") {
            $result .= "duplicate-";
        }
        ilSession::_destroy("123456");
        if (!ilSession::_exists("123456")) {
            $result .= "destroy-";
        }
        ilSession::_destroyExpiredSessions();
        if (ilSession::_exists($duplicate)) {
            $result .= "destroyExp-";
        }
        
        ilSession::_destroyByUserId($ilUser->getId());
        if (!ilSession::_exists($duplicate)) {
            $result .= "destroyByUser-";
        }
        $this->assertEquals("exists-write-get-duplicate-destroy-destroyExp-destroyByUser-", $result);
    }

    /**
     * @group IL_Init
     */
    public function testPasswordAssisstanceSession()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        include_once("./include/inc.pwassist_session_handler.php");
        
        $result = "";
        
        // write session
        db_pwassist_session_write("12345", 60, $ilUser->getId());
        
        // find
        $res = db_pwassist_session_find($ilUser->getId());
        if ($res["pwassist_id"] == "12345") {
            $result .= "find-";
        }
        
        // read
        $res = db_pwassist_session_read("12345");
        if ($res["user_id"] == $ilUser->getId()) {
            $result .= "read-";
        }
        
        // destroy
        db_pwassist_session_destroy("12345");
        $res = db_pwassist_session_read("12345");
        if (!$res) {
            $result .= "destroy-";
        }

        db_pwassist_session_gc();

        $this->assertEquals("find-read-destroy-", $result);
    }
}
