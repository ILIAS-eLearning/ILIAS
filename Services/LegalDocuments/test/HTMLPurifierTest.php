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

namespace ILIAS\LegalDocuments\test;

use ILIAS\LegalDocuments\HTMLPurifier;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

class HTMLPurifierTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->skipIfvfsStreamNotSupported();
        vfsStreamWrapper::register();
        $root = vfsStreamWrapper::setRoot(new vfsStreamDirectory('root'));
        $cache_directory = vfsStream::newDirectory('HTMLPurifier')->at($root);
        $cache_directory->chmod(0777);

        $this->assertInstanceOf(HTMLPurifier::class, new HTMLPurifier([], vfsStream::url('root/HTMLPurifier')));
    }

    /**
     * @dataProvider documents
     */
    public function testPurify(string $input, string $expected): void
    {
        $this->skipIfvfsStreamNotSupported();
        vfsStreamWrapper::register();
        $root = vfsStreamWrapper::setRoot(new vfsStreamDirectory('root'));
        $cache_directory = vfsStream::newDirectory('HTMLPurifier')->at($root);
        $cache_directory->chmod(0777);

        $instance = new HTMLPurifier([
                'a', 'blockquote', 'br', 'cite', 'code', 'dd', 'div', 'dl', 'dt', 'em', 'h1', 'h2', 'h3', 'h4',
                'h5', 'h6', 'hr', 'img', 'li', 'ol', 'p', 'pre', 'span', 'strong', 'sub', 'sup', 'u', 'ul',
            ], vfsStream::url('root/HTMLPurifier'));

        $this->assertSame($expected, $instance->purify($input));
    }

    public function documents(): array
    {
        return [
            'Simple HTML Elements' => [
                '<h1><b>This</b> <i>is</i> <u>a</u> <em>Headline</em>!</h1><p>And a<br>paragraph.</p>',
                '<h1><b>This</b> <i>is</i> <span style="text-decoration:underline;">a</span> <em>Headline</em>!</h1><p>And a<br />paragraph.</p>',
            ],
            'Simple HTML Elements with an Invalid Tag' => [
                '<h1>This is a <a href="mailto:info@ilias.de">Headline</a>!</h1><p>And a paragraph with an invalid element: ILIAS e.V. <info@ilias.de>.</p>',
                '<h1>This is a <a href="mailto:info@ilias.de">Headline</a>!</h1><p>And a paragraph with an invalid element: ILIAS e.V. .</p>',
            ],
            'Block Elements and Nested Lists' => [
                '<div><ul><li>Php</li></ul><hr><ol><li>Unit</li></ol><dl><dt>Test</dt><dd><code>Success or Failure!</code></dd></dl></div>',
                '<div><ul><li>Php</li></ul><hr /><ol><li>Unit</li></ol><dl><dt>Test</dt><dd><code>Success or Failure!</code></dd></dl></div>',
            ],
            'Blockquote' => [
                '<pre>Text</pre><blockquote><cite><sup>Q</sup>uote</cite></blockquote>',
                '<pre>Text</pre><blockquote><p><cite><sup>Q</sup>uote</cite></p></blockquote>',
            ]
        ];
    }

    private function isVsfStreamInstalled(): bool
    {
        return class_exists('org\bovigo\vfs\vfsStreamWrapper');
    }

    private function skipIfvfsStreamNotSupported(): void
    {
        if (!$this->isVsfStreamInstalled()) {
            $this->markTestSkipped('Skipped test, vfsStream (https://github.com/bovigo/vfsStream) required');
        }
    }
}
