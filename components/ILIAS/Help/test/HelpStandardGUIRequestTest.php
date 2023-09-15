<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class HelpStandardGUIRequestTest extends TestCase
{
    protected function tearDown(): void
    {
    }

    protected function getRequest(array $get, array $post): \ILIAS\Help\StandardGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\Help\StandardGUIRequest(
            $http_mock,
            $refinery,
            $get,
            $post
        );
    }

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

    public function testHelpModuleId(): void
    {
        $request = $this->getRequest(
            [
                "hm_id" => "7"
            ],
            []
        );

        $this->assertEquals(
            7,
            $request->getHelpModuleId()
        );
    }

    public function testTerm(): void
    {
        $request = $this->getRequest(
            [
                "term" => "test"
            ],
            []
        );

        $this->assertEquals(
            "test",
            $request->getTerm()
        );
    }

    public function testIds(): void
    {
        $request = $this->getRequest(
            [
            ],
            [
                "id" => [4, 6, 7]
            ]
        );

        $this->assertEquals(
            [4, 6, 7],
            $request->getIds()
        );
    }

    public function testHelpScreenId(): void
    {
        $request = $this->getRequest(
            [
                "help_screen_id" => "foo"
            ],
            [

            ]
        );

        $this->assertEquals(
            "foo",
            $request->getHelpScreenId()
        );
    }
}
