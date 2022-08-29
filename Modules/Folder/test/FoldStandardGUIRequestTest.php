<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class FoldStandardGUIRequestTest extends TestCase
{
    protected function tearDown(): void
    {
    }

    protected function getRequest(array $get, array $post): \ILIAS\Folder\StandardGUIRequest
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
    public function testRefId(): void
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
    public function testNoRefId(): void
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
    public function testBaseClass(): void
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
    public function testCmdClass(): void
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
    public function testUserId(): void
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
