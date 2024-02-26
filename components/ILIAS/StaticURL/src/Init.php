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

namespace ILIAS\StaticURL;

use ILIAS\DI\Container;
use ILIAS\StaticURL\Handler\HandlerService;
use ILIAS\StaticURL\Request\RequestBuilder;
use ILIAS\StaticURL\Request\BundledRequestBuilder;
use ILIAS\StaticURL\Builder\URIBuilder;
use ILIAS\StaticURL\Builder\StandardURIBuilder;
use ILIAS\StaticURL\Handler\Handler;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class Init
{
    public static function init(Container $c): void
    {
        $c['static_url.request_builder'] = static function (Container $c): RequestBuilder {
            return new BundledRequestBuilder();
        };

        $c['static_url.context'] = static function (Container $c): Context {
            return new Context($c);
        };

        $c['static_url.handler'] = static function (Container $c): HandlerService {
            $handlers = (require ArtifactObjective::PATH() ?? []);
            $handlers = array_map(static function (string $handler): Handler {
                return new $handler();
            }, $handlers);

            return new HandlerService(
                $c['static_url.request_builder'],
                $c['static_url.context'],
                ...$handlers
            );
        };

        $c['static_url.uri_builder'] = static function (Container $c): URIBuilder {
            return new StandardURIBuilder(
                ILIAS_HTTP_PATH,
                \ilRobotSettings::getInstance()?->robotSupportEnabled() ?? false
            );
        };

        $c['static_url'] = static function (Container $c): \ILIAS\StaticURL\Services {
            return new Services(
                $c['static_url.handler'],
                $c['static_url.uri_builder'],
                $c['static_url.context']
            );
        };
    }

}
