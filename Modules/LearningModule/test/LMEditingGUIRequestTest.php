<?php

use PHPUnit\Framework\TestCase;

/**
 * Test learning module editing request
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class LMEditingGUIRequestTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function tearDown() : void
    {
    }

    protected function getRequest(array $get, array $post) : \ILIAS\LearningModule\Editing\EditingGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\LearningModule\Editing\EditingGUIRequest(
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

    public function testFirstChild()
    {
        $request = $this->getRequest(
            [
                "first_child" => "1"
            ],
            []
        );

        $this->assertEquals(
            true,
            $request->getFirstChild()
        );
    }

    public function testMulti()
    {
        $request = $this->getRequest(
            [
                "multi" => "1"
            ],
            []
        );

        $this->assertEquals(
            1,
            $request->getMulti()
        );
    }

    public function testNodeId()
    {
        $request = $this->getRequest(
            [
                "node_id" => "5"
            ],
            []
        );

        $this->assertEquals(
            5,
            $request->getNodeId()
        );
    }

    public function testTitles()
    {
        $request = $this->getRequest(
            [
                "title" => ["1" => "test", "2" => "titles"]
            ],
            []
        );

        $this->assertEquals(
            ["1" => "test", "2" => "titles"],
            $request->getTitles()
        );
    }

    public function testIds()
    {
        $request = $this->getRequest(
            [
                "id" => ["4", "6"]
            ],
            []
        );

        $this->assertEquals(
            [4, 6],
            $request->getIds()
        );
    }
}
