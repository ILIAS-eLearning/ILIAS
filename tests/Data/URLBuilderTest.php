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

use ILIAS\Data\URLBuilder;
use ILIAS\Data\URLBuilderToken;
use PHPUnit\Framework\TestCase;

class URLBuilderTest extends TestCase
{
    private const URI_COMPLETE = 'https://www.ilias.de/foo/bar?var1=42&var2=foo#12345';

    public function test_get_url(): void
    {
        $url = new ILIAS\Data\URLBuilder(self::URI_COMPLETE);
        $this->assertEquals('https://www.ilias.de/foo/bar?var1=42&var2=foo#12345', $url->getUrl());
    }

    public function test_acquire_param(): void
    {
        $url = new URLBuilder(self::URI_COMPLETE);
        $result = $url->acquireParameter(['test'], 'title');
        $this->assertInstanceOf(URLBuilder::class, $result["url"]);
        $this->assertEquals(
            'https://www.ilias.de/foo/bar?var1=42&var2=foo&test' . URLBuilder::SEPARATOR . 'title=#12345',
            $result["url"]->getUrl()
        );
        $this->assertInstanceOf(URLBuilderToken::class, $result["token"]);
        $this->assertEquals('test' . URLBuilder::SEPARATOR . 'title', $result["token"]->getName());
        $this->assertNotEmpty($result["token"]->getToken());
    }

    public function test_acquire_param_with_long_namespace(): void
    {
        $url = new URLBuilder(self::URI_COMPLETE);
        $result = $url->acquireParameter(['test', 'object', 'metadata'], 'title');
        $this->assertInstanceOf(URLBuilder::class, $result["url"]);
        $this->assertEquals(
            'https://www.ilias.de/foo/bar?var1=42&var2=foo&test' . URLBuilder::SEPARATOR .
            'object' . URLBuilder::SEPARATOR .
            'metadata' . URLBuilder::SEPARATOR .
            'title=#12345',
            $result["url"]->getUrl()
        );
        $this->assertInstanceOf(URLBuilderToken::class, $result["token"]);
        $this->assertEquals(
            'test' . URLBuilder::SEPARATOR .
            'object' . URLBuilder::SEPARATOR .
            'metadata' . URLBuilder::SEPARATOR .
            'title',
            $result["token"]->getName()
        );
        $this->assertNotEmpty($result["token"]->getToken());
    }

    public function test_acquire_param_with_value(): void
    {
        $url = new URLBuilder(self::URI_COMPLETE);
        $sep = URLBuilder::SEPARATOR;
        $result = $url->acquireParameter(['test'], 'title', 'bar');
        $this->assertInstanceOf(URLBuilder::class, $result["url"]);
        $this->assertEquals(
            'https://www.ilias.de/foo/bar?var1=42&var2=foo&test' . $sep . 'title=bar#12345',
            $result["url"]->getUrl()
        );
        $this->assertInstanceOf(URLBuilderToken::class, $result["token"]);
        $this->assertEquals('test' . $sep . 'title', $result["token"]->getName());
        $this->assertNotEmpty($result["token"]->getToken());
    }

    public function test_acquire_param_with_same_name(): void
    {
        $url = new URLBuilder(self::URI_COMPLETE);
        $sep = URLBuilder::SEPARATOR;
        $result = $url->acquireParameter(['test'], 'title', 'foo');
        $this->assertEquals(
            'https://www.ilias.de/foo/bar?var1=42&var2=foo&test' . $sep . 'title=foo#12345',
            $result["url"]->getUrl()
        );
        $this->assertEquals('test' . $sep . 'title', $result["token"]->getName());

        $result2 = $result["url"]->acquireParameter(['notatest'], 'title', 'bar');
        $this->assertEquals(
            'https://www.ilias.de/foo/bar?var1=42&var2=foo&test' . $sep . 'title=foo&notatest' . $sep . 'title=bar#12345',
            $result2["url"]->getUrl()
        );
        $this->assertEquals('notatest' . $sep . 'title', $result2["token"]->getName());
        $this->assertNotEquals($result["token"]->getToken(), $result2["token"]->getToken());
    }

    public function test_write_param(): void
    {
        $url = new URLBuilder(self::URI_COMPLETE);
        $sep = URLBuilder::SEPARATOR;
        $result = $url->acquireParameter(['test'], 'title', 'bar');
        $this->assertEquals(
            'https://www.ilias.de/foo/bar?var1=42&var2=foo&test' . $sep . 'title=bar#12345',
            $result["url"]->getUrl()
        );

        $url = $result["url"]->writeParameter($result["token"], 'foobar');
        $this->assertInstanceOf(URLBuilder::class, $url);
        $this->assertEquals(
            'https://www.ilias.de/foo/bar?var1=42&var2=foo&test' . $sep . 'title=foobar#12345',
            $url->getUrl()
        );
    }

