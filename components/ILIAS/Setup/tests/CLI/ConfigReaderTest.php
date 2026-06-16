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

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;
use PHPUnit\Framework\TestCase;

class ConfigReaderTest extends TestCase
{
    public function testReadConfigFile(): void
    {
        $filename = tempnam("/tmp", "ILIAS");
        $expected = [
            "some" => [
                "nested" => "config"
            ]
        ];
        file_put_contents($filename, json_encode($expected));
        $obj = new Setup\CLI\ConfigReader();

        $config = $obj->readConfigFile($filename);

        $this->assertEquals($expected, $config);
    }

    public function testBaseDir(): void
    {
        $filename = tempnam("/tmp", "ILIAS");
        $expected = [
            "some" => [
                "nested" => "config"
            ]
        ];
        file_put_contents($filename, json_encode($expected));

        $obj = new Setup\CLI\ConfigReader("/tmp");

        $config = $obj->readConfigFile(basename($filename));

        $this->assertEquals($expected, $config);
    }

    public function testTotalDir(): void
    {
        $filename = tempnam("/tmp", "ILIAS");
        $expected = [
            "some" => [
                "nested" => "config"
            ]
        ];
        file_put_contents($filename, json_encode($expected));

        $obj = new Setup\CLI\ConfigReader("/foo");

        $config = $obj->readConfigFile($filename);

        $this->assertEquals($expected, $config);
    }
}
