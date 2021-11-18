<?php declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;
use PHPUnit\Framework\TestCase;
use Seld\JsonLint\JsonParser;

class ConfigReaderTest extends TestCase
{
    public function testReadConfigFile() : void
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

    public function testBaseDir() : void
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

    public function testTotalDir() : void
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

    public function testApplyOverwrites() : void
    {
        $cr = new class(new JsonParser()) extends Setup\CLI\ConfigReader {
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

    public function testApplyOverwritesToUnsetField() : void
    {
        $cr = new class(new JsonParser()) extends Setup\CLI\ConfigReader {
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
