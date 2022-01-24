<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class GloPresentationGUIRequestTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function tearDown() : void
    {
    }

    protected function getRequest(array $get, array $post) : \ILIAS\Glossary\Presentation\PresentationGUIRequest
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

    public function testDefinitionId()
    {
        $request = $this->getRequest(
            [
                "def" => "7"
            ],
            []
        );

        $this->assertEquals(
            7,
            $request->getDefinitionId()
        );
    }

    public function testLetter()
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

    public function testTermId()
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

    public function test()
    {
        $request = $this->getRequest(
            [
                "type" => "xml"
            ],
            []
        );

        $this->assertEquals(
            "xml",
            $request->getExportType()
        );
    }
}
