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
use ILIAS\StaticURL\Configuration;
use ILIAS\StaticURL\Config;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class StandardURIBuilder implements URIBuilder
{
    private ?URI $cache = null;

    public function __construct(
        private Configuration $config,
    ) {
    }

    public const string SHORT = '/go/';
    public const string LONG = '/goto.php/';

    public function buildLegacy(
        ?int $a_ref_id,
        string $a_type = '',
        array $a_params = [],
        string $append = ""
    ): string {
        global $DIC; // we do not inject this since it's e depreacted method

        $ilObjDataCache = $DIC["ilObjDataCache"];

        if ($a_type === '' && $a_ref_id) {
            $a_type = $ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($a_ref_id));
        }

        $a_params = array_merge($a_params, [$append]);
        $a_params = array_filter($a_params, static fn($value): bool => $value !== "");

        if (!empty($a_type)) {
            return (string) $this->build(
                $a_type,
                $a_ref_id !== null ? new ReferenceId($a_ref_id) : null,
                $a_params
            );
        }

        return '';
    }

    public function build(
        string $namespace,
        ?ReferenceId $reference_id = null,
        array $additional_parameters = []
    ): URI {
        $uri = $this->getBaseURI()
            . $this->config->get(Config::STATIC_LINK_ENDPOINT)
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
        if ($this->cache !== null) {
            return $this->cache;
        }

        $base_path = $this->config->get(Config::BASE_URL);

        $offset = match (true) {
            str_contains((string) $base_path, self::SHORT) => strpos((string) $base_path, self::SHORT),
            str_contains((string) $base_path, self::LONG) => strpos((string) $base_path, rtrim(self::LONG, '/')),
            str_contains((string) $base_path, rtrim(self::LONG, '/')) => strpos((string) $base_path, rtrim(self::LONG, '/')),
            str_contains((string) $base_path, 'Customizing') => strpos((string) $base_path, 'Customizing'),
            str_contains((string) $base_path, 'src') => strpos((string) $base_path, 'src'),
            str_contains((string) $base_path, 'webservices') => strpos((string) $base_path, 'webservices'),
            default => false,
        };

        if ($offset === false) {
            return $this->cache = new URI(trim((string) $base_path, '/'));
        }

        $uri_string = substr(
            (string) $base_path,
            0,
            $offset
        );
        return $this->cache = new URI(
            trim($uri_string, '/')
        );
    }

}