    public function test_delete_param(): void
    {
        $url = new URLBuilder(self::URI_COMPLETE);
        $sep = URLBuilder::SEPARATOR;
        $result = $url->acquireParameter(['test'], 'title', 'bar');
        $this->assertEquals(
            'https://www.ilias.de/foo/bar?var1=42&var2=foo&test' . $sep . 'title=bar#12345',
            $result["url"]->getUrl()
        );

        $url = $result["url"]->deleteParameter($result["token"]);
        $this->assertEquals('https://www.ilias.de/foo/bar?var1=42&var2=foo#12345', $url->getUrl());
    }

    public function test_url_too_long(): void
    {
        $url = new URLBuilder(self::URI_COMPLETE);
        $this->expectException(\ilException::class);
        $result = $url->acquireParameter(['test'], 'title', random_bytes(URLBuilder::URL_MAX_LENGTH));
    }

    public function test_remove_and_add_fragment(): void
    {
        $url = new URLBuilder(self::URI_COMPLETE);
        $url = $url->withFragment(null);
        $this->assertInstanceOf(URLBuilder::class, $url);
        $this->assertEquals('https://www.ilias.de/foo/bar?var1=42&var2=foo', $url->getUrl());
        $url = $url->withFragment('12345');
        $this->assertInstanceOf(URLBuilder::class, $url);
        $this->assertEquals('https://www.ilias.de/foo/bar?var1=42&var2=foo#12345', $url->getUrl());
    }

    public function test_change_fragment(): void
    {
        $url = new URLBuilder(self::URI_COMPLETE);
        $url = $url->withFragment('54321');
        $this->assertInstanceOf(URLBuilder::class, $url);
        $this->assertEquals('https://www.ilias.de/foo/bar?var1=42&var2=foo#54321', $url->getUrl());
    }

    public function test_render_tokens(): void
    {
        $url = new URLBuilder(self::URI_COMPLETE);

        // One parameter
        $result1 = $url->acquireParameter(['test', 'object'], 'title', 'bar');
        $url = $result1["url"];
        $expected_token = 'new Map([["' . $result1["token"]->getName() . '",'
        . 'new il.UI.core.URLBuilderToken(["test","object"], "title", "' . $result1["token"]->getToken() . '")]])';
        $this->assertEquals($expected_token, $url->renderTokens([$result1["token"]]));

        // Two parameters, but just rendered with one
        $result2 = $url->acquireParameter(['test'], 'description', 'foo');
        $url = $result2["url"];
        $this->assertEquals($expected_token, $url->renderTokens([$result1["token"]]));

        // Two parameters with full render
        $expected_token = 'new Map([["' . $result1["token"]->getName() . '",'
            . 'new il.UI.core.URLBuilderToken(["test","object"], "title", "' . $result1["token"]->getToken() . '")],'
            . '["' . $result2["token"]->getName() . '",'
            . 'new il.UI.core.URLBuilderToken(["test"], "description", "' . $result2["token"]->getToken() . '")]])';
        $this->assertEquals($expected_token, $url->renderTokens([$result1["token"], $result2["token"]]));
    }

    public function test_render_object(): void
    {
        $url = new URLBuilder(self::URI_COMPLETE);

        // One parameter
        $result1 = $url->acquireParameter(['test', 'object'], 'title', 'bar');
        $url = $result1["url"];
        $expected_token = 'new Map([["' . $result1["token"]->getName() . '",'
            . 'new il.UI.core.URLBuilderToken(["test","object"], "title", "' . $result1["token"]->getToken() . '")]])';
        $expected_object = 'new il.UI.core.URLBuilder("https://www.ilias.de/foo/bar?var1=42&var2=foo&'
        . 'test' . URLBuilder::SEPARATOR . 'object' . URLBuilder::SEPARATOR . 'title=bar#12345", ' . $expected_token . ')';
        $this->assertEquals($expected_object, $url->renderObject([$result1["token"]]));

        // Two parameters, but just rendered with one
        $result2 = $url->acquireParameter(['test'], 'description', 'foo');
        $url = $result2["url"];
        $expected_object = 'new il.UI.core.URLBuilder("https://www.ilias.de/foo/bar?var1=42&var2=foo&'
        . 'test' . URLBuilder::SEPARATOR . 'object' . URLBuilder::SEPARATOR . 'title=bar&'
        . 'test' . URLBuilder::SEPARATOR . 'description=foo#12345", ' . $expected_token . ')';
        $this->assertEquals($expected_object, $url->renderObject([$result1["token"]]));

        // Two parameters with full render
        $expected_token = 'new Map([["' . $result1["token"]->getName() . '",'
            . 'new il.UI.core.URLBuilderToken(["test","object"], "title", "' . $result1["token"]->getToken() . '")],'
            . '["' . $result2["token"]->getName() . '",'
            . 'new il.UI.core.URLBuilderToken(["test"], "description", "' . $result2["token"]->getToken() . '")]])';
        $expected_object = 'new il.UI.core.URLBuilder("https://www.ilias.de/foo/bar?var1=42&var2=foo&'
        . 'test' . URLBuilder::SEPARATOR . 'object' . URLBuilder::SEPARATOR . 'title=bar&'
        . 'test' . URLBuilder::SEPARATOR . 'description=foo#12345", ' . $expected_token . ')';
        $this->assertEquals($expected_object, $url->renderObject([$result1["token"], $result2["token"]]));
    }
}
