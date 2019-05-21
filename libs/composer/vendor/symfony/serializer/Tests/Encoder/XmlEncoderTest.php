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
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Tests\Fixtures\Dummy;
use Symfony\Component\Serializer\Tests\Fixtures\NormalizableTraversableDummy;
use Symfony\Component\Serializer\Tests\Fixtures\ScalarDummy;

class XmlEncoderTest extends TestCase
{
    /**
     * @var XmlEncoder
     */
    private $encoder;

    private $exampleDateTimeString = '2017-02-19T15:16:08+0300';

    protected function setUp()
    {
        $this->encoder = new XmlEncoder();
        $serializer = new Serializer([new CustomNormalizer()], ['xml' => new XmlEncoder()]);
        $this->encoder->setSerializer($serializer);
    }

    public function testEncodeScalar()
    {
        $obj = new ScalarDummy();
        $obj->xmlFoo = 'foo';

        $expected = '<?xml version="1.0"?>'."\n".
            '<response>foo</response>'."\n";

        $this->assertEquals($expected, $this->encoder->encode($obj, 'xml'));
    }

    /**
     * @group legacy
     */
    public function testSetRootNodeName()
    {
        $obj = new ScalarDummy();
        $obj->xmlFoo = 'foo';

        $this->encoder->setRootNodeName('test');
        $expected = '<?xml version="1.0"?>'."\n".
            '<test>foo</test>'."\n";

        $this->assertEquals($expected, $this->encoder->encode($obj, 'xml'));
    }

    /**
     * @expectedException        \Symfony\Component\Serializer\Exception\UnexpectedValueException
     * @expectedExceptionMessage Document types are not allowed.
     */
    public function testDocTypeIsNotAllowed()
    {
        $this->encoder->decode('<?xml version="1.0"?><!DOCTYPE foo><foo></foo>', 'foo');
    }

    public function testAttributes()
    {
        $obj = new ScalarDummy();
        $obj->xmlFoo = [
            'foo-bar' => [
                '@id' => 1,
                '@name' => 'Bar',
            ],
            'Foo' => [
                'Bar' => 'Test',
                '@Type' => 'test',
            ],
            'föo_bär' => 'a',
            'Bar' => [1, 2, 3],
            'a' => 'b',
        ];
        $expected = '<?xml version="1.0"?>'."\n".
            '<response>'.
            '<foo-bar id="1" name="Bar"/>'.
            '<Foo Type="test"><Bar>Test</Bar></Foo>'.
            '<föo_bär>a</föo_bär>'.
            '<Bar>1</Bar>'.
            '<Bar>2</Bar>'.
            '<Bar>3</Bar>'.
            '<a>b</a>'.
            '</response>'."\n";
        $this->assertEquals($expected, $this->encoder->encode($obj, 'xml'));
    }

    public function testElementNameValid()
    {
        $obj = new ScalarDummy();
        $obj->xmlFoo = [
            'foo-bar' => 'a',
            'foo_bar' => 'a',
            'föo_bär' => 'a',
        ];

        $expected = '<?xml version="1.0"?>'."\n".
            '<response>'.
            '<foo-bar>a</foo-bar>'.
            '<foo_bar>a</foo_bar>'.
            '<föo_bär>a</föo_bär>'.
            '</response>'."\n";

        $this->assertEquals($expected, $this->encoder->encode($obj, 'xml'));
    }

    public function testEncodeSimpleXML()
    {
        $xml = simplexml_load_string('<firstname>Peter</firstname>');
        $array = ['person' => $xml];

        $expected = '<?xml version="1.0"?>'."\n".
            '<response><person><firstname>Peter</firstname></person></response>'."\n";

        $this->assertEquals($expected, $this->encoder->encode($array, 'xml'));
    }

    public function testEncodeXmlAttributes()
    {
        $xml = simplexml_load_string('<firstname>Peter</firstname>');
        $array = ['person' => $xml];

        $expected = '<?xml version="1.1" encoding="utf-8" standalone="yes"?>'."\n".
            '<response><person><firstname>Peter</firstname></person></response>'."\n";

        $context = [
            'xml_version' => '1.1',
            'xml_encoding' => 'utf-8',
            'xml_standalone' => true,
        ];

        $this->assertSame($expected, $this->encoder->encode($array, 'xml', $context));
    }

