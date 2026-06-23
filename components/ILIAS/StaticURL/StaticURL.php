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

use ILIAS\DI\Container;
use ILIAS\Component\Component;
use ILIAS\Setup\Agent;
use ILIAS\StaticURL\SetupAgent;
use ILIAS\Refinery\Factory;
use ILIAS\Component\Resource\PublicAsset;
use ILIAS\Component\Resource\Endpoint;
use ILIAS\StaticURL\StaticURLServices;
use ILIAS\StaticURL\Services;
use ILIAS\StaticURL\Context;
use ILIAS\StaticURL\StaticURLConfig;
use ILIAS\StaticURL\Handler\HandlerService;
use ILIAS\StaticURL\Handler\Handler;
use ILIAS\StaticURL\Handler\LegacyGotoHandler;
use ILIAS\StaticURL\Shortlinks\Handler as ShortlinksHandler;
use ILIAS\StaticURL\Request\BundledRequestBuilder;
use ILIAS\StaticURL\Builder\StandardURIBuilder;
use ILIAS\StaticURL\Builder\URIBuilder;
use ILIAS\StaticURL\Session\SessionStore;
use ILIAS\StaticURL\Session\ILIASSessionStore;

class StaticURL implements Component
{
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ): void {
        $define[] = StaticURLServices::class;
        $define[] = URIBuilder::class;

        $contribute[Agent::class] = static fn() =>
            new SetupAgent(
                $pull[Factory::class]
            );

        $contribute[PublicAsset::class] = fn() =>
            new Endpoint($this, "goto.php");


        $internal[Context::class] = static fn() =>
            new Context(static function (): Container {
                global $DIC;
                return $DIC;
            });

        $internal[StaticURLConfig::class] = static fn() =>
            new StaticURLConfig();

        $contribute[Handler::class] = static fn() => new LegacyGotoHandler();
        $contribute[Handler::class] = static fn() => new ShortlinksHandler();

        $internal[HandlerService::class] = static fn() =>
            new HandlerService(
                new BundledRequestBuilder(),
                $internal[Context::class],
                $seek[Handler::class],
                new ILIASSessionStore()
            );

        $implement[URIBuilder::class] = static fn() =>
            new StandardURIBuilder($internal[StaticURLConfig::class]);

        $implement[StaticURLServices::class] = static fn() =>
            new Services(
                $internal[HandlerService::class],
                $use[URIBuilder::class],
                $internal[Context::class]
            );
    }
}
