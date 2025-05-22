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

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ContRefStandardGUIRequestTest extends TestCase
{
    protected function tearDown(): void
    {
    }

    protected function getRequest(array $get, array $post): \ILIAS\ContainerReference\StandardGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\ContainerReference\StandardGUIRequest(
            $http_mock,
            $refinery,
            $get,
            $post
        );
    }

    /**
     * Test ref id
     */
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

    /**
     * Test no ref id
     */
    public function testNoRefId(): void
    {
        $request = $this->getRequest(
            [
            ],
            []
        );

        $this->assertEquals(
            0,
            $request->getRefId()
        );
    }

    /**
     * Test target id
     */
    public function testTargetId(): void
    {
        $request = $this->getRequest(
            [
                "target_id" => "14"
            ],
            []
        );

        $this->assertEquals(
            14,
            $request->getTargetId()
        );
    }

    /**
     * Test new type
     */
    public function testNewType(): void
    {
        $request = $this->getRequest(
            [
                "new_type" => "cat"
            ],
            []
        );

        $this->assertEquals(
            "cat",
            $request->getNewType()
        );
    }

    /**
     * Test creation mode
     */
    public function testCreationMode(): void
    {
        $request = $this->getRequest(
            [
                "creation_mode" => "1"
            ],
            []
        );

        $this->assertEquals(
            1,
            $request->getCreationMode()
        );
    }
}
