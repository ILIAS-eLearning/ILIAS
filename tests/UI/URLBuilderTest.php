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

namespace ILIAS\UI;

use PHPUnit\Framework\TestCase;

class URLBuilderTest extends TestCase
{
    private \ILIAS\Data\URI $URI_COMPLETE;

    protected function setUp(): void
    {
        $this->URI_COMPLETE = new \ILIAS\Data\URI('https://www.ilias.de/foo/bar?var1=42&var2=foo#12345');
    }

    public function testGetUrl(): void
    {
        $url = new URLBuilder($this->URI_COMPLETE);
        $this->assertInstanceOf(\ILIAS\Data\URI::class, $url->buildURI());
        $this->assertEquals('https://www.ilias.de/foo/bar?var1=42&var2=foo#12345', (string) $url->buildURI());
    }

    public function testAcquireParam(): void
    {
        $url = new URLBuilder($this->URI_COMPLETE);
        $result = $url->acquireParameter(['test'], 'title');
        $this->assertInstanceOf(URLBuilder::class, $result[0]);
        $this->assertEquals(
            'https://www.ilias.de/foo/bar?var1=42&var2=foo&test' . URLBuilder::SEPARATOR . 'title=#12345',
            (string) $result[0]->buildURI()
        );
        $this->assertInstanceOf(URLBuilderToken::class, $result[1]);
        $this->assertEquals('test' . URLBuilder::SEPARATOR . 'title', $result[1]->getName());
        $this->assertNotEmpty($result[1]->getToken());
    }

    public function testAcquireParamWithLongNamespace(): void
    {
        $url = new URLBuilder($this->URI_COMPLETE);
        $result = $url->acquireParameter(['test', 'object', 'metadata'], 'title');
        $this->assertInstanceOf(URLBuilder::class, $result[0]);
        $this->assertEquals(
            'https://www.ilias.de/foo/bar?var1=42&var2=foo&test' . URLBuilder::SEPARATOR .
            'object' . URLBuilder::SEPARATOR .
            'metadata' . URLBuilder::SEPARATOR .
            'title=#12345',
            (string) $result[0]->buildURI()
        );
        $this->assertInstanceOf(URLBuilderToken::class, $result[1]);
        $this->assertEquals(
            'test' . URLBuilder::SEPARATOR .
            'object' . URLBuilder::SEPARATOR .
            'metadata' . URLBuilder::SEPARATOR .
            'title',
            $result[1]->getName()
        );
        $this->assertNotEmpty($result[1]->getToken());
    }

    public function testAcquireParamWithValue(): void
    {
        $url = new URLBuilder($this->URI_COMPLETE);
        $sep = URLBuilder::SEPARATOR;
        $result = $url->acquireParameter(['test'], 'title', 'bar');
        $this->assertInstanceOf(URLBuilder::class, $result[0]);
        $this->assertEquals(
            'https://www.ilias.de/foo/bar?var1=42&var2=foo&test' . $sep . 'title=bar#12345',
            (string) $result[0]->buildURI()
        );
        $this->assertInstanceOf(URLBuilderToken::class, $result[1]);
        $this->assertEquals('test' . $sep . 'title', $result[1]->getName());
        $this->assertNotEmpty($result[1]->getToken());
    }

    public function testAcquireParamWithSameName(): void
    {
        $url = new URLBuilder($this->URI_COMPLETE);
        $sep = URLBuilder::SEPARATOR;
        $result = $url->acquireParameter(['test'], 'title', 'foo');
        $this->assertEquals(
            'https://www.ilias.de/foo/bar?var1=42&var2=foo&test' . $sep . 'title=foo#12345',
            (string) $result[0]->buildURI()
        );
        $this->assertEquals('test' . $sep . 'title', $result[1]->getName());

        $result2 = $result[0]->acquireParameter(['notatest'], 'title', 'bar');
        $this->assertEquals(
            'https://www.ilias.de/foo/bar?var1=42&var2=foo&test' . $sep . 'title=foo&notatest' . $sep . 'title=bar#12345',
            (string) $result2[0]->buildURI()
        );
        $this->assertEquals('notatest' . $sep . 'title', $result2[1]->getName());
        $this->assertNotEquals($result[1]->getToken(), $result2[1]->getToken());
    }

    public function testWriteParam(): void
    {
        $url = new URLBuilder($this->URI_COMPLETE);
        $sep = URLBuilder::SEPARATOR;
        $result = $url->acquireParameter(['test'], 'title', 'bar');
        $this->assertEquals(
            'https://www.ilias.de/foo/bar?var1=42&var2=foo&test' . $sep . 'title=bar#12345',
            (string) $result[0]->buildURI()
        );

        $url = $result[0]->withParameter($result[1], 'foobar');
        $this->assertInstanceOf(URLBuilder::class, $url);
        $this->assertEquals(
            'https://www.ilias.de/foo/bar?var1=42&var2=foo&test' . $sep . 'title=foobar#12345',
            (string) $url->buildURI()
        );

        $url = $result[0]->withParameter($result[1], ['foo', 'bar']);
        $this->assertInstanceOf(URLBuilder::class, $url);
        $this->assertEquals(
            'https://www.ilias.de/foo/bar?var1=42&var2=foo'
                . '&test' . $sep . urlencode('title[]') . '=foo'
                . '&test' . $sep . urlencode('title[]') . '=bar'
                . '#12345',
            (string) $url->buildURI()
        );
    }