    public function testEncodeRemovingEmptyTags()
    {
        $array = ['person' => ['firstname' => 'Peter', 'lastname' => null]];

        $expected = '<?xml version="1.0"?>'."\n".
            '<response><person><firstname>Peter</firstname></person></response>'."\n";

        $context = ['remove_empty_tags' => true];

        $this->assertSame($expected, $this->encoder->encode($array, 'xml', $context));
    }

    public function testEncodeNotRemovingEmptyTags()
    {
        $array = ['person' => ['firstname' => 'Peter', 'lastname' => null]];

        $expected = '<?xml version="1.0"?>'."\n".
            '<response><person><firstname>Peter</firstname><lastname/></person></response>'."\n";

        $this->assertSame($expected, $this->encoder->encode($array, 'xml'));
    }

    public function testContext()
    {
        $array = ['person' => ['name' => 'George Abitbol']];
        $expected = <<<'XML'
<?xml version="1.0"?>
<response>
  <person>
    <name>George Abitbol</name>
  </person>
</response>

XML;

        $context = [
            'xml_format_output' => true,
        ];

        $this->assertSame($expected, $this->encoder->encode($array, 'xml', $context));
    }

    public function testEncodeScalarRootAttributes()
    {
        $array = [
            '#' => 'Paul',
            '@eye-color' => 'brown',
        ];

        $expected = '<?xml version="1.0"?>'."\n".
            '<response eye-color="brown">Paul</response>'."\n";

        $this->assertEquals($expected, $this->encoder->encode($array, 'xml'));
    }

    public function testEncodeRootAttributes()
    {
        $array = [
            'firstname' => 'Paul',
            '@eye-color' => 'brown',
        ];

        $expected = '<?xml version="1.0"?>'."\n".
            '<response eye-color="brown"><firstname>Paul</firstname></response>'."\n";

        $this->assertEquals($expected, $this->encoder->encode($array, 'xml'));
    }

    public function testEncodeCdataWrapping()
    {
        $array = [
            'firstname' => 'Paul <or Me>',
        ];

        $expected = '<?xml version="1.0"?>'."\n".
            '<response><firstname><![CDATA[Paul <or Me>]]></firstname></response>'."\n";

        $this->assertEquals($expected, $this->encoder->encode($array, 'xml'));
    }

    public function testEncodeScalarWithAttribute()
    {
        $array = [
            'person' => ['@eye-color' => 'brown', '#' => 'Peter'],
        ];

        $expected = '<?xml version="1.0"?>'."\n".
            '<response><person eye-color="brown">Peter</person></response>'."\n";

        $this->assertEquals($expected, $this->encoder->encode($array, 'xml'));
    }

    public function testDecodeScalar()
    {
        $source = '<?xml version="1.0"?>'."\n".
            '<response>foo</response>'."\n";

        $this->assertEquals('foo', $this->encoder->decode($source, 'xml'));
    }

    public function testDecodeBigDigitAttributes()
    {
        $source = <<<XML
<?xml version="1.0"?>
<document index="182077241760011681341821060401202210011000045913000000017100">Name</document>
XML;

        $this->assertSame(['@index' => 182077241760011681341821060401202210011000045913000000017100, '#' => 'Name'], $this->encoder->decode($source, 'xml'));
    }

    public function testDecodeNegativeIntAttribute()
    {
        $source = <<<XML
<?xml version="1.0"?>
<document index="-1234">Name</document>
XML;

        $this->assertSame(['@index' => -1234, '#' => 'Name'], $this->encoder->decode($source, 'xml'));
    }

    public function testDecodeFloatAttribute()
    {
        $source = <<<XML
<?xml version="1.0"?>
<document index="-12.11">Name</document>
XML;

        $this->assertSame(['@index' => -12.11, '#' => 'Name'], $this->encoder->decode($source, 'xml'));
    }

