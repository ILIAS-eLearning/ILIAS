<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use PHPUnit\Framework\TestCase;

/**
 * Test learning module editing request
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class LMEditingGUIRequestTest extends TestCase
{
    protected function tearDown(): void
    {
    }

    protected function getRequest(array $get, array $post): \ILIAS\LearningModule\Editing\EditingGUIRequest
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

    public function testFirstChild(): void
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

    public function testMulti(): void
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

    public function testNodeId(): void
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

    public function testTitles(): void
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

    public function testIds(): void
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
