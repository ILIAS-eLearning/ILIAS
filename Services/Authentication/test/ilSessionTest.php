<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/


use PHPUnit\Framework\TestCase;

/**
 * Class ilSessionTest
 */
class ilSessionTest //extends TestCase
{
    protected function setUp() : void
    {
    }

    /**
     * @group IL_Init
     */
    public function testBasicSessionBehaviour() : void
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        $result = "";
        ilSession::_writeData("123456", "Testdata");
        if (ilSession::_exists("123456")) {
            $result .= "exists-";
        }
        if (ilSession::_getData("123456") === "Testdata") {
            $result .= "write-get-";
        }
        $duplicate = ilSession::_duplicate("123456");
        if (ilSession::_getData($duplicate) === "Testdata") {
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
        $this->assertEquals("exists-write-get-duplicate-destroy-destroyExp-destroyByUser-", $result);// TODO PHP8-REVIEW This method does not exists (because this class has no parent class)
    }

    /**
     * @group IL_Init
     */
    public function testPasswordAssisstanceSession() : void
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
                
        $result = "";
        
        // write session
        db_pwassist_session_write("12345", 60, $ilUser->getId());
        
        // find
        $res = db_pwassist_session_find($ilUser->getId());
        if ($res["pwassist_id"] === "12345") {
            $result .= "find-";
        }
        
        // read
        $res = db_pwassist_session_read("12345");
        if ((int) $res["user_id"] === $ilUser->getId()) {
            $result .= "read-";
        }
        
        // destroy
        db_pwassist_session_destroy("12345");
        $res = db_pwassist_session_read("12345");
        if (!$res) {
            $result .= "destroy-";
        }

        db_pwassist_session_gc();

        $this->assertEquals("find-read-destroy-", $result);// TODO PHP8-REVIEW This method does not exists (because this class has no parent class)
    }
}
