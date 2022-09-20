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
 * @author Alexander Killing <killing@leifos.de>
 */
class BookManStandardGUIRequestTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function tearDown(): void
    {
    }

    protected function getRequest(array $get, array $post): \ILIAS\BookingManager\StandardGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\BookingManager\StandardGUIRequest(
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

    public function testPoolRefId(): void
    {
        $request = $this->getRequest(
            [
                "pool_ref_id" => "6"
            ],
            []
        );

        $this->assertEquals(
            6,
            $request->getPoolRefId()
        );
    }

    public function testReservationIds(): void
    {
        $request = $this->getRequest(
            [
            ],
            [
                "reservation_id" => ["4", "6", "8"]
            ]
        );

        $this->assertEquals(
            [4, 6, 8],
            $request->getReservationIds()
        );
    }
}
