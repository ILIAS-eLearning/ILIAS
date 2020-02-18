<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;
use Symfony\Component\Console\Tester\CommandTester;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;

class ConfigReaderTest extends \PHPUnit\Framework\TestCase
{
    public function testReadConfigFile()
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

    public function testBaseDir()
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

    public function testTotalDir()
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

    public function testApplyOverwrites()
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
