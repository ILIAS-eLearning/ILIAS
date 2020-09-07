<?php
/**
 * TestCase for the ilContext
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @version 1.0.0
 */
class ilContextTest extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;

    protected function setUp()
    {
        PHPUnit_Framework_Error_Deprecated::$enabled = false;
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

        return array(array(ilContextExtended::CONTEXT_WEB,"ilContextWeb"),
                    array(ilContextExtended::CONTEXT_CRON,"ilContextCron"),
                    array(ilContextExtended::CONTEXT_RSS,"ilContextRss"),
                    array(ilContextExtended::CONTEXT_ICAL,"ilContextIcal"),
                    array(ilContextExtended::CONTEXT_SOAP,"ilContextSoap"),
                    array(ilContextExtended::CONTEXT_WEBDAV,"ilContextWebdav"),
                    array(ilContextExtended::CONTEXT_RSS_AUTH,"ilContextRssAuth"),
                    array(ilContextExtended::CONTEXT_SESSION_REMINDER,"ilContextSessionReminder"),
                    array(ilContextExtended::CONTEXT_SOAP_WITHOUT_CLIENT,"ilContextSoapWithoutClient"),
                    array(ilContextExtended::CONTEXT_UNITTEST,"ilContextUnitTest"),
                    array(ilContextExtended::CONTEXT_REST,"ilContextRest"),
                    array(ilContextExtended::CONTEXT_SCORM,"ilContextScorm"),
                    array(ilContextExtended::CONTEXT_WAC,"ilContextWAC"));
    }
}
