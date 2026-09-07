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

namespace ILIAS\StaticURL\Request;

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;
use ILIAS\StaticURL\Shortlinks\Handler;
use ILIAS\StaticURL\Builder\StandardURIBuilder;
use ILIAS\StaticURL\Handler\LegacyGotoHandler;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ShortlinkRequestBuilder implements RequestBuilder
{
    public function buildRequest(GlobalHttpState $http, Factory $refinery, array $handlers): ?Request
    {
        // Legacy permanent links always carry a "target" query parameter: either because
        // they were requested as goto.php?target=... directly, or because the rewrite rules
        // shipped in components/ILIAS/Init/resources/.htaccess turn
        // /goto_<client_id>_<type>_<id>.html into goto.php?client_id=...&target=... .
        // Such a rewrite is server-internal, so REQUEST_URI - and therefore the path below -
        // still reads /goto_<client_id>_<type>_<id>.html and none of the goto.php/go checks
        // match. Without this guard these requests would be captured here and answered with
        // a 404 by the shortlink handler. They belong to the LegacyRequestBuilder, which
        // keys on exactly the same query parameter.
        if ($http->wrapper()->query()->has(LegacyGotoHandler::TARGET)) {
            return null;
        }

        $requested_url = $http->request()->getUri()->getPath();

        if (
            str_contains($requested_url, StandardURIBuilder::SHORT)
            || str_contains($requested_url, StandardURIBuilder::LONG)
            || str_contains($requested_url, rtrim(StandardURIBuilder::LONG, '/'))
        ) {
            return null;
        }


        return new Request(
            Handler::SHORTLINK_NAMESPACE,
            null,
            [basename($requested_url)]
        );
    }
}
