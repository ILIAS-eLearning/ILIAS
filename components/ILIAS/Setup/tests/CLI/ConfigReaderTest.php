<?php

declare(strict_types=1);

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

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;
use PHPUnit\Framework\TestCase;
use Seld\JsonLint\JsonParser;

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
        $obj = new Setup\CLI\ConfigReader(new JsonParser());

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

        $obj = new Setup\CLI\ConfigReader(new JsonParser(), "/tmp");

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

        $obj = new Setup\CLI\ConfigReader(new JsonParser(), "/foo");

        $config = $obj->readConfigFile($filename);

        $this->assertEquals($expected, $config);
    }

    public function testApplyOverwrites(): void
    {
        $cr = new class (new JsonParser()) extends Setup\CLI\ConfigReader {
            public function _applyOverwrites($j, $o)
            {
                return $this->applyOverwrites($j, $o);
            }
        };

        $array = [
            "1" => [
                "1" => "1.1",
                "2" => [
                    "1" => "1.2.1"
                ],
            ],
            "2" => "2"
        ];
        $overwrites = [
            "1.2.1" => "foo",
            "2" => "bar"
        ];
        $expected = [
            "1" => [
                "1" => "1.1",
                "2" => [
                    "1" => "foo"
                ],
            ],
            "2" => "bar"
        ];

        $result = $cr->_applyOverwrites($array, $overwrites);
        $this->assertEquals($expected, $result);
    }

    public function testApplyOverwritesToUnsetField(): void
    {
        $cr = new class (new JsonParser()) extends Setup\CLI\ConfigReader {
            public function _applyOverwrites($j, $o)
            {
                return $this->applyOverwrites($j, $o);
            }
        };

        $array = [
        ];
        $overwrites = [
            "1.1.1" => "foo",
        ];
        $expected = [
            "1" => [
                "1" => [
                    "1" => "foo"
                ],
            ]
        ];

        $result = $cr->_applyOverwrites($array, $overwrites);
        $this->assertEquals($expected, $result);
    }
}
