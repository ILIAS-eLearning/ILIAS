<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class CatStandardGUIRequestTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function tearDown() : void
    {
    }

    protected function getRequest(array $get, array $post) : \ILIAS\Category\StandardGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\Category\StandardGUIRequest(
            $http_mock,
            $refinery,
            $get,
            $post
        );
    }

    /**
     * Test ref id
     */
    public function testRefId()
    {
        $request = $this->getRequest(
            [
                "ref_id" => "5"
            ],
            []
        );

        $this->assertEquals(
            5,
            $request->getRefId()
        );
    }

    /**
     * Test no ref id
     */
    public function testNoRefId()
    {
        $request = $this->getRequest(
            [
            ],
            []
        );

        $this->assertEquals(
            0,
            $request->getRefId()
        );
    }

    /**
     * Test base class
     */
    public function testBaseClass()
    {
        $request = $this->getRequest(
            [
                "baseClass" => "myClass"
            ],
            []
        );

        $this->assertEquals(
            "myClass",
            $request->getBaseClass()
        );
    }

    /**
     * Test cmd class
     */
    public function testCmdClass()
    {
        $request = $this->getRequest(
            [
                "cmdClass" => "myClass"
            ],
            []
        );

        $this->assertEquals(
            "myClass",
            $request->getCmdClass()
        );
    }

    /**
     * Test term
     */
    public function testTerm()
    {
        $request = $this->getRequest(
            [
                "term" => "my_term"
            ],
            []
        );

        $this->assertEquals(
            "my_term",
            $request->getTerm()
        );
    }

    /**
     * Test term by post
     */
    public function testTermByPost()
    {
        $request = $this->getRequest(
            [
            ],
            [
                "term" => "my_term"
            ]
        );

        $this->assertEquals(
            "my_term",
            $request->getTerm()
        );
    }

    /**
     * Test that post values overwrite get values
     */
    public function testPostBeatsGet()
    {
        $request = $this->getRequest(
            [
                "term" => "one"
            ],
            [
                "term" => "two"
            ]
        );

        $this->assertEquals(
            "two",
            $request->getTerm()
        );
    }

    /**
     * Test fetch all
     */
    public function testFetchAll()
    {
        $request = $this->getRequest(
            [
                "fetchall" => "1"
            ],
            []
        );

        $this->assertEquals(
            1,
            $request->getFetchAll()
        );
    }

    /**
     * Test role ids
     */
    public function testRoleIds()
    {
        $request = $this->getRequest(
            [
            ],
            [
                "role_ids" => [
                    "6", "7", "9"
                ]
            ]
        );

        $this->assertEquals(
            [6,7,9],
            $request->getRoleIds()
        );
    }

    /**
     * Test user ids
     */
    public function testUserIds()
    {
        $request = $this->getRequest(
            [
            ],
            [
                "user_ids" => [
                    "6", "7", "10"
                ]
            ]
        );

        $this->assertEquals(
            [6,7,10],
            $request->getUserIds()
        );
    }

    /**
     * Test obj id
     */
    public function testObjId()
    {
        $request = $this->getRequest(
            [
                "obj_id" => "15"
            ],
            [
            ]
        );

        $this->assertEquals(
            15,
            $request->getObjId()
        );
    }
}
