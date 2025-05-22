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

/**
 * Class CookieWrapperTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */

namespace ILIAS\HTTP\Cookies;

use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\BackupStaticProperties;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Class CookieWrapperTest
 *
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 */
#[BackupGlobals(false)]
#[BackupStaticProperties(false)]
#[PreserveGlobalState(false)]
class CookieWrapperTest extends TestCase
{
    /**
     * @var CookieWrapper $cookie
     */
    private Cookie $cookie;
    private static CookieFactoryImpl $cookieFactory;


    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$cookieFactory = new CookieFactoryImpl();
    }


    protected function setUp(): void
    {
        parent::setUp();

        //setup the cookie we want to use for our tests.
        $cookieName = "ilias";
        $cookieValue = "theNewCookiesAreYummy";
        $this->cookie = self::$cookieFactory->create($cookieName, $cookieValue);
    }


    #[Test]
    public function testWithValueDoesNotChangeTheCurrentObject(): void
    {
        $newValue = "yes!";
        $newCookie = $this->cookie->withValue("yes!");
        $this->assertEquals($newValue, $newCookie->getValue());
        $this->assertNotEquals($this->cookie->getValue(), $newCookie->getValue());
    }


    #[Test]
    public function testWithExpiresDoesNotChangeTheCurrentObject(): void
    {
        $expires = 1000;
        $newCookie = $this->cookie->withExpires($expires);

        $this->assertEquals($expires, $newCookie->getExpires());
        $this->assertNotEquals($this->cookie->getExpires(), $newCookie->getExpires());
    }


    #[Test]
    public function testRememberForeverDoesNotChangeTheCurrentObject(): void
    {
        $newCookie = $this->cookie->rememberForLongTime();

        //remember forever changes the date of expiry so they should differ by quite a bit.
        $this->assertNotEquals($this->cookie->getExpires(), $newCookie->getExpires());
    }


    #[Test]
    public function testExpireDoesNotChangeTheCurrentObject(): void
    {
        $newCookie = $this->cookie->expire();

        //expire changes the date of expiry so they should differ by quite a bit.
        $this->assertNotEquals($this->cookie->getExpires(), $newCookie->getExpires());
    }


    #[Test]
    public function testWithMaxAgeDoesNotChangeTheCurrentObject(): void
    {
        $maxAge = 1000;
        $newCookie = $this->cookie->withMaxAge($maxAge);

        $this->assertEquals($maxAge, $newCookie->getMaxAge());
        $this->assertNotEquals($this->cookie->getMaxAge(), $newCookie->getMaxAge());
    }


    #[Test]
    public function testWithPathDoesNotChangeTheCurrentObject(): void
    {
        $path = '/ilias';
        $newCookie = $this->cookie->withPath($path);

        $this->assertEquals($path, $newCookie->getPath());
        $this->assertNotEquals($this->cookie->getPath(), $newCookie->getPath());
    }


    #[Test]
    public function testWithDomainDoesNotChangeTheCurrentObject(): void
    {
        $domain = 'ilias.de';
        $newCookie = $this->cookie->withDomain($domain);

        $this->assertEquals($domain, $newCookie->getDomain());
        $this->assertNotEquals($this->cookie->getDomain(), $newCookie->getDomain());
    }


    #[Test]
    public function testWithSecureDoesNotChangeTheCurrentObject(): void
    {
        $secure = true;
        $newCookie = $this->cookie->withSecure($secure);

        $this->assertTrue($newCookie->getSecure());
        $this->assertNotEquals($this->cookie->getSecure(), $newCookie->getSecure());
    }


    #[Test]
    public function testWithHttpOnlyDoesNotChangeTheCurrentObject(): void
    {
        $httpOnly = true;
        $newCookie = $this->cookie->withHttpOnly($httpOnly);

        $this->assertTrue($newCookie->getHttpOnly());
        $this->assertNotEquals($this->cookie->getHttpOnly(), $newCookie->getHttpOnly());
    }
}
