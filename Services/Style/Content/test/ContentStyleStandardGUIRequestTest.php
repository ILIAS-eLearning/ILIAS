<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\Style\Content\StandardGUIRequest;
use ILIAS\Data\Factory;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ContentStyleStandardGUIRequestTest extends TestCase
{
    protected function tearDown() : void
    {
    }

    protected function getRequest(array $get, array $post) : StandardGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new StandardGUIRequest(
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


    public function testTemplateId() : void
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

    public function testCharacteristics() : void
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