    public function testDecodeNegativeFloatAttribute()
    {
        $source = <<<XML
<?xml version="1.0"?>
<document index="-12.11">Name</document>
XML;

        $this->assertSame(['@index' => -12.11, '#' => 'Name'], $this->encoder->decode($source, 'xml'));
    }

    public function testNoTypeCastAttribute()
    {
        $source = <<<XML
<?xml version="1.0"?>
<document a="018" b="-12.11">
    <node a="018" b="-12.11"/>
</document>
XML;

        $data = $this->encoder->decode($source, 'xml', ['xml_type_cast_attributes' => false]);
        $expected = [
            '@a' => '018',
            '@b' => '-12.11',
            'node' => [
                '@a' => '018',
                '@b' => '-12.11',
                '#' => '',
            ],
        ];
        $this->assertSame($expected, $data);
    }

    public function testEncode()
    {
        $source = $this->getXmlSource();
        $obj = $this->getObject();

        $this->assertEquals($source, $this->encoder->encode($obj, 'xml'));
    }

    public function testEncodeWithNamespace()
    {
        $source = $this->getNamespacedXmlSource();
        $array = $this->getNamespacedArray();

        $this->assertEquals($source, $this->encoder->encode($array, 'xml'));
    }

    public function testEncodeSerializerXmlRootNodeNameOption()
    {
        $options = ['xml_root_node_name' => 'test'];
        $this->encoder = new XmlEncoder();
        $serializer = new Serializer([], ['xml' => new XmlEncoder()]);
        $this->encoder->setSerializer($serializer);

        $array = [
            'person' => ['@eye-color' => 'brown', '#' => 'Peter'],
        ];

        $expected = '<?xml version="1.0"?>'."\n".
            '<test><person eye-color="brown">Peter</person></test>'."\n";

        $this->assertEquals($expected, $serializer->serialize($array, 'xml', $options));
    }

    public function testEncodeTraversableWhenNormalizable()
    {
        $this->encoder = new XmlEncoder();
        $serializer = new Serializer([new CustomNormalizer()], ['xml' => new XmlEncoder()]);
        $this->encoder->setSerializer($serializer);

        $expected = <<<'XML'
<?xml version="1.0"?>
<response><foo>normalizedFoo</foo><bar>normalizedBar</bar></response>

XML;

        $this->assertEquals($expected, $serializer->serialize(new NormalizableTraversableDummy(), 'xml'));
    }

    public function testDecode()
    {
        $source = $this->getXmlSource();
        $obj = $this->getObject();

        $this->assertEquals(get_object_vars($obj), $this->encoder->decode($source, 'xml'));
    }

    public function testDecodeCdataWrapping()
    {
        $expected = [
            'firstname' => 'Paul <or Me>',
        ];

        $xml = '<?xml version="1.0"?>'."\n".
            '<response><firstname><![CDATA[Paul <or Me>]]></firstname></response>'."\n";

        $this->assertEquals($expected, $this->encoder->decode($xml, 'xml'));
    }

    public function testDecodeCdataWrappingAndWhitespace()
    {
        $expected = [
            'firstname' => 'Paul <or Me>',
        ];

        $xml = '<?xml version="1.0"?>'."\n".
            '<response><firstname>'."\n".
                '<![CDATA[Paul <or Me>]]></firstname></response>'."\n";

        $this->assertEquals($expected, $this->encoder->decode($xml, 'xml'));
    }

    public function testDecodeWithNamespace()
    {
        $source = $this->getNamespacedXmlSource();
        $array = $this->getNamespacedArray();

        $this->assertEquals($array, $this->encoder->decode($source, 'xml'));
    }

    public function testDecodeScalarWithAttribute()
    {
        $source = '<?xml version="1.0"?>'."\n".
            '<response><person eye-color="brown">Peter</person></response>'."\n";

        $expected = [
            'person' => ['@eye-color' => 'brown', '#' => 'Peter'],
        ];

        $this->assertEquals($expected, $this->encoder->decode($source, 'xml'));
    }

