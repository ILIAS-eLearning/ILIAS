<?php

require_once("libs/composer/vendor/autoload.php");

use PHPUnit\Framework\TestCase;

class InitHttpServicesTest extends TestCase
{
    /**
     * @var \ILIAS\DI\Container
     */
    protected $dic;

    /**
     * Http services has no additional deps so far to be set up.
     */
    protected function setUp(): void
    {
        $this->dic = new \ILIAS\DI\Container();
    }

    public function testUIFrameworkInitialization(): void
    {
        $this->assertFalse(isset($this->dic['http']));
        $this->assertFalse(isset($this->dic['http.response_sender_strategy']));
        $this->assertFalse(isset($this->dic['http.cookie_jar_factory']));
        $this->assertFalse(isset($this->dic['http.request_factory']));
        $this->assertFalse(isset($this->dic['http.response_factory']));
        (new \InitHttpServices())->init($this->dic);
        $this->assertInstanceOf("ILIAS\HTTP\Services", $this->dic->http());
        $this->assertTrue(isset($this->dic['http']));
        $this->assertTrue(isset($this->dic['http.response_sender_strategy']));
        $this->assertTrue(isset($this->dic['http.cookie_jar_factory']));
        $this->assertTrue(isset($this->dic['http.request_factory']));
        $this->assertTrue(isset($this->dic['http.response_factory']));
    }
}
