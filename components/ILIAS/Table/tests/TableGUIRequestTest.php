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
 * Test evaluation request class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class TableGUIRequestTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
    }

    protected function getRequest(array $get, array $post): \ILIAS\Table\TableGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\Table\TableGUIRequest(
            $http_mock,
            $refinery,
            $get,
            $post
        );
    }

    public function testTableId(): void
    {
        $request = $this->getRequest(
            [
                "table_id" => "tid"
            ],
            []
        );

        $this->assertEquals(
            "tid",
            $request->getTableId()
        );
    }

    public function testRows(): void
    {
        $request = $this->getRequest(
            [
                "id_trows" => "22"
            ],
            []
        );

        $this->assertEquals(
            22,
            $request->getRows("id")
        );
    }
}
