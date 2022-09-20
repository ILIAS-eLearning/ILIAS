<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class AccordionStandardGUIRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
    }

    protected function getRequest(array $get, array $post): \ILIAS\Accordion\StandardGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\Accordion\StandardGUIRequest(
            $http_mock,
            $refinery,
            $get,
            $post
        );
    }

    public function testUserId(): void
    {
        $request = $this->getRequest(
            [
                "user_id" => "5"
            ],
            []
        );

        $this->assertEquals(
            5,
            $request->getUserId()
        );
    }


    public function testTabNr(): void
    {
        $request = $this->getRequest(
            [
                "tab_nr" => "7"
            ],
            []
        );

        $this->assertEquals(
            7,
            $request->getTabNr()
        );
    }
}
