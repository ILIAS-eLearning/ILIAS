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
 * Test editing request class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class SplEditingGUIRequestTest extends TestCase
{
    protected function tearDown(): void
    {
    }

    protected function getRequest(array $get, array $post): \ILIAS\SurveyQuestionPool\Editing\EditingGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\SurveyQuestionPool\Editing\EditingGUIRequest(
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
                "ref_id" => 102
            ],
            []
        );

        $this->assertEquals(
            102,
            $request->getRefId()
        );
    }

    public function testQuestionId(): void
    {
        $request = $this->getRequest(
            [
                "q_id" => 33
            ],
            []
        );

        $this->assertEquals(
            33,
            $request->getQuestionId()
        );
    }

    public function testQuestionIds(): void
    {
        $request = $this->getRequest(
            [

            ],
            [
                "q_id" => ["13", "15", "19"]
            ]
        );

        $this->assertEquals(
            [13, 15, 19],
            $request->getQuestionIds()
        );
    }

    public function testPreview(): void
    {
        $request = $this->getRequest(
            [
                "preview" => 1
            ],
            []
        );

        $this->assertEquals(
            1,
            $request->getPreview()
        );
    }

    public function testSelectedQuestionTypes(): void
    {
        $request = $this->getRequest(
            [
                "sel_question_types" => "Metric"
            ],
            []
        );

        $this->assertEquals(
            "Metric",
            $request->getSelectedQuestionTypes()
        );
    }

    public function testSort(): void
    {
        $request = $this->getRequest(
            [

            ],
            [
                "sort" => [
                    "a" => "a1",
                    "b" => "b1",
                    "c" => "c1",
                ]
            ]
        );

        $this->assertEquals(
            [
                "a" => "a1",
                "b" => "b1",
                "c" => "c1",
            ],
            $request->getSort()
        );
    }

}
