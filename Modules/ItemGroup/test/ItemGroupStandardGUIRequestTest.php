<?php

use PHPUnit\Framework\TestCase;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ItemGroupStandardGUIRequestTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function tearDown() : void
    {
    }

    protected function getRequest(array $get, array $post) : \ILIAS\ItemGroup\StandardGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\ItemGroup\StandardGUIRequest(
            $http_mock,
            $refinery,
            $get,
            $post
        );
    }

    public function testItems()
    {
        $request = $this->getRequest(
            [
            ],
            [
                "items" => ["3", "7"]
            ]
        );

        $this->assertEquals(
            [3,7],
            $request->getItems()
        );
    }
}
