<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;
use PHPUnit\Framework\TestCase;

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

        $obj = new Setup\CLI\ConfigReader();

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

        $obj = new Setup\CLI\ConfigReader("/tmp");

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

        $obj = new Setup\CLI\ConfigReader("/foo");

        $config = $obj->readConfigFile($filename);

        $this->assertEquals($expected, $config);
    }

    public function testApplyOverwrites() : void
    {
        $cr = new class() extends Setup\CLI\ConfigReader {
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
}
