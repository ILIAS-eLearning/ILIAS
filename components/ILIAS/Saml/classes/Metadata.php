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

namespace ILIAS\Saml;

use SimpleSAML\Utils\Crypto;
use SimpleSAML\Error\Exception;
use SimpleSAML\Store\SQLStore;
use SimpleSAML\Configuration;
use ilSamlAuth;
use SAML2\Constants;
use Closure;

class Metadata
{
    public function __construct(private readonly SimpleSamlFactory $create)
    {
    }

    public function buildXML(ilSamlAuth $auth): string
    {
        $source = $this->create->sourceById($auth->getAuthId());
        $config = $source->getMetadata();
        $base_url = rtrim(ILIAS_HTTP_PATH, '/');

        $acs = $this->assertionConsumerServices($config, $this->defaultAssertionConsumerServices($config, $base_url, $auth->getAuthId()));

        $base = [
            'entityid' => $source->getEntityId(),
            'metadata-set' => 'saml20-sp-remote',
            'SingleLogoutService' => $this->singleLogoutService($config, $base_url, $source->getAuthId()),
            'AssertionConsumerService' => $acs['services'],
        ];

        $metadata_sp20 = $this->mergeList([
            $base,
            $this->nameIdPolicy($config),
            $this->nameInformation($config),
            $this->organizationalInformation($config),
            $this->certificates($config),
            $this->extensions($config),
        ]);

        $builder = $this->create->builder($source->getEntityId());
        $builder->addMetadataSP20($metadata_sp20, $acs['supported_protocols']);
        $builder->addOrganizationInfo($metadata_sp20);

        $xml = $builder->getEntityDescriptorText();
        $xml = $this->create->sign($xml, $config->toArray(), 'SAML 2 SP');

        return $xml;
    }

    private function singleLogoutService(Configuration $config, string $logout_url, string $source_id): array
    {
        $logout_url = $logout_url . '/module.php/saml/sp/saml2-logout.php/' . $source_id;
        $store = $this->create->store();

        $bindings = $config->getOptionalArray('SingleLogoutServiceBinding', [
            Constants::BINDING_HTTP_REDIRECT,
            Constants::BINDING_SOAP,
        ]);

        $bindings = $store instanceof SQLStore ?
                  $bindings :
                  array_values(array_filter($bindings, static fn(string $b): bool => $b !== Constants::BINDING_SOAP));

        return array_map(static fn(string $b): array => [
            'Binding' => $b,
            'Location' => $config->getOptionalString('SingleLogoutServiceLocation', $logout_url),
        ], $bindings);
    }

    private function assertionConsumerServices(Configuration $config, array $default): array
    {
        $services = $config->getOptionalArray('acs.Bindings', array_keys($default));

        $services = array_intersect($services, array_keys($default));

        $services = array_map(static fn(string $service, int $index): array => array_merge($default[$service] ?? [], [
            'index' => $index,
        ]), $services, range(0, count($services) - 1));

        return [
            'services' => array_map($this->removeKey('Protocol'), $services),
            'supported_protocols' => array_unique(array_values(array_column($services, 'Protocol'))),
        ];
    }

    private function defaultAssertionConsumerServices(Configuration $config, string $base_url, string $source_id): array
    {
        $default = [
            'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST' => [
                'Binding' => Constants::BINDING_HTTP_POST,
                'Location' => sprintf('%s/module.php/saml/sp/saml2-acs.php/%s', $base_url, $source_id),
                'Protocol' => Constants::NS_SAMLP,
            ],
            'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact' => [
                'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
                'Location' => sprintf('%s/module.php/saml/sp/saml2-acs.php/%s', $base_url, $source_id),
                'Protocol' => Constants::NS_SAMLP,
            ],
        ];

        if ($config->getOptionalString('ProtocolBinding', '') === 'urn:oasis:names:tc:SAML:2.0:profiles:holder-of-key:SSO:browser') {
            $default['urn:oasis:names:tc:SAML:2.0:profiles:holder-of-key:SSO:browser'] = [
                'Binding' => 'urn:oasis:names:tc:SAML:2.0:profiles:holder-of-key:SSO:browser',
                'hoksso:ProtocolBinding' => Constants::BINDING_HTTP_REDIRECT,
                'Location' => sprintf('%s/module.php/saml2-acs/%s', $base_url, $source_id),
                'Protocol' => Constants::NS_SAMLP,
            ];
        }

        return $default;
    }

