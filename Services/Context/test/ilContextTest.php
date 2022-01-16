<?php
use PHPUnit\Framework\TestCase;

/**
 * TestCase for the ilContext
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @version 1.0.0
 */
class ilContextTest extends TestCase
{
    protected $backupGlobals = false;

    protected function setUp() : void
    {
        require_once("Services/Context/test/class.ilContextExtended.php");
    }
    
    /**
    * test init ilContext
    *
    * @dataProvider contextProvider
    */
    public function testInit($context, $className)
    {
        $context_obj = ilContextExtended::init($context);
        $this->assertTrue($context_obj);
        $this->assertEquals(ilContextExtended::getType(), $context);
        $this->assertEquals(ilContextExtended::getClassName(), $className);
    }

    public function contextProvider()
    {
        require_once("Services/Context/test/class.ilContextExtended.php");

        return array(array(ilContext::CONTEXT_WEB, "ilContextWeb"),
                     array(ilContext::CONTEXT_CRON, "ilContextCron"),
                     array(ilContext::CONTEXT_RSS, "ilContextRss"),
                     array(ilContext::CONTEXT_ICAL, "ilContextIcal"),
                     array(ilContext::CONTEXT_SOAP, "ilContextSoap"),
                     array(ilContext::CONTEXT_WEBDAV, "ilContextWebdav"),
                     array(ilContext::CONTEXT_RSS_AUTH, "ilContextRssAuth"),
                     array(ilContext::CONTEXT_SESSION_REMINDER, "ilContextSessionReminder"),
                     array(ilContext::CONTEXT_SOAP_WITHOUT_CLIENT, "ilContextSoapWithoutClient"),
                     array(ilContext::CONTEXT_UNITTEST, "ilContextUnitTest"),
                     array(ilContext::CONTEXT_REST, "ilContextRest"),
                     array(ilContext::CONTEXT_SCORM, "ilContextScorm"),
                     array(ilContext::CONTEXT_WAC, "ilContextWAC"));
    }
}
