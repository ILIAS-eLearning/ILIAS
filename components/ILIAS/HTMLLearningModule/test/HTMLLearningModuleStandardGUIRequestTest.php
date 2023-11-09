<?php

use PHPUnit\Framework\TestCase;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class HTMLLearningModuleStandardGUIRequestTest extends TestCase
{
    protected function tearDown(): void
    {
    }

    protected function getRequest(array $get, array $post): \ILIAS\HTMLLearningModule\StandardGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\HTMLLearningModule\StandardGUIRequest(
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
                "ref_id" => "66"
            ],
            [
            ]
        );

        $this->assertEquals(
            66,
            $request->getRefId()
        );
    }

    public function testUserId(): void
    {
        $request = $this->getRequest(
            [
                "user_id" => "4"
            ],
            [
            ]
        );

        $this->assertEquals(
            4,
            $request->getUserId()
        );
    }
}
