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

require_once("vendor/composer/vendor/autoload.php");

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
