<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class AdveStandardGUIRequestTest extends TestCase
{
    protected function tearDown(): void
    {
    }

    protected function getRequest(array $get, array $post): \ILIAS\AdvancedEditing\StandardGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\AdvancedEditing\StandardGUIRequest(
            $http_mock,
            $refinery,
            $get,
            $post
        );
    }

    /**
     * Test group
     */
    public function testGroup(): void
    {
        $request = $this->getRequest(
            [
                "grp" => "5"
            ],
            []
        );

        $this->assertEquals(
            "5",
            $request->getGroup()
        );
    }
}
