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
    public function testRefId() : void
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
    public function testNoRefId() : void
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
    public function testAdminMode() : void
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
    public function testSelectedIds() : void
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
    public function testNewType() : void
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
    public function testUserId() : void
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
    public function testPluginId() : void
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
