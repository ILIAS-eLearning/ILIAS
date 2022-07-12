<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class MultilingualismStandardGUIRequestTest extends TestCase
{
    protected function tearDown() : void
    {
    }

    protected function getRequest(array $get, array $post) : \ILIAS\Multilingualism\StandardGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\Multilingualism\StandardGUIRequest(
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
            ],
            [
                "lang" => [
                    "en" => "English",
                    "de" => "German"
                ]
            ]
        );

        $this->assertEquals(
            [
                "en" => "English",
                "de" => "German"
            ],
            $request->getLanguages()
        );
    }
}