    public function testDecodeScalarRootAttributes()
    {
        $source = '<?xml version="1.0"?>'."\n".
            '<person eye-color="brown">Peter</person>'."\n";

        $expected = [
            '#' => 'Peter',
            '@eye-color' => 'brown',
        ];

        $this->assertEquals($expected, $this->encoder->decode($source, 'xml'));
    }

    public function testDecodeRootAttributes()
    {
        $source = '<?xml version="1.0"?>'."\n".
            '<person eye-color="brown"><firstname>Peter</firstname><lastname>Mac Calloway</lastname></person>'."\n";

        $expected = [
            'firstname' => 'Peter',
            'lastname' => 'Mac Calloway',
            '@eye-color' => 'brown',
        ];

        $this->assertEquals($expected, $this->encoder->decode($source, 'xml'));
    }

    public function testDecodeArray()
    {
        $source = '<?xml version="1.0"?>'."\n".
            '<response>'.
            '<people>'.
            '<person><firstname>Benjamin</firstname><lastname>Alexandre</lastname></person>'.
            '<person><firstname>Damien</firstname><lastname>Clay</lastname></person>'.
            '</people>'.
            '</response>'."\n";

        $expected = [
            'people' => ['person' => [
                ['firstname' => 'Benjamin', 'lastname' => 'Alexandre'],
                ['firstname' => 'Damien', 'lastname' => 'Clay'],
            ]],
        ];

        $this->assertEquals($expected, $this->encoder->decode($source, 'xml'));
    }

    public function testDecodeXMLWithProcessInstruction()
    {
        $source = <<<'XML'
<?xml version="1.0"?>
<?xml-stylesheet type="text/xsl" href="/xsl/xmlverbatimwrapper.xsl"?>
    <?display table-view?>
    <?sort alpha-ascending?>
    <response>
        <foo>foo</foo>
        <?textinfo whitespace is allowed ?>
        <bar>a</bar>
        <bar>b</bar>
        <baz>
            <key>val</key>
            <key2>val</key2>
            <item key="A B">bar</item>
            <item>
                <title>title1</title>
            </item>
            <?item ignore-title ?>
            <item>
                <title>title2</title>
            </item>
            <Barry>
                <FooBar id="1">
                    <Baz>Ed</Baz>
                </FooBar>
            </Barry>
        </baz>
        <qux>1</qux>
    </response>
    <?instruction <value> ?>
XML;
        $obj = $this->getObject();

        $this->assertEquals(get_object_vars($obj), $this->encoder->decode($source, 'xml'));
    }

    public function testDecodeIgnoreWhiteSpace()
    {
        $source = <<<'XML'
<?xml version="1.0"?>
<people>
    <person>
        <firstname>Benjamin</firstname>
        <lastname>Alexandre</lastname>
    </person>
    <person>
        <firstname>Damien</firstname>
        <lastname>Clay</lastname>
    </person>
</people>
XML;
        $expected = ['person' => [
            ['firstname' => 'Benjamin', 'lastname' => 'Alexandre'],
            ['firstname' => 'Damien', 'lastname' => 'Clay'],
        ]];

        $this->assertEquals($expected, $this->encoder->decode($source, 'xml'));
    }

    public function testDecodeIgnoreComments()
    {
        $source = <<<'XML'
<?xml version="1.0"?>
<!-- This comment should not become the root node. -->
<people>
    <person>
        <!-- Even if the first comment didn't become the root node, we don't
             want this comment either. -->
        <firstname>Benjamin</firstname>
        <lastname>Alexandre</lastname>
    </person>
    <person>
        <firstname>Damien</firstname>
        <lastname>Clay</lastname>
    </person>
</people>
XML;

        $expected = ['person' => [
          ['firstname' => 'Benjamin', 'lastname' => 'Alexandre'],
          ['firstname' => 'Damien', 'lastname' => 'Clay'],
        ]];

        $this->assertEquals($expected, $this->encoder->decode($source, 'xml'));
    }

