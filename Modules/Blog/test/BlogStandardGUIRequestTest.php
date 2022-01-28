<?php

use PHPUnit\Framework\TestCase;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class BlogStandardGUIRequestTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function tearDown() : void
    {
    }

    protected function getRequest(array $get, array $post) : \ILIAS\Blog\StandardGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\Blog\StandardGUIRequest(
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

    public function testBlogPage()
    {
        $request = $this->getRequest(
            [
                "blpg" => "6"
            ],
            []
        );

        $this->assertEquals(
            6,
            $request->getBlogPage()
        );
    }

    public function testObjIds()
    {
        $request = $this->getRequest(
            [
            ],
            [
                "obj_id" => ["3", "7"]
            ]
        );

        $this->assertEquals(
            [3,7],
            $request->getObjIds()
        );
    }

    public function testIds()
    {
        $request = $this->getRequest(
            [
            ],
            [
                "id" => ["12", "17"]
            ]
        );

        $this->assertEquals(
            [12,17],
            $request->getIds()
        );
    }

    public function testUserLogin()
    {
        $request = $this->getRequest(
            [
                "user_login" => "my_login"
            ],
            []
        );

        $this->assertEquals(
            "my_login",
            $request->getUserLogin()
        );
    }

    public function testKeyword()
    {
        $request = $this->getRequest(
            [
                "kwd" => "my_keyw"
            ],
            []
        );

        $this->assertEquals(
            "my_keyw",
            $request->getKeyword()
        );
    }
}
