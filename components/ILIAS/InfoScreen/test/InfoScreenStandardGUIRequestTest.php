<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\InfoScreen\StandardGUIRequest;
use ILIAS\Data\Factory;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class InfoScreenStandardGUIRequestTest extends TestCase
{
    protected function tearDown(): void
    {
    }

    protected function getRequest(array $get, array $post): StandardGUIRequest
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

    /**
     * Test user id
     */
    public function testUserId()
    {
        $request = $this->getRequest(
            [
                "user_id" => "57"
            ],
            []
        );

        $this->assertEquals(
            57,
            $request->getUserId()
        );
    }

    /**
     * Test lp edit
     */
    public function testLPEdit()
    {
        $request = $this->getRequest(
            [
            ],
            [
                "lp_edit" => "1"
            ]
        );

        $this->assertEquals(
            1,
            $request->getLPEdit()
        );
    }
}
