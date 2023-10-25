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

use ILIAS\StaticURL\Handler\LegacyGotoHandler;
use ILIAS\Refinery\Factory;
use ILIAS\StaticURL\Builder\StandardURIBuilder;
use ILIAS\Data\ReferenceId;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class LegacyRequestBuilder implements RequestBuilder
{
    public function buildRequest(\ILIAS\HTTP\Services $http, Factory $refinery, array $handlers): ?Request
    {
        // try to get target from query
        $target = $http->wrapper()->query()->has("target")
            ? $http->wrapper()->query()->retrieve(
                "target",
                $refinery->to()->string()
            )
            : null;
        if ($target !== null) {
            $target_parts = explode('_', $target);
            if (isset($target_parts[0]) && array_key_exists($target_parts[0], $handlers)) {
                return null;
            }

            return new Request(
                LegacyGotoHandler::NAMESPACE,
                null,
                [LegacyGotoHandler::TARGET => $target]
            );
        }

        // try build target from path (since URL has been rewritten to goto/...)
        $path = $http->request()->getUri()->getPath();
        // get everything after /goto.php/
        $path = substr($path, strpos($path, StandardURIBuilder::LONG) + strlen(StandardURIBuilder::LONG));
        $path = str_replace([StandardURIBuilder::LONG, StandardURIBuilder::SHORT], '', $path);
        $target_parts = explode('/', $path);
        if (isset($target_parts[0]) && array_key_exists($target_parts[0], $handlers)) {
            return null;
        }
        $ref_id = null;
        if (isset($target_parts[1]) && is_numeric($target_parts[1])) {
            $ref_id = new ReferenceId((int) $target_parts[1]);
            $target = $target_parts[2] ?? '';
        } else {
            $target = $target_parts[1] ?? '';
        }

        return new Request(
            LegacyGotoHandler::NAMESPACE,
            $ref_id,
            [LegacyGotoHandler::TARGET => str_replace('/', '_', $path)]
        );
    }
}
