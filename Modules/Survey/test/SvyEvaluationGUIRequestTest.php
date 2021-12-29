<?php

use PHPUnit\Framework\TestCase;

/**
 * Test evaluation request class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class SvyEvaluationGUIRequestTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function tearDown() : void
    {
    }

    protected function getRequest(array $get, array $post) : \ILIAS\Survey\Evaluation\EvaluationGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\Survey\Evaluation\EvaluationGUIRequest(
            $http_mock,
            $refinery,
            $get,
            $post
        );
    }

    public function testShowTable()
    {
        $request = $this->getRequest(
            [
                "vw" => "t"
            ],
            []
        );

        $this->assertEquals(
            true,
            $request->getShowTable()
        );
    }

    public function testShowChart()
    {
        $request = $this->getRequest(
            [
                "vw" => "c"
            ],
            []
        );

        $this->assertEquals(
            true,
            $request->getShowChart()
        );
    }

    public function testShowAbsolute()
    {
        $request = $this->getRequest(
            [
                "cp" => "a"
            ],
            []
        );

        $this->assertEquals(
            true,
            $request->getShowAbsolute()
        );
    }

    public function testShowPercentage()
    {
        $request = $this->getRequest(
            [
                "cp" => "p"
            ],
            []
        );

        $this->assertEquals(
            true,
            $request->getShowPercentage()
        );
    }

    public function testAppraiseeId()
    {
        $request = $this->getRequest(
            [
                "appr_id" => 14
            ],
            []
        );

        $this->assertEquals(
            14,
            $request->getAppraiseeId()
        );
    }

    public function testRaterId()
    {
        $request = $this->getRequest(
            [
                "rater_id" => "r12"
            ],
            []
        );

        $this->assertEquals(
            "r12",
            $request->getRaterId()
        );
    }

    public function testRefId()
    {
        $request = $this->getRequest(
            [
                "ref_id" => 101
            ],
            []
        );

        $this->assertEquals(
            101,
            $request->getRefId()
        );
    }

    public function testCompEvalMode()
    {
        $request = $this->getRequest(
            [
                "comp_eval_mode" => "evmode"
            ],
            []
        );

        $this->assertEquals(
            "evmode",
            $request->getCompEvalMode()
        );
    }

    public function testSurveyCode()
    {
        $request = $this->getRequest(
            [
                "surveycode" => "code"
            ],
            []
        );

        $this->assertEquals(
            "code",
            $request->getSurveyCode()
        );
    }

    public function testExportLabel()
    {
        $request = $this->getRequest(
            [
                "export_label" => "lab"
            ],
            []
        );

        $this->assertEquals(
            "lab",
            $request->getExportLabel()
        );
    }

    public function testExportFormat()
    {
        $request = $this->getRequest(
            [
            ],
            [
                "export_format" => "xml"
            ]
        );

        $this->assertEquals(
            "xml",
            $request->getExportFormat()
        );
    }
}
