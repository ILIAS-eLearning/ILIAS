<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilPDFGenerationSuite
 * @package ilPdfGenerator
 */
class ilServicesPDFGenerationSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * @return self
     */
    public static function suite()
    {
        if (defined('ILIAS_PHPUNIT_CONTEXT')) {
            include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
            ilUnitUtil::performInitialisation();
        } else {
            chdir(__DIR__);
            chdir('../../../');
        }

        // Set timezone to prevent notices
        date_default_timezone_set('Europe/Berlin');

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
