<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */
declare(strict_types=1);

/**
 * Class ilWhiteListUrlValidator
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilWhiteListUrlValidator
{
    /** @var string */
    protected $url = '';
    
    /** @var string[] */
    protected $whitelist = [];

    /**
     * ilWhiteListUrlValidator constructor.
     * @param string   $url
     * @param string[] $whitelist
     */
    public function __construct(string $url, array $whitelist)
    {
        $this->url       = $url;
        $this->whitelist = array_filter(array_map(function (string $domain) {
            return trim($domain); // Used for trimming and type validation (strict primitive type hint)
        }, $whitelist));
    }

    /**
     * @param string $domain
     * @return bool
     */
    private function isValidDomain(string $domain) : bool
    {
        foreach ($this->whitelist as $validDomain) {
            if ($domain === $validDomain) {
                return true;
            }

            $firstChar = $validDomain{0};
            if ('.' !== $firstChar) {
                $validDomain = '.' . $validDomain;
            }

            if (strlen($domain) > strlen($validDomain)) {
                if (substr($domain, (0 - strlen($validDomain))) === $validDomain) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isValid() : bool
    {
        $redirectDomain = parse_url($this->url, PHP_URL_HOST);
        if (null === $redirectDomain) {
            return false;
        }

        return $this->isValidDomain($redirectDomain);
    }
}
