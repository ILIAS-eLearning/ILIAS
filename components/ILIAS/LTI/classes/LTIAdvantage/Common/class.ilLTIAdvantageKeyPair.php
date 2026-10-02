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

use ceLTIc\LTI\Jwt\Jwt;
use ceLTIc\LTI\Platform;
use ceLTIc\LTI\Tool;
use Random\RandomException;

/**
 * The RSA key pair ILIAS signs its LTI Advantage messages, id tokens and access tokens with, as platform
 * and as tool, and the JSON Web Key Set that publishes it (lticerts.php).
 *
 * The key is created the first time it is needed and stored in the settings under the names earlier
 * releases used, so tools and platforms configured against an updated installation keep verifying it.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIAdvantageKeyPair
{
    private const string SETTING_PRIVATE_KEY = 'lti_1_3_privatekey';
    private const string SETTING_KID = 'lti_1_3_kid';
    private const string SIGNATURE_METHOD = 'RS256';

    public static function getJwksUrl(): string
    {
        return ilLTIEndpoint::getUrl(ilLTIEndpoint::KEY_SET);
    }

    /**
     * Lets celtic/lti sign as ILIAS.
     *
     * @throws ilException when no key can be created
     * @throws RandomException
     */
    public static function applyTo(Platform|Tool $system): void
    {
        $system->rsaKey = self::getPrivateKey();
        $system->kid = self::getKid();
        $system->jku = self::getJwksUrl();
        $system->signatureMethod = self::SIGNATURE_METHOD;
    }

    /**
     * @return array the public JSON Web Key Set
     * @throws ilException when no key can be created
     * @throws RandomException
     */
    public static function getJwks(): array
    {
        return Jwt::getJwtClient()::getJWKS(self::getPrivateKey(), self::SIGNATURE_METHOD, self::getKid());
    }

    /**
     * A token ILIAS hands out and later takes back itself, such as the registration token of a Dynamic
     * Registration. It names what it is for, so that no other token ILIAS signs is taken for it.
     *
     * @param array $claims
     * @throws ilException when no key can be created
     * @throws RandomException
     */
    public static function signFor(string $purpose, int $lifetime, array $claims): string
    {
        $now = time();

        return Jwt::getJwtClient()::sign(
            ['purpose' => $purpose, 'iat' => $now, 'exp' => $now + $lifetime] + $claims,
            self::SIGNATURE_METHOD,
            self::getPrivateKey(),
            self::getKid()
        );
    }

    /**
     * The payload of a token signFor() signed for the purpose, null when it is invalid, expired or for another.
     *
     * @throws ilException when no key can be created
     * @throws RandomException
     */
    public static function verifyFor(string $purpose, string $token): ?array
    {
        $payload = $token !== '' ? self::verify($token) : null;

        return ($payload['purpose'] ?? null) === $purpose ? $payload : null;
    }

    /**
     * The token of an Authorization header of the Bearer scheme, empty when there is none.
     */
    public static function bearerToken(string $authorization): string
    {
        return preg_match('/^Bearer\s+(\S+)$/i', trim($authorization), $matches) === 1 ? $matches[1] : '';
    }

    /**
     * The payload of a token ILIAS signed, null when the signature does not match or the token has expired.
     * A token without expiry is not accepted, and neither is one of another algorithm: the library verifies
     * with the algorithm the token names.
     *
     * @throws ilException when no key can be created
     * @throws RandomException
     */
    public static function verify(string $token): ?array
    {
        $public_key = Jwt::getJwtClient()::getPublicKey(self::getPrivateKey());
        $jwt = Jwt::getJwtClient();
        if (
            $public_key === null
            || !$jwt->load($token)
            || $jwt->getHeader('alg') !== self::SIGNATURE_METHOD
            || !$jwt->hasClaim('exp')
            || !$jwt->verifySignature($public_key)
        ) {
            return null;
        }

        return json_decode((string) json_encode($jwt->getPayload()), true);
    }

    /**
     * @throws ilException when no key can be created
     * @throws RandomException
     */
    private static function getPrivateKey(): string
    {
        $settings = self::settings();
        $key = (string) $settings->get(self::SETTING_PRIVATE_KEY, '');
        if ($key !== '') {
            return $key;
        }

        $key = (string) Jwt::getJwtClient()::generateKey(self::SIGNATURE_METHOD);
        if ($key === '') {
            throw new ilException('No LTI Advantage key could be created, check the OpenSSL configuration of PHP.');
        }
        $settings->set(self::SETTING_KID, bin2hex(random_bytes(10)));
        $settings->set(self::SETTING_PRIVATE_KEY, $key);

        return $key;
    }

    private static function getKid(): string
    {
        return (string) self::settings()->get(self::SETTING_KID, '');
    }

    private static function settings(): ilSetting
    {
        global $DIC;

        return $DIC->settings();
    }
}