    public function testDecodePreserveComments()
    {
        $this->doTestDecodePreserveComments();
    }

    public function testLegacyDecodePreserveComments()
    {
        $this->doTestDecodePreserveComments(true);
    }

    private function doTestDecodePreserveComments(bool $legacy = false)
    {
        $source = <<<'XML'
<?xml version="1.0"?>
<people>
    <person>
        <!-- This comment should be decoded. -->
        <firstname>Benjamin</firstname>
        <lastname>Alexandre</lastname>
    </person>
    <person>
        <firstname>Damien</firstname>
        <lastname>Clay</lastname>
    </person>
</people>
XML;

        if ($legacy) {
            $this->encoder = new XmlEncoder('people', null, [XML_PI_NODE]);
        } else {
            $this->encoder = new XmlEncoder([
                XmlEncoder::ROOT_NODE_NAME => 'people',
                XmlEncoder::DECODER_IGNORED_NODE_TYPES => [XML_PI_NODE],
            ]);
        }
        $serializer = new Serializer([new CustomNormalizer()], ['xml' => new XmlEncoder()]);
        $this->encoder->setSerializer($serializer);

        $expected = ['person' => [
          ['firstname' => 'Benjamin', 'lastname' => 'Alexandre', '#comment' => ' This comment should be decoded. '],
          ['firstname' => 'Damien', 'lastname' => 'Clay'],
        ]];

        $this->assertEquals($expected, $this->encoder->decode($source, 'xml'));
    }

    public function testDecodeAlwaysAsCollection()
    {
        $this->doTestDecodeAlwaysAsCollection();
    }

    public function testLegacyDecodeAlwaysAsCollection()
    {
        $this->doTestDecodeAlwaysAsCollection(true);
    }

    private function doTestDecodeAlwaysAsCollection(bool $legacy = false)
    {
        if ($legacy) {
            $this->encoder = new XmlEncoder('response', null);
        } else {
            $this->encoder = new XmlEncoder([XmlEncoder::ROOT_NODE_NAME => 'response']);
        }
        $serializer = new Serializer([new CustomNormalizer()], ['xml' => new XmlEncoder()]);
        $this->encoder->setSerializer($serializer);

        $source = <<<'XML'
<?xml version="1.0"?>
<order_rows nodeType="order_row" virtualEntity="true">
    <order_row>
        <id><![CDATA[16]]></id>
        <test><![CDATA[16]]></test>
    </order_row>
</order_rows>
XML;
        $expected = [
            '@nodeType' => 'order_row',
            '@virtualEntity' => 'true',
            'order_row' => [[
                'id' => [16],
                'test' => [16],
            ]],
        ];

        $this->assertEquals($expected, $this->encoder->decode($source, 'xml', ['as_collection' => true]));
    }

    public function testDecodeWithoutItemHash()
    {
        $obj = new ScalarDummy();
        $obj->xmlFoo = [
            'foo-bar' => [
                '@key' => 'value',
                'item' => ['@key' => 'key', 'key-val' => 'val'],
            ],
            'Foo' => [
                'Bar' => 'Test',
                '@Type' => 'test',
            ],
            'föo_bär' => 'a',
            'Bar' => [1, 2, 3],
            'a' => 'b',
        ];
        $expected = [
            'foo-bar' => [
                '@key' => 'value',
                'key' => ['@key' => 'key', 'key-val' => 'val'],
            ],
            'Foo' => [
                'Bar' => 'Test',
                '@Type' => 'test',
            ],
            'föo_bär' => 'a',
            'Bar' => [1, 2, 3],
            'a' => 'b',
        ];
        $xml = $this->encoder->encode($obj, 'xml');
        $this->assertEquals($expected, $this->encoder->decode($xml, 'xml'));
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\UnexpectedValueException
     */
    public function testDecodeInvalidXml()
    {
        $this->encoder->decode('<?xml version="1.0"?><invalid><xml>', 'xml');
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\UnexpectedValueException
     */
    public function testPreventsComplexExternalEntities()
    {
        $this->encoder->decode('<?xml version="1.0"?><!DOCTYPE scan[<!ENTITY test SYSTEM "php://filter/read=convert.base64-encode/resource=XmlEncoderTest.php">]><scan>&test;</scan>', 'xml');
    }

    public function testDecodeEmptyXml()
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Symfony\Component\Serializer\Exception\UnexpectedValueException');
            $this->expectExceptionMessage('Invalid XML data, it can not be empty.');
        } else {
            $this->setExpectedException('Symfony\Component\Serializer\Exception\UnexpectedValueException', 'Invalid XML data, it can not be empty.');
        }
        $this->encoder->decode(' ', 'xml');
    }

