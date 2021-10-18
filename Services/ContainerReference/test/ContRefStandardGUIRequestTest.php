<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ContRefStandardGUIRequestTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function tearDown() : void
    {
    }

    protected function getRequest(array $get, array $post) : \ILIAS\ContainerReference\StandardGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\ContainerReference\StandardGUIRequest(
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
     * Test target id
     */
    public function testTargetId()
    {
        $request = $this->getRequest(
            [
                "target_id" => "14"
            ],
            []
        );

        $this->assertEquals(
            14,
            $request->getTargetId()
        );
    }

    /**
     * Test new type
     */
    public function testNewType()
    {
        $request = $this->getRequest(
            [
                "new_type" => "cat"
            ],
            []
        );

        $this->assertEquals(
            "cat",
            $request->getNewType()
        );
    }

    /**
     * Test creation mode
     */
    public function testCreationMode()
    {
        $request = $this->getRequest(
            [
                "creation_mode" => "1"
            ],
            []
        );

        $this->assertEquals(
            1,
            $request->getCreationMode()
        );
    }
}
