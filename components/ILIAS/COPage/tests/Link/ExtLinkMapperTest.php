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

namespace ILIAS\COPage\Test\Link;

use PHPUnit\Framework\TestCase;
use ILIAS\COPage\Link\LinkManager;
use ILIAS\COPage\Link\ExtLinkMapper;

class ExtLinkMapperTest extends \COPageTestBase
{
    public function testGetRefId(): void
    {
        $def_mock = $this->getMockBuilder(\ilObjectDefinition::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $def_mock
            ->method('isRBACObject')
            ->willReturn(true);

        $ext_link_mapper = new ExtLinkMapper(
            $def_mock,
            "http://localhost/ilias",
            [3 => 5],
            "client"
        );

        $ref_id = $ext_link_mapper->getRefId("http://localhost/ilias/goto.php/file/231");
        $this->assertEquals(
            231,
            $ref_id
        );

        $ref_id = $ext_link_mapper->getRefId("http://localhost/ilias/goto_client_cat_581.html");
        $this->assertEquals(
            581,
            $ref_id
        );

        $ref_id = $ext_link_mapper->getRefId("http://localhost/ilias/go/file/33");
        $this->assertEquals(
            33,
            $ref_id
        );

        $ref_id = $ext_link_mapper->getRefId("http://localhost/ilias/ilias.php?baseClass=ilrepositorygui&ref_id=198");
        $this->assertEquals(
            198,
            $ref_id
        );
    }
}