    protected function getXmlSource()
    {
        return '<?xml version="1.0"?>'."\n".
            '<response>'.
            '<foo>foo</foo>'.
            '<bar>a</bar><bar>b</bar>'.
            '<baz><key>val</key><key2>val</key2><item key="A B">bar</item>'.
            '<item><title>title1</title></item><item><title>title2</title></item>'.
            '<Barry><FooBar id="1"><Baz>Ed</Baz></FooBar></Barry></baz>'.
            '<qux>1</qux>'.
            '</response>'."\n";
    }

    protected function getNamespacedXmlSource()
    {
        return '<?xml version="1.0"?>'."\n".
            '<response xmlns="http://www.w3.org/2005/Atom" xmlns:app="http://www.w3.org/2007/app" xmlns:media="http://search.yahoo.com/mrss/" xmlns:gd="http://schemas.google.com/g/2005" xmlns:yt="http://gdata.youtube.com/schemas/2007">'.
            '<qux>1</qux>'.
            '<app:foo>foo</app:foo>'.
            '<yt:bar>a</yt:bar><yt:bar>b</yt:bar>'.
            '<media:baz><media:key>val</media:key><media:key2>val</media:key2><item key="A B">bar</item>'.
            '<item><title>title1</title></item><item><title>title2</title></item>'.
            '<Barry size="large"><FooBar gd:id="1"><Baz>Ed</Baz></FooBar></Barry></media:baz>'.
            '</response>'."\n";
    }

    protected function getNamespacedArray()
    {
        return [
            '@xmlns' => 'http://www.w3.org/2005/Atom',
            '@xmlns:app' => 'http://www.w3.org/2007/app',
            '@xmlns:media' => 'http://search.yahoo.com/mrss/',
            '@xmlns:gd' => 'http://schemas.google.com/g/2005',
            '@xmlns:yt' => 'http://gdata.youtube.com/schemas/2007',
            'qux' => '1',
            'app:foo' => 'foo',
            'yt:bar' => ['a', 'b'],
            'media:baz' => [
                'media:key' => 'val',
                'media:key2' => 'val',
                'A B' => 'bar',
                'item' => [
                    [
                        'title' => 'title1',
                    ],
                    [
                        'title' => 'title2',
                    ],
                ],
                'Barry' => [
                    '@size' => 'large',
                    'FooBar' => [
                        'Baz' => 'Ed',
                        '@gd:id' => 1,
                    ],
                ],
            ],
        ];
    }

    protected function getObject()
    {
        $obj = new Dummy();
        $obj->foo = 'foo';
        $obj->bar = ['a', 'b'];
        $obj->baz = ['key' => 'val', 'key2' => 'val', 'A B' => 'bar', 'item' => [['title' => 'title1'], ['title' => 'title2']], 'Barry' => ['FooBar' => ['Baz' => 'Ed', '@id' => 1]]];
        $obj->qux = '1';

        return $obj;
    }

    public function testEncodeXmlWithBoolValue()
    {
        $expectedXml = <<<'XML'
<?xml version="1.0"?>
<response><foo>1</foo><bar>0</bar></response>

XML;

        $actualXml = $this->encoder->encode(['foo' => true, 'bar' => false], 'xml');

        $this->assertEquals($expectedXml, $actualXml);
    }

