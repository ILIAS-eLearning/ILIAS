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

use org\bovigo\vfs;

/**
 * Class ilTermsOfServiceDocumentHtmlPurifierTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentHtmlPurifierTest extends ilTermsOfServiceCriterionBaseTest
{
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

    public function documentTextProvider(): array
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

    /**
     * @dataProvider documentTextProvider
     * @param string $text
     * @param string $expected
     */
    public function testPurifyingWorksAsExpected(string $text, string $expected): void
    {
        $this->skipIfvfsStreamNotSupported();

        vfs\vfsStreamWrapper::register();
        $root = vfs\vfsStreamWrapper::setRoot(new vfs\vfsStreamDirectory('root'));
        $cacheDirectory = vfs\vfsStream::newDirectory('HTMLPurifier')->at($root);
        $cacheDirectory->chmod(0777);

        $purifier = new ilTermsOfServiceDocumentHtmlPurifier(
            [
                'a',
                'blockquote',
                'br',
                'cite',
                'code',
                'dd',
                'div',
                'dl',
                'dt',
                'em',
                'h1',
                'h2',
                'h3',
                'h4',
                'h5',
                'h6',
                'hr',
                'img',
                'li',
                'ol',
                'p',
                'pre',
                'span',
                'strong',
                'sub',
                'sup',
                'u',
                'ul'
            ],
            vfs\vfsStream::url('root/HTMLPurifier')
        );
        $this->assertSame($expected, $purifier->purify($text));
    }
}
