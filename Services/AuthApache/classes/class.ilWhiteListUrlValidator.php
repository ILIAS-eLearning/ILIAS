<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Class ilWhiteListUrlValidator
 * @author Michael Jansen <mjansen@databay.de>
 */
final class ilWhiteListUrlValidator
{
    /** @var string[] */
    private array $whitelist;

    /**
     * ilWhiteListUrlValidator constructor.
     * @param string[] $whitelist
     */
    public function __construct(private string $url, array $whitelist)
    {
        $this->whitelist = array_filter(array_map(static function (string $domain): string {
            return trim($domain); // Used for trimming and type validation (strict primitive type hint)
        }, $whitelist));
    }

    private function isValidDomain(string $domain): bool
    {
        foreach ($this->whitelist as $validDomain) {
            if ($domain === $validDomain) {
                return true;
            }

            $firstChar = $validDomain[0];
            if ('.' !== $firstChar) {
                $validDomain = '.' . $validDomain;
            }

            if ((strlen($domain) > strlen($validDomain)) && substr(
                $domain,
                (0 - strlen($validDomain))
            ) === $validDomain) {
                return true;
            }
        }

        return false;
    }

    public function isValid(): bool
    {
        $redirectDomain = parse_url($this->url, PHP_URL_HOST);
        if (null === $redirectDomain) {
            return false;
        }

        return $this->isValidDomain($redirectDomain);
    }
}
