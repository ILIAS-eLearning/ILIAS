<?php

use PHPUnit\Framework\TestCase;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class LearningHistoryStandardGUIRequestTest extends TestCase
{
    protected function tearDown(): void
    {
    }

    protected function getRequest(array $get, array $post): \ILIAS\LearningHistory\StandardGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\LearningHistory\StandardGUIRequest(
            $http_mock,
            $refinery,
            $get,
            $post
        );
    }

    public function testItems(): void
    {
        $request = $this->getRequest(
            [
                "to_ts" => "123"
            ],
            [
            ]
        );

        $this->assertEquals(
            123,
            $request->getToTS()
        );
    }
}
