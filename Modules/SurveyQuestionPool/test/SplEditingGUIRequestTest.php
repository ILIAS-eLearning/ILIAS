<?php

use PHPUnit\Framework\TestCase;

/**
 * Test editing request class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class SplEditingGUIRequestTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function tearDown() : void
    {
    }

    protected function getRequest(array $get, array $post) : \ILIAS\SurveyQuestionPool\Editing\EditingGUIRequest
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

    public function testRefId()
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

    public function testQuestionId()
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

    public function testQuestionIds()
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

    public function testPreview()
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

    public function testSelectedQuestionTypes()
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

    public function testSort()
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

    public function testPhraseId()
    {
        $request = $this->getRequest(
            [
                "p_id" => 55
            ],
            []
        );

        $this->assertEquals(
            55,
            $request->getPhraseId()
        );
    }

    public function testPhraseIds()
    {
        $request = $this->getRequest(
            [

            ],
            [
                "phrase" => ["13", "15", "19"]
            ]
        );

        $this->assertEquals(
            [13, 15, 19],
            $request->getPhraseIds()
        );
    }
}
