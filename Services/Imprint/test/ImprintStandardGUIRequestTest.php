<?php

use PHPUnit\Framework\TestCase;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ImprintStandardGUIRequestTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function tearDown() : void
    {
    }

    protected function getRequest(array $get, array $post) : \ILIAS\Imprint\StandardGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\Imprint\StandardGUIRequest(
            $http_mock,
            $refinery,
            $get,
            $post
        );
    }

    public function testBaseClass() : void
    {
        $request = $this->getRequest(
            [
                "baseClass" => "MyClass"
            ],
            []
        );

        $this->assertEquals(
            "MyClass",
            $request->getBaseClass()
        );
    }
}
