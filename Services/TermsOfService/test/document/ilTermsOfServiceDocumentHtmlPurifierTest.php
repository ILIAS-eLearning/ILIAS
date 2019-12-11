<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use org\bovigo\vfs;

/**
* Class ilTermsOfServiceDocumentHtmlPurifierTest
* @author Michael Jansen <mjansen@databay.de>
*/
class ilTermsOfServiceDocumentHtmlPurifierTest extends \ilTermsOfServiceCriterionBaseTest
{
    /**
     * @return bool
     */
    private function isVsfStreamInstalled() : bool
    {
        return class_exists('org\bovigo\vfs\vfsStreamWrapper');
    }

    /**
     *
     */
    private function skipIfvfsStreamNotSupported()
    {
        if (!$this->isVsfStreamInstalled()) {
            $this->markTestSkipped('Skipped test, vfsStream (http://vfs.bovigo.org) required');
        }
    }

    /**
     * @return array
     */
    public function documentTextProvider() : array
    {
        return [
            [
                '<h1><b>This</b> <i>is</i> <u>a</u> <em>Headline</em>!</h1><p>And a<br>paragraph.</p>',
                '<h1><b>This</b> <i>is</i> <span style="text-decoration:underline;">a</span> <em>Headline</em>!</h1><p>And a<br />paragraph.</p>',
            ],
            [
                '<h1>This is a <a href="mailto:info@ilias.de">Headline</a>!</h1><p>And a paragraph with an invalid element: ILIAS e.V. <info@ilias.de>.</p>',
                '<h1>This is a <a href="mailto:info@ilias.de">Headline</a>!</h1><p>And a paragraph with an invalid element: ILIAS e.V. .</p>',
            ],
            [
                '<div><ul><li>Php</li></ul><hr><ol><li>Unit</li></ol><dl><dt>Test</dt><dd><code>Success or Failure!</code></dd></dl></div>',
                '<div><ul><li>Php</li></ul><hr /><ol><li>Unit</li></ol><dl><dt>Test</dt><dd><code>Success or Failure!</code></dd></dl></div>',
            ],
            [
                '<pre>Text</pre><blockquote><cite><sup>Q</sup>uote</cite></blockquote>',
                '<pre>Text</pre><blockquote><p><cite><sup>Q</sup>uote</cite></p></blockquote>',
            ]
        ];
    }

    /**
     * @dataProvider documentTextProvider
     * @param string $text
     * @param string $expected
     * @throws vfs\vfsStreamException
     */
    public function testPurifyingWorksAsExpected(string $text, string $expected)
    {
        $this->skipIfvfsStreamNotSupported();

        vfs\vfsStreamWrapper::register();
        $root  = vfs\vfsStreamWrapper::setRoot(new vfs\vfsStreamDirectory('root'));
        $cacheDirectory = vfs\vfsStream::newDirectory('HTMLPurifier')->at($root);
        $cacheDirectory->chmod(0777);

        $purifier = new \ilTermsOfServiceDocumentHtmlPurifier(
            [
                "a", "blockquote", "br", "cite", "code", "dd", "div", "dl", "dt", "em", "h1", "h2", "h3", "h4", "h5",
                "h6", "hr", "img", "li", "ol", "p", "pre", "span", "strong", "sub", "sup", "u", "ul"
            ],
            vfs\vfsStream::url('root/HTMLPurifier')
        );
        $this->assertEquals($expected, $purifier->purify($text));
    }
}
