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

namespace ILIAS\Component\Tests\Resource;

use PHPUnit\Framework\TestCase;
use ILIAS\Component\Dependencies\Name;
use ILIAS\Component\Resource as R;

class ComponentResourceTest extends TestCase
{
    public function testTarget1()
    {
        $public_asset = new R\ComponentResource(
            new \ILIAS\Component(),
            "asset.png",
            "some/target"
        );

        $this->assertEquals("some/target/asset.png", $public_asset->getTarget());
    }

    public function testTarget2()
    {
        $public_asset = new R\ComponentResource(
            new \ILIAS\Component(),
            "directory/asset.png",
            "some/target"
        );

        $this->assertEquals("some/target/asset.png", $public_asset->getTarget());
    }

    public function testTarget3()
    {
        $public_asset = new R\ComponentResource(
            new \ILIAS\Component(),
            "directory/asset.png",
            "some/target"
        );

        $this->assertEquals("some/target/asset.png", $public_asset->getTarget());
    }

    public function testHtaccessIsAllowedAsSource()
    {
        $public_asset = new R\ComponentResource(
            new \ILIAS\Component(),
            ".htaccess",
            "target"
        );

        $this->assertTrue(true);
    }

    public function testDotIsAllowedAsTarget()
    {
        $public_asset = new R\ComponentResource(
            new \ILIAS\Component(),
            "foo.php",
            "."
        );

        $this->assertTrue(true);
    }

    public function testSource()
    {
        $public_asset = new R\ComponentResource(
            new \ILIAS\Component(),
            "directory/asset.png",
            "some/target"
        );

        $this->assertEquals("components/ILIAS/Component/resources/directory/asset.png", $public_asset->getSource());
    }
}
