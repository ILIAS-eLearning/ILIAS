<?php declare(strict_types=1);

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

use \ILIAS\DI\Container;

class lti13lib
{
    const LTI_JWT_CLAIM_PREFIX = 'https://purl.imsglobal.org/spec/lti';
    const LTI_1_3_KID = 'lti_1_3_kid';
    const LTI_1_3_PRIVATE_KEY = 'lti_1_3_privatekey';
    const ERROR_OPEN_SSL_CONF = 'error openssl config invalid';
    const OPENSSL_KEYTYPE_RSA = '';
    const ASN1_BIT_STRING = '';
    private ilSetting $setting;

    protected Container $dic;

    public array $supported_algs = [
        'ES256' => array('openssl', 'SHA256'),
        'HS256' => array('hash_hmac', 'SHA256'),
        'HS384' => array('hash_hmac', 'SHA384'),
        'HS512' => array('hash_hmac', 'SHA512'),
        'RS256' => array('openssl', 'SHA256'),
        'RS384' => array('openssl', 'SHA384'),
        'RS512' => array('openssl', 'SHA512'),
    ];


    public function __construct()
    {
        /** @var Container $DIC */
        /** @var ilSetting $ilSetting */
        global $DIC, $ilSetting;
        $this->dic = $DIC;
        $this->setting = $ilSetting;
    }

    public function verifyPrivateKey() : string
    {
        $key = $this->setting->get(self::LTI_1_3_PRIVATE_KEY);

        if (empty($key)) {
            $kid = bin2hex(openssl_random_pseudo_bytes(10));
            $this->setting->set(self::LTI_1_3_KID, $kid);
            $config = array(
                "digest_alg" => "sha256",
                "private_key_bits" => 2048,
                "private_key_type" => self::OPENSSL_KEYTYPE_RSA
            );
            $res = openssl_pkey_new($config);
            openssl_pkey_export($res, $privatekey);
            if (!empty($privatekey)) {
                $this->setting->set(self::LTI_1_3_PRIVATE_KEY, $privatekey);
            } else {
                return self::ERROR_OPEN_SSL_CONF;
            }
        }
        return '';
    }

