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

namespace ILIAS;

use ILIAS\WebDAV\Environment;
use ILIAS\Setup\Agent;
use ILIAS\Component\Component;
use ILIAS\Component\Resource\PublicAsset;
use ILIAS\Component\Resource\Endpoint;
use ILIAS\Component\EntryPoint;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\WebDAV\Entity\Factory;
use ILIAS\WebDAV\Config;
use ILIAS\WebDAV\Request\RequestTranslation;
use ILIAS\WebDAV\Request\LegacyRequestProxy;
use ILIAS\WebDAV\Objects\TreeProxyRepository;
use ILIAS\WebDAV\Objects\ProxyRepository;
use ILIAS\WebDAV\Objects\Filter\Filter;
use ILIAS\WebDAV\Objects\Filter\Collection;
use ILIAS\WebDAV\Objects\Filter\CharacterFilter;
use ILIAS\WebDAV\AccessCheck;
use ILIAS\WebDAV\RBACAccessCheckLegacyProxy;
use ILIAS\WebDAV\Objects\Filter\RBACFilter;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKey;
use ILIAS\WebDAV\Setup\KeyRotationObjective;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKeyRotation;
use ILIAS\WebDAV\Mount\UriBuilder;

class WebDAV implements Component
{
    public function init(
        array|\ArrayAccess &$define,
        array|\ArrayAccess &$implement,
        array|\ArrayAccess &$use,
        array|\ArrayAccess &$contribute,
        array|\ArrayAccess &$seek,
        array|\ArrayAccess &$provide,
        array|\ArrayAccess &$pull,
        array|\ArrayAccess &$internal,
    ): void {
        // TODO remove after HTTP service implements this
        $define[] = ServerRequestInterface::class;

        $implement[ServerRequestInterface::class] = fn(): ServerRequestInterface => new LegacyRequestProxy();
        $internal[Config::class] = static fn(): Config => new Config();
        $internal[RequestTranslation::class] = static fn(): RequestTranslation => new RequestTranslation(
            $internal[Config::class],
            $use[ServerRequestInterface::class]
        );

        $internal[AccessCheck::class] = static fn(): AccessCheck => new RBACAccessCheckLegacyProxy();

        $internal[Filter::class] = static fn(): Filter => new Collection(
            new CharacterFilter(
                $internal[Config::class],
            ),
            new RBACFilter(
                $internal[AccessCheck::class]
            )
        );

        $internal[ProxyRepository::class] = static fn(): ProxyRepository => new TreeProxyRepository(
            $internal[Config::class],
            $internal[Filter::class],
        );

        $internal[Factory::class] = static fn(): Factory => new Factory(
            $internal[RequestTranslation::class],
            $internal[ProxyRepository::class]
        );

        $contribute[PublicAsset::class] = fn(): Endpoint => new Endpoint(
            $this,
            $internal[Config::class]->getEndpoint()
        );

        $internal[SecretKeyRotation::class] = static function (): SecretKeyRotation {
            $keys = array_map(
                static fn(string $key): SecretKey => new SecretKey($key),
                (@include KeyRotationObjective::PATH()) ?: []
            );
            $current_key = array_shift($keys) ?? new SecretKey(bin2hex(random_bytes(32)));

            return new SecretKeyRotation(
                $current_key,
                ... $keys
            );
        };

        $internal[UriBuilder::class] = static fn(): UriBuilder => new UriBuilder(
            $use[ServerRequestInterface::class],
            $internal[Config::class]
        );

        $provide[Environment::class] = static fn(
        ): Environment => new Environment(
            $internal[Config::class],
            $internal[UriBuilder::class]
        );

        $contribute[EntryPoint::class] = static fn(): Entrypoint => new \ILIAS\WebDAV\Entrypoint(
            $internal[Factory::class],
            $internal[RequestTranslation::class],
            $internal[SecretKeyRotation::class],
            $internal[Config::class]
        );

        $contribute[Agent::class] = static fn(): Agent
            => new \ILIAS\WebDAV\Setup\Agent();
    }
}
