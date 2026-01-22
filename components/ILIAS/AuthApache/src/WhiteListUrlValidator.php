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

namespace ILIAS\AuthApache;

final readonly class WhiteListUrlValidator
{
    /** @var list<string> */
    private array $whitelist;

    /**
     * @param list<string> $whitelist
     */
    public function __construct(private string $url, array $whitelist)
    {
        $this->whitelist = array_filter(array_map(static function (string $domain): string {
            return trim($domain);
        }, $whitelist));
    }

    private function isValidHost(string $host): bool
    {
        foreach ($this->whitelist as $valid_host) {
            if ($host === $valid_host) {
                return true;
            }

            if (!str_starts_with($valid_host, '.')) {
                $valid_host = '.' . $valid_host;
            }

            if ((\strlen($host) > \strlen($valid_host)) && substr(
                $host,
                (0 - \strlen($valid_host))
            ) === $valid_host) {
                return true;
            }
        }

        return false;
    }

    public function isValid(): bool
    {
        $host = parse_url($this->url, PHP_URL_HOST);
        if ($host === null) {
            return false;
        }

        return $this->isValidHost($host);
    }
}