    private function nameIdPolicy(Configuration $config): array
    {
        $format = $config->getOptionalValue('NameIDPolicy', null);
        return match (gettype($format)) {
            'array' => [
                'NameIDFormat' => $this->create->configFromArray($format)->getString('Format'),
            ],
            'string' => ['NameIDFormat' => $format],
            default => [],
        };
    }

    private function nameInformation(Configuration $config): array
    {
        if (!$config->hasValue('name') || !$config->hasValue('attributes')) {
            return [];
        }

        $information = [
            'name' => $config->getLocalizedString('name'),
            'attributes' => $config->getArray('attributes'),
        ];

        return array_merge($information, $this->mergeListIfExists($config, [
            ['attributes.required', 'getArray'],
            ['description', 'getString'],
            ['attributes.NameFormat', 'getString'],
            ['attributes.index', 'getInteger'],
            ['attributes.isDefault', 'getBoolean'],
        ]));
    }

    private function organizationalInformation(Configuration $config): array
    {
        $array = [];
        if ($config->hasValue('OrganizationName')) {
            $array['OrganizationName'] = $config->getLocalizedString('OrganizationName');

            $array = array_merge($array, $this->addIfExists($config, 'OrganizationDisplayName', 'getLocalizedString'));

            if (!$config->hasValue('OrganizationURL')) {
                throw new Exception('If OrganizationName is set, OrganizationURL must also be set.');
            }
            $array['OrganizationURL'] = $config->getLocalizedString('OrganizationURL');
        }

        foreach ($config->getOptionalArray('contacts', []) as $contact) {
            $array['contacts'][] = $this->create->contact($contact);
        }

        // add technical contact
        if ($config->hasValue('technicalcontact_email') && $config->getString('technicalcontact_email') !== 'na@example.org') {
            $techcontact = [
                'emailAddress' => $config->getString('technicalcontact_email'),
                'name' => $config->getOptionalString('technicalcontact_name', null),
                'contactType' => 'technical',
            ];
            $array['contacts'][] = $this->create->contact($techcontact);
        }

        return $array;
    }

    private function certificates(Configuration $config): array
    {
        $crypt = $this->create->crypt();

        $key = $this->buildCertData($crypt->loadPublicKey($config, false, 'new_'), true);
        $has_new_cert = $key !== null;

        $keys = array_values(array_filter([
            $key,
            $this->buildCertData($crypt->loadPublicKey($config), !$has_new_cert),
        ]));

        if (count($keys) === 1) {
            return ['certData' => $keys[0]['X509Certificate']];
        } elseif (count($keys) > 1) {
            return ['keys' => $keys];
        }

        return [];
    }

    private function buildCertData(?array $cert_info, bool $encryption): ?array
    {
        if ($cert_info['certData'] ?? false) {
            return [
                'type' => 'X509Certificate',
                'signing' => true,
                'encryption' => $encryption,
                'X509Certificate' => $cert_info['certData'],
            ];
        }

        return null;
    }

    private function extensions(Configuration $config): array
    {
        return $this->mergeListIfExists($config, [
            ['EntityAttributes', 'getArray'],
            ['UIInfo', 'getArray'],
            ['RegistrationInfo', 'getArray'],
            ['WantAssertionsSigned', 'getBoolean', 'saml20.sign.assertion'],
            ['redirect.sign', 'getBoolean', 'redirect.validate'],
            ['sign.authnrequest', 'getBoolean', 'validate.authnrequest'],
        ]);
    }

    /**
     * @param string|int $key
     * @return Closure(array): array
     */
    private function removeKey($key): Closure
    {
        return static function (array $a) use ($key) {
            unset($a[$key]);
            return $a;
        };
    }

    /**
     * @param list<array> $list
     */
    private function mergeList(array $list): array
    {
        return array_merge(...$list);
    }

    private function addIfExists(Configuration $config, string $needle, string $selector = 'getValue', ?string $as_key = null): array
    {
        return $config->hasValue($needle) ?
            [$as_key ?? $needle => $config->$selector($needle)] :
            [];
    }

    /**
     * @param list<string|list<string>> $list
     */
    private function mergeListIfExists(Configuration $config, array $list): array
    {
        return $this->mergeList(array_map(
            fn($pair): array => $this->addIfExists($config, ...(is_array($pair) ? $pair : [$pair])),
            $list
        ));
    }
}
