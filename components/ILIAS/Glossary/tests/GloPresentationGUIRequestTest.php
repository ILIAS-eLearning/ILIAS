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
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class GloPresentationGUIRequestTest extends TestCase
{
    protected function getRequest(array $get, array $post): \ILIAS\Glossary\Presentation\PresentationGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\Glossary\Presentation\PresentationGUIRequest(
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

    public function testLetter(): void
    {
        $request = $this->getRequest(
            [
                "letter" => "a"
            ],
            []
        );

        $this->assertEquals(
            "a",
            $request->getLetter()
        );
    }

    public function testTermId(): void
    {
        $request = $this->getRequest(
            [
                "term_id" => "14"
            ],
            []
        );

        $this->assertEquals(
            14,
            $request->getTermId()
        );
    }
}
