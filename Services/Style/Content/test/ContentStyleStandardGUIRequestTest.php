<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ContentStyleStandardGUIRequestTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function tearDown() : void
    {
    }

    protected function getRequest(array $get, array $post) : \ILIAS\Style\Content\StandardGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\Style\Content\StandardGUIRequest(
            $http_mock,
            $refinery,
            $get,
            $post
        );
    }

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


    public function testTemplateId()
    {
        $request = $this->getRequest(
            [
                "t_id" => "7"
            ],
            []
        );

        $this->assertEquals(
            7,
            $request->getTemplateId()
        );
    }

    public function testCharacteristics()
    {
        $request = $this->getRequest(
            [
            ],
            [
                "char" => [
                    "Foo",
                    "Bar"
                ]
            ]
        );

        $this->assertEquals(
            ["Foo", "Bar"],
            $request->getCharacteristics()
        );
    }
}
