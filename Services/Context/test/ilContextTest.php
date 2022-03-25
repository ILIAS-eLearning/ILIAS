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
    protected function setUp() : void
    {
        require_once("Services/Context/test/class.ilContextExtended.php");
    }

    /**
     * @dataProvider contextProvider
     */
    public function testInit(string $context, string $className) : void
    {
        $context_obj = ilContextExtended::init($context);
        $this->assertTrue($context_obj);
        $this->assertEquals(ilContextExtended::getType(), $context);
        $this->assertEquals(ilContextExtended::getClassName(), $className);
    }

    public function contextProvider() : array
    {
        return [
            [ilContext::CONTEXT_WEB, ilContextWeb::class],
            [ilContext::CONTEXT_CRON, ilContextCron::class],
            [ilContext::CONTEXT_RSS, ilContextRss::class],
            [ilContext::CONTEXT_ICAL, ilContextIcal::class],
            [ilContext::CONTEXT_SOAP, ilContextSoap::class],
            [ilContext::CONTEXT_WEBDAV, ilContextWebdav::class],
            [ilContext::CONTEXT_RSS_AUTH, ilContextRssAuth::class],
            [ilContext::CONTEXT_SESSION_REMINDER, ilContextSessionReminder::class],
            [ilContext::CONTEXT_SOAP_WITHOUT_CLIENT, ilContextSoapWithoutClient::class],
            [ilContext::CONTEXT_UNITTEST, ilContextUnitTest::class],
            [ilContext::CONTEXT_REST, ilContextRest::class],
            [ilContext::CONTEXT_SCORM, ilContextScorm::class],
            [ilContext::CONTEXT_WAC, ilContextWAC::class],
        ];
    }
}
