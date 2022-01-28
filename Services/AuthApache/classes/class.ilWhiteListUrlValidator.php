<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilWhiteListUrlValidator
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilWhiteListUrlValidator
{
    protected string $url = '';
    /** @var string[] */
    protected array $whitelist = [];

    /**
     * ilWhiteListUrlValidator constructor.
     * @param string $url
     * @param string[] $whitelist
     */
    public function __construct(string $url, array $whitelist)
    {
        $this->url = $url;
        $this->whitelist = array_filter(array_map(static function (string $domain) {
            return trim($domain); // Used for trimming and type validation (strict primitive type hint)
        }, $whitelist));
    }

    private function isValidDomain(string $domain) : bool
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

    public function isValid() : bool
    {
        $redirectDomain = parse_url($this->url, PHP_URL_HOST);
        if (null === $redirectDomain) {
            return false;
        }

        return $this->isValidDomain($redirectDomain);
    }
}
