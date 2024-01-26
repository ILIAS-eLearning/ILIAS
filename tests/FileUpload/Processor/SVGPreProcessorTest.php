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

namespace ILIAS\FileUpload\Processor;

require_once('./libs/composer/vendor/autoload.php');

use PHPUnit\Framework\TestCase;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\DTO\ProcessingStatus;

/**
 * Class SVGPreProcessorTest
 */
class SVGPreProcessorTest extends TestCase
{
    public function maliciousSVGProvider() : array
    {
        return [
            [
                '<svg width="100" height="100">
    <foreignObject width="100%" height="100%">
        <script>alert(document.domain);</script>
    </foreignObject>
</svg>',
                'script'
            ],
            [
                '<svg width="100" height="100">
    <foreignObject width="100%" height="100%" onclick="alert(document.domain);">
        
    </foreignObject>
</svg>',
                'onclick'
            ],
            [
                '<svg version="1.1" baseProfile="full"
xmlns="http://www.w3.org/2000/svg">
<rect width="100" height="100" style="fill:rgb(0,0,255);" />
<script type="text/javascript">
alert("XSS in SVG on " + document.domain );
</script>
</svg>',
                'script'
            ],
            [
                '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
<use xlink:href="data:application/xml;base64 ,
PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5r
PSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj4KPGRlZnM+CjxjaXJjbGUgaWQ9InRlc3QiIHI9I
jUwIiBjeD0iMTAwIiBjeT0iMTAwIiBzdHlsZT0iZmlsbDogI0YwMCI+CjxzZXQgYXR0cmlidXRlTm
FtZT0iZmlsbCIgYXR0cmlidXRlVHlwZT0iQ1NTIiBvbmJlZ2luPSdhbGVydChkb2N1bWVudC5jb29r
aWUpJwpvbmVuZD0nYWxlcnQoIm9uZW5kIiknIHRvPSIjMDBGIiBiZWdpbj0iMXMiIGR1cj0iNXMiIC
8+CjwvY2lyY2xlPgo8L2RlZnM+Cjx1c2UgeGxpbms6aHJlZj0iI3Rlc3QiLz4KPC9zdmc+#test"/>
</svg>',
                'base64'
            ]
        ];
    }

    /**
     * @dataProvider maliciousSVGProvider
     */
    public function testMaliciousSVG(string $malicious_svg, string $type) : void
    {
        $preProcessor = new SVGBlacklistPreProcessor('The SVG file contains malicious code.');
        $stream = Streams::ofString($malicious_svg);
        $metadata = new Metadata('test.svg', 100, 'image/svg+xml');

        $result = $preProcessor->process($stream, $metadata);

        $this->assertFalse($result->getCode() === ProcessingStatus::OK);
        $this->assertTrue($result->getCode() === ProcessingStatus::DENIED);
        $this->assertSame('The SVG file contains malicious code. (' . $type . ').', $result->getMessage());
    }

    public function testSaneSVG() : void
    {
        $svg = '<svg version="1.1" baseProfile="full"
xmlns="http://www.w3.org/2000/svg">
<rect width="100" height="100" style="fill:rgb(0,0,255);" />
</svg>';

        $preProcessor = new SVGBlacklistPreProcessor('The SVG file contains possibily malicious code.');
        $stream = Streams::ofString($svg);
        $metadata = new Metadata('test.svg', 100, 'image/svg+xml');

        $result = $preProcessor->process($stream, $metadata);

        $this->assertTrue($result->getCode() === ProcessingStatus::OK);
        $this->assertFalse($result->getCode() === ProcessingStatus::REJECTED);
        $this->assertSame('SVG OK', $result->getMessage());
    }

    public function provideSomeComplexSaneSVG() : array
    {
        return [
            ['./templates/default/images/bigplay.svg'],
            ['./templates/default/images/jstree.svg'],
            ['./templates/default/images/loader.svg'],
            ['./templates/default/images/col.svg'],
            ['./templates/default/images/HeaderIcon.svg'],
            ['./templates/default/images/answered_not.svg'],
        ];
    }

    /**
     * @dataProvider provideSomeComplexSaneSVG
     */
    public function testSomeComplexSaneSVG(string $path) : void
    {
        $this->assertTrue(file_exists($path));
        $svg = file_get_contents($path);

        $preProcessor = new SVGBlacklistPreProcessor('The SVG file contains possibily malicious code.');
        $stream = Streams::ofString($svg);
        $metadata = new Metadata('bigplay.svg', 100, 'image/svg+xml');

        $result = $preProcessor->process($stream, $metadata);

        $this->assertSame('SVG OK', $result->getMessage());
        $this->assertTrue($result->getCode() === ProcessingStatus::OK);
        $this->assertFalse($result->getCode() === ProcessingStatus::REJECTED);
    }
}
