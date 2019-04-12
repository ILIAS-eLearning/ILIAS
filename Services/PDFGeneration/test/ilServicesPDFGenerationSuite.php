<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

require_once 'libs/composer/vendor/autoload.php';

/**
 * Class ilPDFGenerationSuite
 * @package ilPdfGenerator
 */
class ilServicesPDFGenerationSuite extends TestSuite
{
	/**
	 * @return self
	 */
	public static function suite()
	{
		$suite = new self();

		require_once __DIR__ . '/ilPdfGeneratorConstantsTest.php';
		$suite->addTestSuite('ilPdfGeneratorConstantsTest');

		require_once __DIR__ . '/ilPhantomJSRendererTest.php';
		$suite->addTestSuite('ilPhantomJSRendererTest');

		require_once __DIR__ . '/ilPhantomJSRendererUiTest.php';
		$suite->addTestSuite('ilPhantomJSRendererUiTest');

		return $suite;
	}
} 