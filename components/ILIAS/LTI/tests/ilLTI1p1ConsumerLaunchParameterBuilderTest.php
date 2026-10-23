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

use ceLTIc\LTI\OAuth\OAuthConsumer;
use ceLTIc\LTI\OAuth\OAuthRequest;
use ceLTIc\LTI\OAuth\OAuthSignatureMethod_HMAC_SHA1;
use PHPUnit\Framework\TestCase;

/**
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTI1p1ConsumerLaunchParameterBuilderTest extends TestCase
{
    private const string URL = 'https://tool.example.org/launch';

    public function testSignedLaunchKeepsTheDataAndAddsOAuthParameters(): void
    {
        $parameters = ilLTI1p1ConsumerLaunchParameterBuilder::signOAuth($this->getLaunch('key1', 'secret1'));

        $this->assertSame('basic-lti-launch-request', $parameters['lti_message_type']);
        $this->assertSame('42', $parameters['resource_link_id']);
        $this->assertSame('blue', $parameters['custom_color']);
        $this->assertSame('key1', $parameters['oauth_consumer_key']);
        $this->assertSame('HMAC-SHA1', $parameters['oauth_signature_method']);
        $this->assertSame('1.0', $parameters['oauth_version']);
        $this->assertNotEmpty($parameters['oauth_nonce']);
        $this->assertNotEmpty($parameters['oauth_timestamp']);
        $this->assertNotEmpty($parameters['oauth_signature']);
    }

    public function testSignatureVerifiesWithTheSecretOnly(): void
    {
        $parameters = ilLTI1p1ConsumerLaunchParameterBuilder::signOAuth($this->getLaunch('key1', 'secret1'));
        $request = new OAuthRequest('POST', self::URL, $parameters);
        $method = new OAuthSignatureMethod_HMAC_SHA1();

        $this->assertTrue($method->check_signature(
            $request,
            new OAuthConsumer('key1', 'secret1'),
            null,
            $parameters['oauth_signature']
        ));
        $this->assertFalse($method->check_signature(
            $request,
            new OAuthConsumer('key1', 'secret2'),
            null,
            $parameters['oauth_signature']
        ));
    }

    public function testChangedDataBreaksTheSignature(): void
    {
        $parameters = ilLTI1p1ConsumerLaunchParameterBuilder::signOAuth($this->getLaunch('key1', 'secret1'));
        $signature = $parameters['oauth_signature'];
        $parameters['roles'] = 'Instructor';

        $this->assertFalse((new OAuthSignatureMethod_HMAC_SHA1())->check_signature(
            new OAuthRequest('POST', self::URL, $parameters),
            new OAuthConsumer('key1', 'secret1'),
            null,
            $signature
        ));
    }

    public function testUnknownSignatureMethodIsRefused(): void
    {
        $launch = $this->getLaunch('key1', 'secret1');
        $launch['sign_method'] = 'RSA_SHA1';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unknown signature method: RSA_SHA1');

        ilLTI1p1ConsumerLaunchParameterBuilder::signOAuth($launch);
    }

    private function getLaunch(string $key, string $secret): array
    {
        return [
            'url' => self::URL,
            'key' => $key,
            'secret' => $secret,
            'callback' => 'about:blank',
            'http_method' => 'POST',
            'sign_method' => 'HMAC_SHA1',
            'token' => null,
            'data' => [
                'lti_message_type' => 'basic-lti-launch-request',
                'lti_version' => ilLTITool::VERSION_1P1,
                'resource_link_id' => '42',
                'roles' => 'Learner',
                'custom_color' => 'blue',
            ],
        ];
    }
}
