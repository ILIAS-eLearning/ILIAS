<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class FoldStandardGUIRequestTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function tearDown() : void
    {
    }

    protected function getRequest(array $get, array $post) : \ILIAS\Folder\StandardGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\Folder\StandardGUIRequest(
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
     * Test user id
     */
    public function testUserId()
    {
        $request = $this->getRequest(
            [
                "user_id" => "4"
            ],
            []
        );

        $this->assertEquals(
            4,
            $request->getUserId()
        );
    }
}
