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

declare(strict_types=1);

namespace ILIAS\HTTP\Request;

use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class RequestFactoryImplTest extends TestCase
{
    private array $server_backup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->server_backup = $_SERVER;

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_HOST'] = 'ilias.example.com';
        $_SERVER['REQUEST_URI'] = '/ilias.php';
        unset($_SERVER['HTTPS']);
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->server_backup;
        parent::tearDown();
    }

    public function testSchemeStaysUntouchedWithoutConfiguration(): void
    {
        $this->assertSame('http', (new RequestFactoryImpl())->create()->getUri()->getScheme());
    }

    public function testSchemeStaysUntouchedIfHeaderDoesNotMatch(): void
    {
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'http';

        $factory = new RequestFactoryImpl('X-Forwarded-Proto', 'https');

        $this->assertSame('http', $factory->create()->getUri()->getScheme());
    }

    public function testSchemeIsHttpsIfHeaderMatches(): void
    {
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

        $factory = new RequestFactoryImpl('X-Forwarded-Proto', 'https');

        $this->assertSame('https', $factory->create()->getUri()->getScheme());
    }

    /**
     * The configured header value describes when https is in use, it is not the
     * protocol itself - see https://mantis.ilias.de/view.php?id=45344
     */
    public function testSchemeIsHttpsForHeaderValuesWhichAreNoProtocol(): void
    {
        $_SERVER['HTTP_FRONT_END_HTTPS'] = 'on';

        $factory = new RequestFactoryImpl('FRONT-END-HTTPS', 'on');

        $this->assertSame('https', $factory->create()->getUri()->getScheme());
    }

    public function testSchemeIsHttpsForHeaderNamesConfiguredWithUnderscores(): void
    {
        $_SERVER['HTTP_FRONT_END_HTTPS'] = 'on';

        $factory = new RequestFactoryImpl('FRONT_END_HTTPS', 'on');

        $this->assertSame('https', $factory->create()->getUri()->getScheme());
    }
}
