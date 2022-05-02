<?php

use PHPUnit\Framework\TestCase;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class MediaCastStandardGUIRequestTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function tearDown() : void
    {
    }

    protected function getRequest(array $get, array $post) : \ILIAS\MediaCast\StandardGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\MediaCast\StandardGUIRequest(
            $http_mock,
            $refinery,
            $get,
            $post
        );
    }

    public function testRefId() : void
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

    public function testMimeTypes() : void
    {
        $request = $this->getRequest(
            [
                "mimetypes" => "a,b"
            ],
            []
        );

        $this->assertEquals(
            "a,b",
            $request->getMimeTypes()
        );
    }

    public function testItemIds() : void
    {
        $request = $this->getRequest(
            [
            ],
            [
                "item_id" => ["4", "7", "8"]
            ]
        );

        $this->assertEquals(
            [4,7,8],
            $request->getItemIds()
        );
    }

    public function testSeconds() : void
    {
        $request = $this->getRequest(
            [
                "sec" => "5"
            ],
            []
        );

        $this->assertEquals(
            5,
            $request->getSeconds()
        );
    }
}
