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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTILibraryLoggerTest extends TestCase
{
    private const string JWT = 'eyJhbGciOiJSUzI1NiIsImtpZCI6ImsxIn0.eyJzdWIiOiI0MiIsImV4cCI6MX0.c2lnbmF0dXJl';

    public static function secretProvider(): array
    {
        return [
            'bearer token' => [
                'Authorization: Bearer 1a2b3c.4d5e',
                'Authorization: Bearer [hidden]',
            ],
            'bare JWT' => [
                'Token ' . self::JWT . ' received',
                'Token [hidden] received',
            ],
            'JSON access token' => [
                '{"access_token":"a1b2c3","token_type":"Bearer","expires_in":3600}',
                '{"access_token":"[hidden]","token_type":"Bearer","expires_in":3600}',
            ],
            'exported array with a client secret' => [
                "['client_secret' => 'xyz', 'name' => 'Tool']",
                "['client_secret' => '[hidden]', 'name' => 'Tool']",
            ],
            'form body with a client assertion' => [
                'grant_type=client_credentials&client_assertion=abc.def.ghi&scope=x',
                'grant_type=client_credentials&client_assertion=[hidden]&scope=x',
            ],
            'quoted OAuth1 signature' => [
                'oauth_signature="Zm9v%3D", oauth_nonce="n1"',
                'oauth_signature="[hidden]", oauth_nonce="n1"',
            ],
            'any parameter ending in secret' => [
                'shared_secret=s3cr3t other=1',
                'shared_secret=[hidden] other=1',
            ],
            'cookie header' => [
                "Cookie: PHPSESSID=abc; ilClientId=default\r\nAccept: */*",
                "Cookie: [hidden]\r\nAccept: */*",
            ],
            'set-cookie header' => [
                'Set-Cookie: PHPSESSID=abc; path=/; secure',
                'Set-Cookie: [hidden]',
            ],
            'nothing secret' => [
                'Launch of resource link 42 for user_id=7 with client_id=abc',
                'Launch of resource link 42 for user_id=7 with client_id=abc',
            ],
        ];
    }

    #[DataProvider('secretProvider')]
    public function testHideSecrets(string $message, string $expected): void
    {
        $this->assertSame($expected, ilLTILibraryLogger::hideSecrets($message));
    }

    public function testDescribeSkipsEmptyIdsAndEncodesNonScalars(): void
    {
        $this->assertSame(
            'client_id=abc roles=["Learner","Mentor"] attempt=0',
            ilLTILibraryLogger::describe([
                'client_id' => 'abc',
                'deployment_id' => '',
                'iss' => null,
                'roles' => ['Learner', 'Mentor'],
                'attempt' => 0,
            ])
        );
    }

    public function testDescribeHidesSecrets(): void
    {
        $this->assertSame(
            'access_token=[hidden] token=[hidden]',
            ilLTILibraryLogger::describe(['access_token' => 'a1b2c3', 'token' => self::JWT])
        );
    }

    public function testDescribeOfNoIdsIsEmpty(): void
    {
        $this->assertSame('', ilLTILibraryLogger::describe([]));
    }

    public function testLogsDetailsOnDebugWithoutSecrets(): void
    {
        $logger = $this->createMock(ilLogger::class);
        $logger->expects($this->once())
               ->method('debug')
               ->with('celtic/lti Util.php: Request sent with Authorization: Bearer [hidden]');
        $logger->expects($this->never())->method('warning');

        $this->createLibraryLogger($logger)->debug('Request sent with Authorization: Bearer 1a2b3c');
    }

    public function testLogsFailuresAsWarnings(): void
    {
        $logger = $this->createMock(ilLogger::class);
        $logger->expects($this->once())
               ->method('warning')
               ->with('celtic/lti Util.php: Access token not obtained');
        $logger->expects($this->never())->method('debug');

        $this->createLibraryLogger($logger)->error('Access token not obtained');
    }

    public function testLogsRefusedRequestsOnDebug(): void
    {
        $logger = $this->createMock(ilLogger::class);
        $logger->expects($this->once())
               ->method('debug')
               ->with('celtic/lti Util.php: Request failed with reason: Invalid nonce');
        $logger->expects($this->never())->method('warning');

        $this->createLibraryLogger($logger)->error('Request failed with reason: Invalid nonce');
    }

    public function testDropsNoise(): void
    {
        $logger = $this->createMock(ilLogger::class);
        $logger->expects($this->never())->method('debug');
        $logger->expects($this->never())->method('warning');

        $library_logger = $this->createLibraryLogger($logger);
        $library_logger->debug('JwtClient set to ceLTIc\LTI\Jwt\FirebaseClient');
        $library_logger->debug('HttpClient set to ceLTIc\LTI\Http\CurlClient');
        $library_logger->debug('LoggerClient set to ilLTILibraryLogger');
    }

    private function createLibraryLogger(ilLogger $logger): ilLTILibraryLogger
    {
        // the logger is only created by register(), which needs the whole DIC
        return Closure::bind(
            static fn(ilLogger $logger): ilLTILibraryLogger => new ilLTILibraryLogger($logger),
            null,
            ilLTILibraryLogger::class
        )($logger);
    }
}
