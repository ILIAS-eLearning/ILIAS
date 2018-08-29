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
	private function isVsfStreamInstalled(): bool
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
	public function documentTextProvider(): array
	{
		return [
			['<h1>This is a Headline!</h1><p>And a paragraph.</p>', '<h1>This is a Headline!</h1><p>And a paragraph.</p>', ],
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
				"h6", "hr", "img", "li", "ol", "p", "pre", "span", "strike", "strong", "sub", "sup", "u", "ul"
			],
			vfs\vfsStream::url('root/HTMLPurifier')
		);
		$this->assertEquals($expected, $purifier->purify($text));
	}
}