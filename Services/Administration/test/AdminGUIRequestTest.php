<?php

use PHPUnit\Framework\TestCase;

/**
 * Test administration request class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class AdminGUIRequestTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function tearDown() : void
    {
    }

    protected function getRequest(array $get, array $post) : \ILIAS\Administration\AdminGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\Administration\AdminGUIRequest(
            $http_mock,
            $refinery,
            $get,
            $post
        );
    }

    /**
     * Test ref id
     */
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

    /**
     * Test no ref id
     */
    public function testNoRefId()
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
     * Test admin mode
     */
    public function testAdminMode()
    {
        $request = $this->getRequest(
            [
                "admin_mode" => "repository"
            ],
            []
        );

        $this->assertEquals(
            "repository",
            $request->getAdminMode()
        );
    }

    /**
     * Test selected ids
     */
    public function testSelectedIds()
    {
        $request = $this->getRequest(
            [
            ],
            [
                "id" => ["1", "2", "3"]
            ]
        );

        $this->assertEquals(
            [1,2,3],
            $request->getSelectedIds()
        );
    }

    /**
     * Test new type
     */
    public function testNewType()
    {
        $request = $this->getRequest(
            [
                "new_type" => "usr"
            ],
            []
        );

        $this->assertEquals(
            "usr",
            $request->getNewType()
        );
    }

    /**
     * Test user id
     */
    public function testUserId()
    {
        $request = $this->getRequest(
            [
                "jmpToUser" => "15"
            ],
            []
        );

        $this->assertEquals(
            15,
            $request->getJumpToUserId()
        );
    }

    /**
     * Test plugin id
     */
    public function testPluginId()
    {
        $request = $this->getRequest(
            [
                "plugin_id" => "xyz"
            ],
            []
        );

        $this->assertEquals(
            "xyz",
            $request->getPluginId()
        );
    }
}