    public function testEncodeXmlWithDateTimeObjectValue()
    {
        $xmlEncoder = $this->createXmlEncoderWithDateTimeNormalizer();

        $actualXml = $xmlEncoder->encode(['dateTime' => new \DateTime($this->exampleDateTimeString)], 'xml');

        $this->assertEquals($this->createXmlWithDateTime(), $actualXml);
    }

    public function testEncodeXmlWithDateTimeObjectField()
    {
        $xmlEncoder = $this->createXmlEncoderWithDateTimeNormalizer();

        $actualXml = $xmlEncoder->encode(['foo' => ['@dateTime' => new \DateTime($this->exampleDateTimeString)]], 'xml');

        $this->assertEquals($this->createXmlWithDateTimeField(), $actualXml);
    }

    public function testEncodeComment()
    {
        $expected = <<<'XML'
<?xml version="1.0"?>
<response><!-- foo --></response>

XML;

        $data = ['#comment' => ' foo '];

        $this->assertEquals($expected, $this->encoder->encode($data, 'xml'));
    }

    public function testEncodeWithoutPi()
    {
        $this->doTestEncodeWithoutPi();
    }

    public function testLegacyEncodeWithoutPi()
    {
        $this->doTestEncodeWithoutPi(true);
    }

    private function doTestEncodeWithoutPi(bool $legacy = false)
    {
        if ($legacy) {
            $encoder = new XmlEncoder('response', null, [], [XML_PI_NODE]);
        } else {
            $encoder = new XmlEncoder([
                XmlEncoder::ROOT_NODE_NAME => 'response',
                XmlEncoder::ENCODER_IGNORED_NODE_TYPES => [XML_PI_NODE],
            ]);
        }

        $expected = '<response/>';

        $this->assertEquals($expected, $encoder->encode([], 'xml'));
    }

    public function testEncodeWithoutComment()
    {
        $this->doTestEncodeWithoutComment();
    }

    public function testLegacyEncodeWithoutComment()
    {
        $this->doTestEncodeWithoutComment(true);
    }

    private function doTestEncodeWithoutComment(bool $legacy = false)
    {
        if ($legacy) {
            $encoder = new XmlEncoder('response', null, [], [XML_COMMENT_NODE]);
        } else {
            $encoder = new XmlEncoder([
                XmlEncoder::ROOT_NODE_NAME => 'response',
                XmlEncoder::ENCODER_IGNORED_NODE_TYPES => [XML_COMMENT_NODE],
            ]);
        }

        $expected = <<<'XML'
<?xml version="1.0"?>
<response/>

XML;

        $data = ['#comment' => ' foo '];

        $this->assertEquals($expected, $encoder->encode($data, 'xml'));
    }

    /**
     * @return XmlEncoder
     */
    private function createXmlEncoderWithDateTimeNormalizer()
    {
        $encoder = new XmlEncoder();
        $serializer = new Serializer([$this->createMockDateTimeNormalizer()], ['xml' => new XmlEncoder()]);
        $encoder->setSerializer($serializer);

        return $encoder;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|NormalizerInterface
     */
    private function createMockDateTimeNormalizer()
    {
        $mock = $this->getMockBuilder('\Symfony\Component\Serializer\Normalizer\CustomNormalizer')->getMock();

        $mock
            ->expects($this->once())
            ->method('normalize')
            ->with(new \DateTime($this->exampleDateTimeString), 'xml', [])
            ->willReturn($this->exampleDateTimeString);

        $mock
            ->expects($this->once())
            ->method('supportsNormalization')
            ->with(new \DateTime($this->exampleDateTimeString), 'xml')
            ->willReturn(true);

        return $mock;
    }

    /**
     * @return string
     */
    private function createXmlWithDateTime()
    {
        return sprintf('<?xml version="1.0"?>
<response><dateTime>%s</dateTime></response>
', $this->exampleDateTimeString);
    }

    /**
     * @return string
     */
    private function createXmlWithDateTimeField()
    {
        return sprintf('<?xml version="1.0"?>
<response><foo dateTime="%s"/></response>
', $this->exampleDateTimeString);
    }
}