    /**
     * @return array<string, null>|array<string, string>
     */
    public function getPrivateKey() : array
    {
        $privatekey = $this->setting->get(self::LTI_1_3_PRIVATE_KEY);
        $kid = $this->setting->get(self::LTI_1_3_KID);
        return [
            "key" => $privatekey,
            "kid" => $kid
        ];
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getJwks() : array
    {
        $jwks = ['keys' => []];

        $privatekey = $this->getPrivateKey();
        $res = openssl_pkey_get_private($privatekey['key']);
        $details = openssl_pkey_get_details($res);

        $jwk = [];
        $jwk['kty'] = 'RSA';
        $jwk['alg'] = 'RS256';
        $jwk['kid'] = $privatekey['kid'];
        $jwk['e'] = rtrim(strtr(base64_encode($details['rsa']['e']), '+/', '-_'), '=');
        $jwk['n'] = rtrim(strtr(base64_encode($details['rsa']['n']), '+/', '-_'), '=');
        $jwk['use'] = 'sig';

        $jwks['keys'][] = $jwk;
        return $jwks;
    }

    // from \Firebase\JWT
    // /lib/php-jwt/src/JWT.php

    /**
     * Converts and signs a PHP object or array into a JWT string.
     * @param object|array $payLoad     PHP object or array
     * @param string       $key         The secret key.
     *                                  If the algorithm used is asymmetric, this is the private key
     * @param string       $alg         The signing algorithm.
     *                                  Supported algorithms are 'ES256', 'HS256', 'HS384', 'HS512', 'RS256', 'RS384', and 'RS512'
     * @param mixed        $keyId
     * @param array|null   $head        An array with header elements to attach
     * @return string A signed JWT
     * @uses JsonEncode
     * @uses UrlSafeB64Encode
     */
    public function JwtEncode($payLoad, string $key, string $alg = 'RS256', $keyId = null, ?array $head = null) : string
    {
        $header = ['typ' => 'JWT', 'alg' => $alg];
        if ($keyId !== null) {
            $header['kid'] = $keyId;
        }
        if (isset($head) && is_array($head)) {
            $header = array_merge($head, $header);
        }
        $segments = [];
        $segments[] = $this->UrlSafeB64Encode(json_encode($header));
        $segments[] = $this->UrlSafeB64Encode(json_encode($payLoad));
        $signing_input = implode('.', $segments);

        $signature = $this->OpenSSLSign($signing_input, $key, $alg);
        $segments[] = $this->UrlSafeB64Encode($signature);

        return implode('.', $segments);
    }

    /**
     * Encode a string with URL-safe Base64.
     * @param string $input The string you want encoded
     * @return string The base64 encode of what you passed in
     */
    public function UrlSafeB64Encode(string $input) : string
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    public function OpenSSLSign(string $msg, string $key, string $alg = 'HS256') : string
    {
        if (empty($this->supported_algs[$alg])) {
            throw new DomainException('Algorithm not supported');
        }
        list($function, $algorithm) = $this->supported_algs[$alg];
        switch ($function) {
            case 'hash_hmac':
                return \hash_hmac($algorithm, $msg, $key, true);
            case 'openssl':
                $signature = '';
                $success = \openssl_sign($msg, $signature, $key, $algorithm);
                if (!$success) {
                    throw new DomainException("OpenSSL unable to sign data");
                } else {
                    if ($alg === 'ES256') {
                        $signature = $this->signatureFromDER($signature, 256);
                    }
                    return $signature;
                }
        }
        return '';
    }

    public function signatureFromDER(string $der, $keySize) : string
    {
        // OpenSSL returns the ECDSA signatures as a binary ASN.1 DER SEQUENCE
        list($offset, $_) = self::readDER($der);
        list($offset, $r) = self::readDER($der, $offset);
        list($offset, $s) = self::readDER($der, $offset);

        // Convert r-value and s-value from signed two's compliment to unsigned
        // big-endian integers
        $r = \ltrim($r, "\x00");
        $s = \ltrim($s, "\x00");

        // Pad out r and s so that they are $keySize bits long
        $r = \str_pad($r, $keySize / 8, "\x00", STR_PAD_LEFT);
        $s = \str_pad($s, $keySize / 8, "\x00", STR_PAD_LEFT);

        return $r . $s;
    }

    /**
     * Converts the message paramters to their equivalent JWT claim and signs the payload to launch the external tool using JWT
     * @param array  $parms    Parameters to be passed for signing
     * @param string $endPoint url of the external tool
     * @param string $oAuthConsumerKey
     * @param string|int $typeId   ID of LTI tool type
     * @param string $nonce    Nonce value to use
     * @return array|null
     */
    public function LTISignJWT(array $parms, string $endPoint, string $oAuthConsumerKey, $typeId = 0, string $nonce = '') : array
    {
        if (empty($typeId)) {
            $typeId = 0;
        }
        $messageTypeMapping = $this->LTIGetJWTMessageTypeMapping();
        if (isset($parms['lti_message_type']) && array_key_exists($parms['lti_message_type'], $messageTypeMapping)) {
            $parms['lti_message_type'] = $messageTypeMapping[$parms['lti_message_type']];
        }
        if (isset($parms['roles'])) {
            $roles = explode(',', $parms['roles']);
            $newRoles = array();
            foreach ($roles as $role) {
                if (strpos($role, 'urn:lti:role:ims/lis/') === 0) {
                    $role = 'http://purl.imsglobal.org/vocab/lis/v2/membership#' . substr($role, 21);
                } elseif (strpos($role, 'urn:lti:instrole:ims/lis/') === 0) {
                    $role = 'http://purl.imsglobal.org/vocab/lis/v2/institution/person#' . substr($role, 25);
                } elseif (strpos($role, 'urn:lti:sysrole:ims/lis/') === 0) {
                    $role = 'http://purl.imsglobal.org/vocab/lis/v2/system/person#' . substr($role, 24);
                } elseif ((strpos($role, '://') === false) && (strpos($role, 'urn:') !== 0)) {
                    $role = "http://purl.imsglobal.org/vocab/lis/v2/membership#{$role}";
                }
                $newRoles[] = $role;
            }
            $parms['roles'] = implode(',', $newRoles);
        }

        $now = time();
        if (empty($nonce)) {
            $nonce = bin2hex(openssl_random_pseudo_bytes(10));
        }
        $claimMapping = $this->LTIGetJWTClaimMapping();
        $payLoad = array(
            'nonce' => $nonce,
            'iat' => $now,
            'exp' => $now + 60,
        );
        #$payLoad['iss'] = $CFG->wwwroot;
        $payLoad['iss'] = ILIAS_HTTP_PATH; // TODO!!
        $payLoad['aud'] = $oAuthConsumerKey;
        $payLoad[self::LTI_JWT_CLAIM_PREFIX . '/claim/deployment_id'] = strval($typeId);
        $payLoad[self::LTI_JWT_CLAIM_PREFIX . '/claim/target_link_uri'] = $endPoint;

        foreach ($parms as $key => $value) {
            $claim = self::LTI_JWT_CLAIM_PREFIX;
            if (array_key_exists($key, $claimMapping)) {
                $mapping = $claimMapping[$key];
                $type = $mapping["type"] ?? "string";
                if ($mapping['isarray']) {
                    $value = explode(',', $value);
                    sort($value);
                } elseif ($type == 'boolean') {
                    $value = isset($value) && ($value == 'true');
                }
                if (!empty($mapping['suffix'])) {
                    $claim .= "-{$mapping['suffix']}";
                }
                $claim .= '/claim/';
                if (is_null($mapping['group'])) {
                    $payLoad[$mapping['claim']] = $value;
                } elseif (empty($mapping['group'])) {
                    $payLoad["{$claim}{$mapping['claim']}"] = $value;
                } else {
                    $claim .= $mapping['group'];
                    $payLoad[$claim][$mapping['claim']] = $value;
                }
            } elseif (strpos($key, 'custom_') === 0) {
                $payLoad["{$claim}/claim/custom"][substr($key, 7)] = $value;
            } elseif (strpos($key, 'ext_') === 0) {
                $payLoad["{$claim}/claim/ext"][substr($key, 4)] = $value;
            }
        }
        if (!empty($this->verifyPrivateKey())) {
            throw new DomainException(self::ERROR_OPEN_SSL_CONF);
        }
        $privateKey = $this->getPrivateKey();
        $jwt = $this->JwtEncode($payLoad, $privateKey['key'], 'RS256', $privateKey['kid']);

        $newParms = array();
        $newParms['id_token'] = $jwt;

        return $newParms;
    }

    /**
     * Reads binary DER-encoded data and decodes into a single object
     * @param string $der    the binary data in DER format
     * @param int    $offset the offset of the data stream containing the object
     * to decode
     * @return array [$offset, $data] the new offset and the decoded object
     */
    private static function readDER(string $der, int $offset = 0) : array
    {
        $pos = $offset;
        $size = \strlen($der);
        $constructed = (\ord($der[$pos]) >> 5) & 0x01;
        $type = \ord($der[$pos++]) & 0x1f;

        // Length
        $len = \ord($der[$pos++]);
        if ($len & 0x80) {
            $n = $len & 0x1f;
            $len = 0;
            while ($n-- && $pos < $size) {
                $len = ($len << 8) | \ord($der[$pos++]);
            }
        }

        // Value
        if ($type == self::ASN1_BIT_STRING) {
            $pos++; // Skip the first contents octet (padding indicator)
            $data = \substr($der, $pos, $len - 1);
            $pos += $len - 1;
        } elseif (!$constructed) {
            $data = \substr($der, $pos, $len);
            $pos += $len;
        } else {
            $data = null;
        }

        return array($pos, $data);
    }
    /**
     * Return the mapping for standard message types to JWT message_type claim.
     *
     * @return array
     */

    public function LTIGetJWTMessageTypeMapping() : array
    {
        return array(
            'basic-lti-launch-request' => 'LtiResourceLinkRequest',
            'ContentItemSelectionRequest' => 'LtiDeepLinkingRequest',
            'LtiDeepLinkingResponse' => 'ContentItemSelection',
        );
    }

    /**
     * Return the mapping for standard message parameters to JWT claim.
     *
     * @return array
     */
    public function LTIGetJWTClaimMapping() : array
    {
        return array(
            'accept_copy_advice' => [
                'suffix' => 'dl',
                'group' => 'deep_linking_settings',
                'claim' => 'accept_copy_advice',
                'isarray' => false,
                'type' => 'boolean'
            ],
            'accept_media_types' => [
                'suffix' => 'dl',
                'group' => 'deep_linking_settings',
                'claim' => 'accept_media_types',
                'isarray' => true
            ],
            'accept_multiple' => [
                'suffix' => 'dl',
                'group' => 'deep_linking_settings',
                'claim' => 'accept_multiple',
                'isarray' => false,
                'type' => 'boolean'
            ],
            'accept_presentation_document_targets' => [
                'suffix' => 'dl',
                'group' => 'deep_linking_settings',
                'claim' => 'accept_presentation_document_targets',
                'isarray' => true
            ],
            'accept_types' => [
                'suffix' => 'dl',
                'group' => 'deep_linking_settings',
                'claim' => 'accept_types',
                'isarray' => true
            ],
            'accept_unsigned' => [
                'suffix' => 'dl',
                'group' => 'deep_linking_settings',
                'claim' => 'accept_unsigned',
                'isarray' => false,
                'type' => 'boolean'
            ],
            'auto_create' => [
                'suffix' => 'dl',
                'group' => 'deep_linking_settings',
                'claim' => 'auto_create',
                'isarray' => false,
                'type' => 'boolean'
            ],
            'can_confirm' => [
                'suffix' => 'dl',
                'group' => 'deep_linking_settings',
                'claim' => 'can_confirm',
                'isarray' => false,
                'type' => 'boolean'
            ],
            'content_item_return_url' => [
                'suffix' => 'dl',
                'group' => 'deep_linking_settings',
                'claim' => 'deep_link_return_url',
                'isarray' => false
            ],
            'content_items' => [
                'suffix' => 'dl',
                'group' => '',
                'claim' => 'content_items',
                'isarray' => true
            ],
            'data' => [
                'suffix' => 'dl',
                'group' => 'deep_linking_settings',
                'claim' => 'data',
                'isarray' => false
            ],
            'text' => [
                'suffix' => 'dl',
                'group' => 'deep_linking_settings',
                'claim' => 'text',
                'isarray' => false
            ],
            'title' => [
                'suffix' => 'dl',
                'group' => 'deep_linking_settings',
                'claim' => 'title',
                'isarray' => false
            ],
            'lti_msg' => [
                'suffix' => 'dl',
                'group' => '',
                'claim' => 'msg',
                'isarray' => false
            ],
            'lti_log' => [
                'suffix' => 'dl',
                'group' => '',
                'claim' => 'log',
                'isarray' => false
            ],
            'lti_errormsg' => [
                'suffix' => 'dl',
                'group' => '',
                'claim' => 'errormsg',
                'isarray' => false
            ],
            'lti_errorlog' => [
                'suffix' => 'dl',
                'group' => '',
                'claim' => 'errorlog',
                'isarray' => false
            ],
            'context_id' => [
                'suffix' => '',
                'group' => 'context',
                'claim' => 'id',
                'isarray' => false
            ],
            'context_label' => [
                'suffix' => '',
                'group' => 'context',
                'claim' => 'label',
                'isarray' => false
            ],
            'context_title' => [
                'suffix' => '',
                'group' => 'context',
                'claim' => 'title',
                'isarray' => false
            ],
            'context_type' => [
                'suffix' => '',
                'group' => 'context',
                'claim' => 'type',
                'isarray' => true
            ],
            'lis_course_offering_sourcedid' => [
                'suffix' => '',
                'group' => 'lis',
                'claim' => 'course_offering_sourcedid',
                'isarray' => false
            ],
            'lis_course_section_sourcedid' => [
                'suffix' => '',
                'group' => 'lis',
                'claim' => 'course_section_sourcedid',
                'isarray' => false
            ],
            'launch_presentation_css_url' => [
                'suffix' => '',
                'group' => 'launch_presentation',
                'claim' => 'css_url',
                'isarray' => false
            ],
            'launch_presentation_document_target' => [
                'suffix' => '',
                'group' => 'launch_presentation',
                'claim' => 'document_target',
                'isarray' => false
            ],
            'launch_presentation_height' => [
                'suffix' => '',
                'group' => 'launch_presentation',
                'claim' => 'height',
                'isarray' => false
            ],
            'launch_presentation_locale' => [
                'suffix' => '',
                'group' => 'launch_presentation',
                'claim' => 'locale',
                'isarray' => false
            ],
            'launch_presentation_return_url' => [
                'suffix' => '',
                'group' => 'launch_presentation',
                'claim' => 'return_url',
                'isarray' => false
            ],
            'launch_presentation_width' => [
                'suffix' => '',
                'group' => 'launch_presentation',
                'claim' => 'width',
                'isarray' => false
            ],
            'lis_person_contact_email_primary' => [
                'suffix' => '',
                'group' => null,
                'claim' => 'email',
                'isarray' => false
            ],
            'lis_person_name_family' => [
                'suffix' => '',
                'group' => null,
                'claim' => 'family_name',
                'isarray' => false
            ],
            'lis_person_name_full' => [
                'suffix' => '',
                'group' => null,
                'claim' => 'name',
                'isarray' => false
            ],
            'lis_person_name_given' => [
                'suffix' => '',
                'group' => null,
                'claim' => 'given_name',
                'isarray' => false
            ],
            'lis_person_sourcedid' => [
                'suffix' => '',
                'group' => 'lis',
                'claim' => 'person_sourcedid',
                'isarray' => false
            ],
            'user_id' => [
                'suffix' => '',
                'group' => null,
                'claim' => 'sub',
                'isarray' => false
            ],
            'user_image' => [
                'suffix' => '',
                'group' => null,
                'claim' => 'picture',
                'isarray' => false
            ],
            'roles' => [
                'suffix' => '',
                'group' => '',
                'claim' => 'roles',
                'isarray' => true
            ],
            'role_scope_mentor' => [
                'suffix' => '',
                'group' => '',
                'claim' => 'role_scope_mentor',
                'isarray' => false
            ],
            'deployment_id' => [
                'suffix' => '',
                'group' => '',
                'claim' => 'deployment_id',
                'isarray' => false
            ],
            'lti_message_type' => [
                'suffix' => '',
                'group' => '',
                'claim' => 'message_type',
                'isarray' => false
            ],
            'lti_version' => [
                'suffix' => '',
                'group' => '',
                'claim' => 'version',
                'isarray' => false
            ],
            'resource_link_description' => [
                'suffix' => '',
                'group' => 'resource_link',
                'claim' => 'description',
                'isarray' => false
            ],
            'resource_link_id' => [
                'suffix' => '',
                'group' => 'resource_link',
                'claim' => 'id',
                'isarray' => false
            ],
            'resource_link_title' => [
                'suffix' => '',
                'group' => 'resource_link',
                'claim' => 'title',
                'isarray' => false
            ],
            'tool_consumer_info_product_family_code' => [
                'suffix' => '',
                'group' => 'tool_platform',
                'claim' => 'product_family_code',
                'isarray' => false
            ],
            'tool_consumer_info_version' => [
                'suffix' => '',
                'group' => 'tool_platform',
                'claim' => 'version',
                'isarray' => false
            ],
            'tool_consumer_instance_contact_email' => [
                'suffix' => '',
                'group' => 'tool_platform',
                'claim' => 'contact_email',
                'isarray' => false
            ],
            'tool_consumer_instance_description' => [
                'suffix' => '',
                'group' => 'tool_platform',
                'claim' => 'description',
                'isarray' => false
            ],
            'tool_consumer_instance_guid' => [
                'suffix' => '',
                'group' => 'tool_platform',
                'claim' => 'guid',
                'isarray' => false
            ],
            'tool_consumer_instance_name' => [
                'suffix' => '',
                'group' => 'tool_platform',
                'claim' => 'name',
                'isarray' => false
            ],
            'tool_consumer_instance_url' => [
                'suffix' => '',
                'group' => 'tool_platform',
                'claim' => 'url',
                'isarray' => false
            ],
            'custom_context_memberships_v2_url' => [
                'suffix' => 'nrps',
                'group' => 'namesroleservice',
                'claim' => 'context_memberships_url',
                'isarray' => false
            ],
            'custom_context_memberships_versions' => [
                'suffix' => 'nrps',
                'group' => 'namesroleservice',
                'claim' => 'service_versions',
                'isarray' => true
            ],
            'custom_gradebookservices_scope' => [
                'suffix' => 'ags',
                'group' => 'endpoint',
                'claim' => 'scope',
                'isarray' => true
            ],
            'custom_lineitems_url' => [
                'suffix' => 'ags',
                'group' => 'endpoint',
                'claim' => 'lineitems',
                'isarray' => false
            ],
            'custom_lineitem_url' => [
                'suffix' => 'ags',
                'group' => 'endpoint',
                'claim' => 'lineitem',
                'isarray' => false
            ],
            'custom_results_url' => [
                'suffix' => 'ags',
                'group' => 'endpoint',
                'claim' => 'results',
                'isarray' => false
            ],
            'custom_result_url' => [
                'suffix' => 'ags',
                'group' => 'endpoint',
                'claim' => 'result',
                'isarray' => false
            ],
            'custom_scores_url' => [
                'suffix' => 'ags',
                'group' => 'endpoint',
                'claim' => 'scores',
                'isarray' => false
            ],
            'custom_score_url' => [
                'suffix' => 'ags',
                'group' => 'endpoint',
                'claim' => 'score',
                'isarray' => false
            ],
            'lis_outcome_service_url' => [
                'suffix' => 'bo',
                'group' => 'basicoutcome',
                'claim' => 'lis_outcome_service_url',
                'isarray' => false
            ],
            'lis_result_sourcedid' => [
                'suffix' => 'bo',
                'group' => 'basicoutcome',
                'claim' => 'lis_result_sourcedid',
                'isarray' => false
            ],
        );
    }
}