    public function testDeleteParam(): void
    {
        $url = new URLBuilder($this->URI_COMPLETE);
        $sep = URLBuilder::SEPARATOR;
        $result = $url->acquireParameter(['test'], 'title', 'bar');
        $this->assertEquals(
            'https://www.ilias.de/foo/bar?var1=42&var2=foo&test' . $sep . 'title=bar#12345',
            (string) $result[0]->buildURI()
        );

        $url = $result[0]->deleteParameter($result[1]);
        $this->assertEquals('https://www.ilias.de/foo/bar?var1=42&var2=foo#12345', (string) $url->buildURI());
    }

    public function testUrlTooLong(): void
    {
        $url = new URLBuilder($this->URI_COMPLETE);
        $result = $url->acquireParameter(['test'], 'title', random_bytes(URLBuilder::URL_MAX_LENGTH));
        $this->expectException(\LengthException::class);
        $output = $result[0]->buildURI();
    }

    public function testRemoveAndAddFragment(): void
    {
        $url = new URLBuilder($this->URI_COMPLETE);
        $url = $url->withFragment(''); // set fragment to empty
        $this->assertInstanceOf(URLBuilder::class, $url);
        $this->assertEquals('https://www.ilias.de/foo/bar?var1=42&var2=foo', (string) $url->buildURI());
        $url = $url->withFragment(null); // unset fragment, use fragment from base URL if present
        $this->assertInstanceOf(URLBuilder::class, $url);
        $this->assertEquals('https://www.ilias.de/foo/bar?var1=42&var2=foo#12345', (string) $url->buildURI());
        $url = $url->withFragment('54321'); // set fragment to value
        $this->assertInstanceOf(URLBuilder::class, $url);
        $this->assertEquals('https://www.ilias.de/foo/bar?var1=42&var2=foo#54321', (string) $url->buildURI());
    }

    public function testWithUri(): void
    {
        $url = new URLBuilder($this->URI_COMPLETE);
        $result = $url->acquireParameter(['test'], 'title', 'bar');
        $url = $result[0]->withURI(
            new \ILIAS\Data\URI('http://test.ilias.de/bar/foo?test' . URLBuilder::SEPARATOR . 'title=foo&var1=46#12345')
        );
        $this->assertEquals(
            'http://test.ilias.de/bar/foo?test' . URLBuilder::SEPARATOR . 'title=bar&var1=46#12345',
            (string) $url->buildURI()
        );
    }

    public function testRenderTokens(): void
    {
        $url = new URLBuilder($this->URI_COMPLETE);

        // One parameter
        $result1 = $url->acquireParameter(['test', 'object'], 'title', 'bar');
        $url = $result1[0];
        $expected_token = 'new Map([["' . $result1[1]->getName() . '",'
            . 'new il.UI.core.URLBuilderToken(["test","object"], "title", "' . $result1[1]->getToken() . '")]])';
        $this->assertEquals($expected_token, $url->renderTokens([$result1[1]]));

        // Two parameters, but just rendered with one
        $result2 = $url->acquireParameter(['test'], 'description', 'foo');
        $url = $result2[0];
        $this->assertEquals($expected_token, $url->renderTokens([$result1[1]]));

        // Two parameters with full render
        $expected_token = 'new Map([["' . $result1[1]->getName() . '",'
            . 'new il.UI.core.URLBuilderToken(["test","object"], "title", "' . $result1[1]->getToken() . '")],'
            . '["' . $result2[1]->getName() . '",'
            . 'new il.UI.core.URLBuilderToken(["test"], "description", "' . $result2[1]->getToken() . '")]])';
        $this->assertEquals($expected_token, $url->renderTokens([$result1[1], $result2[1]]));
    }

    public function testRenderObject(): void
    {
        $url = new URLBuilder($this->URI_COMPLETE);

        // One parameter
        $result1 = $url->acquireParameter(['test', 'object'], 'title', 'bar');
        $url = $result1[0];
        $expected_token = 'new Map([["' . $result1[1]->getName() . '",'
            . 'new il.UI.core.URLBuilderToken(["test","object"], "title", "' . $result1[1]->getToken() . '")]])';
        $expected_object = 'new il.UI.core.URLBuilder(new URL("https://www.ilias.de/foo/bar?var1=42&var2=foo&'
            . 'test' . URLBuilder::SEPARATOR . 'object' . URLBuilder::SEPARATOR . 'title=bar#12345"), ' . $expected_token . ')';
        $this->assertEquals($expected_object, $url->renderObject([$result1[1]]));

        // Two parameters, but just rendered with one
        $result2 = $url->acquireParameter(['test'], 'description', 'foo');
        $url = $result2[0];
        $expected_object = 'new il.UI.core.URLBuilder(new URL("https://www.ilias.de/foo/bar?var1=42&var2=foo&'
            . 'test' . URLBuilder::SEPARATOR . 'object' . URLBuilder::SEPARATOR . 'title=bar&'
            . 'test' . URLBuilder::SEPARATOR . 'description=foo#12345"), ' . $expected_token . ')';
        $this->assertEquals($expected_object, $url->renderObject([$result1[1]]));

        // Two parameters with full render
        $expected_token = 'new Map([["' . $result1[1]->getName() . '",'
            . 'new il.UI.core.URLBuilderToken(["test","object"], "title", "' . $result1[1]->getToken() . '")],'
            . '["' . $result2[1]->getName() . '",'
            . 'new il.UI.core.URLBuilderToken(["test"], "description", "' . $result2[1]->getToken() . '")]])';
        $expected_object = 'new il.UI.core.URLBuilder(new URL("https://www.ilias.de/foo/bar?var1=42&var2=foo&'
            . 'test' . URLBuilder::SEPARATOR . 'object' . URLBuilder::SEPARATOR . 'title=bar&'
            . 'test' . URLBuilder::SEPARATOR . 'description=foo#12345"), ' . $expected_token . ')';
        $this->assertEquals($expected_object, $url->renderObject([$result1[1], $result2[1]]));
    }
}
