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

namespace IntLink;

use PHPUnit\Framework\TestCase;
use ILIAS;
use ilLanguage;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class LinkStandardGUIRequestTest extends TestCase
{
    protected function tearDown(): void
    {
    }

    protected function getRequest(array $get, array $post): \ILIAS\COPage\IntLink\StandardGUIRequest
    {
        $http_mock = $this->createMock(ILIAS\HTTP\Services::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $data = new \ILIAS\Data\Factory();
        $refinery = new \ILIAS\Refinery\Factory($data, $lng_mock);
        return new \ILIAS\COPage\IntLink\StandardGUIRequest(
            $http_mock,
            $refinery,
            $get,
            $post
        );
    }

    public function testSelectedId(): void
    {
        $request = $this->getRequest(
            [
                "sel_id" => "123"
            ],
            [
            ]
        );

        $this->assertEquals(
            123,
            $request->getSelectedId()
        );
    }

    public function testDo(): void
    {
        $request = $this->getRequest(
            [
                "do" => "set"
            ],
            [
            ]
        );

        $this->assertEquals(
            "set",
            $request->getDo()
        );
    }

    public function testMediaPoolFolder(): void
    {
        $request = $this->getRequest(
            [
                "mep_fold" => "14"
            ],
            [
            ]
        );

        $this->assertEquals(
            14,
            $request->getMediaPoolFolder()
        );
    }

    public function testLinkType(): void
    {
        $request = $this->getRequest(
            [
                "link_type" => "mytype"
            ],
            [
            ]
        );

        $this->assertEquals(
            "mytype",
            $request->getLinkType()
        );
    }

    public function testLinkParentObjId(): void
    {
        $request = $this->getRequest(
            [
                "link_par_obj_id" => "13"
            ],
            [
            ]
        );

        $this->assertEquals(
            13,
            $request->getLinkParentObjId()
        );
    }

    public function testLinkParentFolderId(): void
    {
        $request = $this->getRequest(
            [
                "link_par_fold_id" => "18"
            ],
            [
            ]
        );

        $this->assertEquals(
            18,
            $request->getLinkParentFolderId()
        );
    }

    public function testLinkParentRefId(): void
    {
        $request = $this->getRequest(
            [
                "link_par_ref_id" => "22"
            ],
            [
            ]
        );

        $this->assertEquals(
            22,
            $request->getLinkParentRefId()
        );
    }

    public function testUserSearchString(): void
    {
        $request = $this->getRequest(
            [
            ],
            [
                "usr_search_str" => "term"
            ]
        );

        $this->assertEquals(
            "term",
            $request->getUserSearchStr()
        );
    }
}
