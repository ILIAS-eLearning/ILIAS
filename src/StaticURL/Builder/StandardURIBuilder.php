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

namespace ILIAS\StaticURL\Builder;

use ILIAS\Data\URI;
use ILIAS\Data\ReferenceId;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class StandardURIBuilder implements URIBuilder
{
    public function __construct(
        private string $ILIAS_HTTP_PATH,
        private bool $short_url_possible = false
    ) {
    }

    public const SHORT = '/go/';
    public const LONG = '/goto.php/';

    public function build(
        string $namespace,
        ?ReferenceId $reference_id = null,
        array $additional_parameters = []
    ): URI {
        $uri = $this->getBaseURI()
            . ($this->short_url_possible ? self::SHORT : self::LONG)
            . $this->buildTarget($namespace, $reference_id, $additional_parameters);

        return new URI($uri);
    }

    public function buildTarget(
        string $namespace,
        ?ReferenceId $reference_id = null,
        array $additional_parameters = []
    ): string {
        return $namespace
            . ($reference_id !== null ? '/' . $reference_id->toInt() : '')
            . '/'
            . implode('/', $additional_parameters);
    }

    public function getBaseURI(): URI
    {
        $base_path = $this->ILIAS_HTTP_PATH;

        $offset = match (true) {
            str_contains($base_path, self::SHORT) => strpos($base_path, self::SHORT),
            str_contains($base_path, self::LONG) => strpos($base_path, rtrim(self::LONG, '/')),
            str_contains($base_path, rtrim(self::LONG, '/')) => strpos($base_path, rtrim(self::LONG, '/')),
            str_contains($base_path, 'Customizing') => strpos($base_path, 'Customizing'),
            str_contains($base_path, 'src') => strpos($base_path, 'src'),
            str_contains($base_path, 'webservices') => strpos($base_path, 'webservices'),
            default => false,
        };

        if ($offset === false) {
            return new URI(trim($base_path, '/'));
        }

        $uri_string = substr(
            $base_path,
            0,
            $offset
        );
        return new URI(
            trim($uri_string, '/')
        );
    }

}
