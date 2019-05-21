<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Serializer\Tests\Encoder;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class YamlEncoderTest extends TestCase
{
    public function testEncode()
    {
        $encoder = new YamlEncoder();

        $this->assertEquals('foo', $encoder->encode('foo', 'yaml'));
        $this->assertEquals('{ foo: 1 }', $encoder->encode(['foo' => 1], 'yaml'));
    }

    public function testSupportsEncoding()
    {
        $encoder = new YamlEncoder();

        $this->assertTrue($encoder->supportsEncoding('yaml'));
        $this->assertTrue($encoder->supportsEncoding('yml'));
        $this->assertFalse($encoder->supportsEncoding('json'));
    }

    public function testDecode()
    {
        $encoder = new YamlEncoder();

        $this->assertEquals('foo', $encoder->decode('foo', 'yaml'));
        $this->assertEquals(['foo' => 1], $encoder->decode('{ foo: 1 }', 'yaml'));
    }

    public function testSupportsDecoding()
    {
        $encoder = new YamlEncoder();

        $this->assertTrue($encoder->supportsDecoding('yaml'));
        $this->assertTrue($encoder->supportsDecoding('yml'));
        $this->assertFalse($encoder->supportsDecoding('json'));
    }

    public function testContext()
    {
        $encoder = new YamlEncoder(new Dumper(), new Parser(), ['yaml_inline' => 1, 'yaml_indent' => 4, 'yaml_flags' => Yaml::DUMP_OBJECT | Yaml::PARSE_OBJECT]);

        $obj = new \stdClass();
        $obj->bar = 2;

        $legacyTag = "    foo: !php/object:O:8:\"stdClass\":1:{s:3:\"bar\";i:2;}\n";
        $spacedTag = "    foo: !php/object 'O:8:\"stdClass\":1:{s:3:\"bar\";i:2;}'\n";
        $this->assertThat($encoder->encode(['foo' => $obj], 'yaml'), $this->logicalOr($this->equalTo($legacyTag), $this->equalTo($spacedTag)));
        $this->assertEquals('  { foo: null }', $encoder->encode(['foo' => $obj], 'yaml', ['yaml_inline' => 0, 'yaml_indent' => 2, 'yaml_flags' => 0]));
        $this->assertEquals(['foo' => $obj], $encoder->decode("foo: !php/object 'O:8:\"stdClass\":1:{s:3:\"bar\";i:2;}'", 'yaml'));
        $this->assertEquals(['foo' => null], $encoder->decode("foo: !php/object 'O:8:\"stdClass\":1:{s:3:\"bar\";i:2;}'", 'yaml', ['yaml_flags' => 0]));
    }
}
