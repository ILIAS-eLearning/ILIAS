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

use ILIAS\Data\ReferenceId;
use ILIAS\Refinery\Factory;
use ILIAS\StaticURL\Handler\LegacyGotoHandler;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class StaticURLRequestBuilder implements RequestBuilder
{
    public function buildRequest(\ILIAS\HTTP\Services $http, Factory $refinery, array $handlers): ?Request
    {
        // maybe we have a legacy parameter during transition phase
        $target = $http->wrapper()->query()->has("target")
            ? $http->wrapper()->query()->retrieve(
                "target",
                $refinery->to()->string()
            )
            : null;
        if ($target !== null) {
            $target_parts = explode('_', $target);
            $namespace = array_shift($target_parts);

            if (is_numeric($target_parts[0])) {
                $reference_id = new ReferenceId((int) array_shift($target_parts));
            } else {
                $reference_id = null;
            }
            $additional_parameters = [];
            foreach ($target_parts as $target_part) {
                $additional_parameters[] = urldecode($target_part);
            }
            return new Request(
                $namespace,
                $reference_id,
                $additional_parameters
            );
        }

        // This part is for new urls
        // everything behind goto.php/ is the requested target
        $requested_url = (string) $http->request()->getUri();

        if (str_contains($requested_url, '/go/')) {
            $offset = strpos($requested_url, '/go/') + strlen('/go/');
        } else {
            $offset = strpos($requested_url, '/goto.php/') + strlen('/goto.php/');
        }

        $requested_url = substr(
            $requested_url,
            $offset
        );
        $requested_url_parts = explode('/', $requested_url);
        $namespace = array_shift($requested_url_parts);
        $additional_parameters = [];
        if (is_numeric($requested_url_parts[0])) {
            $reference_id = new ReferenceId((int) array_shift($requested_url_parts));
        } else {
            $reference_id = null;
        }
        foreach ($requested_url_parts as $requested_url_part) {
            $additional_parameters[] = urldecode($requested_url_part);
        }

        return new Request(
            $namespace,
            $reference_id,
            $additional_parameters
        );
    }
}
