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
class LikeStandardGUIRequestTest extends TestCase
{
    protected function tearDown(): void
    {
    }

    protected function getRequest(array $get, array $post): \ILIAS\Like\StandardGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\Like\StandardGUIRequest(
            $http_mock,
            $refinery,
            $get,
            $post
        );
    }

    public function testValue(): void
    {
        $request = $this->getRequest(
            [
                "val" => "5"
            ],
            []
        );

        $this->assertEquals(
            5,
            $request->getValue()
        );
    }

    public function testExpressionKey(): void
    {
        $request = $this->getRequest(
            [
                "exp" => "2"
            ],
            []
        );

        $this->assertEquals(
            2,
            $request->getExpressionKey()
        );
    }

    public function testModalSignalId(): void
    {
        $request = $this->getRequest(
            [
                "modal_show_sig_id" => "yxc12"
            ],
            []
        );

        $this->assertEquals(
            "yxc12",
            $request->getModalSignalId()
        );
    }
}
