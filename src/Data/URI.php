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

namespace ILIAS\Data;

/**
 * The scope of this class is split ilias-conform URI's into components.
 * Please refer to RFC 3986 for details.
 * Notice, ilias-confor URI's will form a SUBSET of RFC 3986:
 *  - Notice the restrictions on baseuri-subdelims.
 *  - We require a schema and an authority to be present.
 *  - If any part is located and it is invalid an exception will be thrown
 *    instead of just omiting it.
 *  - IPv6 is now supported.
 *  - The IPv6 conforms to https://datatracker.ietf.org/doc/html/rfc4291#section-2.2.
 */
class URI
{
    private const PATH_DELIM = '/';

    /**
     * Relevant character-groups as defined in RFC 3986 Appendix 1
     */
    private const ALPHA = '[A-Za-z]';
    private const DIGIT = '[0-9]';
    private const ALPHA_DIGIT = '[A-Za-z0-9]';
    private const HEXDIG = '[0-9A-Fa-f]';
    private const PCTENCODED = '%' . self::HEXDIG . self::HEXDIG;
    /**
     * point|minus|plus to be used in schema.
     */
    private const PIMP = '[\\+\\-\\.]';

    /**
     * valid subdelims according to RFC 3986 Appendix 1:
     * "!" "$" "&" "'" "(" ")" "*" "+" "," ";" "="
     */
    private const SUBDELIMS = '[\\$,;=!&\'\\(\\)\\*\\+]';
    /**
     * subdelims without jsf**k characters +!() and =
     */
    private const BASEURI_SUBDELIMS = '[\\$,;&\'\\*]';

    private const UNRESERVED = self::ALPHA_DIGIT . '|[\\-\\._~]';
    private const UNRESERVED_NO_DOT = self::ALPHA_DIGIT . '|[\\-_~]';

    private const PCHAR = self::UNRESERVED . '|' . self::SUBDELIMS . '|' . self::PCTENCODED . '|:|@';
    private const BASEURI_PCHAR = self::UNRESERVED . '|' . self::BASEURI_SUBDELIMS . '|' . self::PCTENCODED . '|:|@';

    private const SCHEMA = '#^' . self::ALPHA . '(' . self::ALPHA_DIGIT . '|' . self::PIMP . ')*$#';
    private const DOMAIN_LABEL = self::ALPHA_DIGIT . '((' . self::UNRESERVED_NO_DOT . '|' . self::PCTENCODED . '|' . self::BASEURI_SUBDELIMS . ')*' . self::ALPHA_DIGIT . ')*';
    private const HOST_REG_NAME = '^' . self::DOMAIN_LABEL . '(\\.' . self::DOMAIN_LABEL . ')*$';
    private const HOST_IPV4_EMBEDDED = '(' . self::DIGIT . '{1,3})(\\.' . self::DIGIT . '{1,3}){3}';
    private const HOST_IPV4 = '^' . self::HOST_IPV4_EMBEDDED . '$';

    private const IPV6_COUNT_COLONS = '(' . self::HEXDIG . '{1,4}:)';
    private const IPV6_COLONS_BETWEEN = '(' . self::HEXDIG . '{0,4}:' . self::HEXDIG . '{0,4})';
    private const IPV6_LEFT_SHORT = '(' . self::HEXDIG . '{1,4}|' . self::HEXDIG . '{1,4}(:' . self::HEXDIG . '{1,4})*)?'; // Left of :: are separated with one ":".

    // As specified in RFC4291 Section 2.2 there are three different text forms of an IPv6 address.
    // HOST_IPV6_LONG corresponds to the form defined in RFC4291 Section 2.2.1.
    private const HOST_IPV6_LONG = '^\\[' . self::IPV6_COUNT_COLONS . '{7}' . self::HEXDIG . '{1,4}\\]$';
    // HOST_IPV6_SHORT corresponds to the form defined in RFC4291 Section 2.2.2.
    private const HOST_IPV6_SHORT = '^\\[(?=' . self::IPV6_COLONS_BETWEEN . '{2,7}\\]$)' . // Ensure whole string contains between 2-7 colons.
                                  self::IPV6_LEFT_SHORT . '::' .
                                  // Right of "::" are separated with one ":".
                                  '(' . self::HEXDIG . '{1,4}|(' . self::HEXDIG . '{1,4}:)*' . self::HEXDIG . '{1,4})?\\]$';

    // HOST_IPV6_EMBEDDED_LONG corresponds to the form defined in RFC4291 Section 2.2.3 but not in a compressed form.
    private const HOST_IPV6_EMBEDDED_IPV4_LONG = '^\\[' . self::IPV6_COUNT_COLONS. '{5}' . self::HEXDIG . '{1,4}:' . self::HOST_IPV4_EMBEDDED . '\\]$';
    // HOST_IPV6_EMBEDDED_SHORT corresponds to the form defined in RFC4291 Section 2.2.3 in a compressed form.
    private const HOST_IPV6_EMBEDDED_IPV4_SHORT = '^\\[(?=' . self::IPV6_COLONS_BETWEEN . '{2,6}' . self::HOST_IPV4_EMBEDDED . '\\]$)' . // Ensure whole string contains between 2-6 colons.
                                                self::IPV6_LEFT_SHORT . '::' .
                                                // Right of "::" are separated with one ":".
                                                // The hexdig pair before the ipv4 is 0-4 to accommodate for both ::1.2.3.4 and ::2:1.2.3.4 colon usages.
                                                '(' . self::HEXDIG . '{1,4}|(' . self::HEXDIG . '{1,4}:)*' . self::HEXDIG . '{0,4})?' . self::HOST_IPV4_EMBEDDED . '\\]$';

    private const HOST_IPV6_EMBEDDED_IPV4 = self::HOST_IPV6_EMBEDDED_IPV4_SHORT . '|' . self::HOST_IPV6_EMBEDDED_IPV4_LONG;

    private const HOST_IPV6 = self::HOST_IPV6_LONG . '|' . self::HOST_IPV6_SHORT . '|' . self::HOST_IPV6_EMBEDDED_IPV4;
    private const HOST = '#' . self::HOST_IPV4 . '|' . self::HOST_REG_NAME . '|' . self::HOST_IPV6 . '#';
    private const PORT = '#^' . self::DIGIT . '+$#';
    private const PATH = '#^(?!//)(?!:)(' . self::PCHAR . '|' . self::PATH_DELIM . ')+$#';
    private const QUERY = '#^(' . self::PCHAR . '|' . self::PATH_DELIM . '|\\?)+$#';
    private const FRAGMENT = '#^(' . self::PCHAR . '|' . self::PATH_DELIM . '|\\?|\\#)+$#';

    protected string $schema;
    protected string $host;
    protected ?int $port;
    protected ?string $path;
    protected ?string $query;
    protected ?string $fragment;

    public function __construct(string $uri_string)
    {
        $this->schema = $this->digestSchema(parse_url($uri_string, PHP_URL_SCHEME));
        $this->host = $this->digestHost(parse_url($uri_string, PHP_URL_HOST));
        $this->port = $this->digestPort(parse_url($uri_string, PHP_URL_PORT));
        $this->path = $this->digestPath(parse_url($uri_string, PHP_URL_PATH));
        $this->query = $this->digestQuery(parse_url($uri_string, PHP_URL_QUERY));
        $this->fragment = $this->digestFragment(parse_url($uri_string, PHP_URL_FRAGMENT));
    }

    /**
     * Check schema formating. Return it in case of success.
     */
    protected function digestSchema(string $schema): string
    {
        return $this->checkCorrectFormatOrThrow(self::SCHEMA, $schema);
    }

    /**
     * Check host formating. Return it in case of success.
     */
    protected function digestHost(string $host): string
    {
        return $this->checkCorrectFormatOrThrow(self::HOST, $host);
    }

    /**
     * Check port formating. Return it in case of success.
     */
    protected function digestPort(int $port = null): ?int
    {
        return $port ?? null;
    }

    /**
     * Check path formating. Return it in case of success.
     */
    protected function digestPath(string $path = null): ?string
    {
        if ($path === null) {
            return null;
        }
        $path = trim($this->checkCorrectFormatOrThrow(self::PATH, $path), self::PATH_DELIM);
        if ($path === '') {
            $path = null;
        }
        return $path;
    }

    /**
     * Check query formating. Return it in case of success.
     */
    protected function digestQuery(string $query = null): ?string
    {
        if ($query === null) {
            return null;
        }
        return $this->checkCorrectFormatOrThrow(self::QUERY, $query);
    }

    /**
     * Check fragment formating. Return it in case of success.
     */
    protected function digestFragment(string $fragment = null): ?string
    {
        if ($fragment === null) {
            return null;
        }
        return $this->checkCorrectFormatOrThrow(self::FRAGMENT, $fragment);
    }


    /**
     * Check wether a string fits a regexp. Return it, if so,
     * throw otherwise.
     */
    protected function checkCorrectFormatOrThrow(string $regexp, string $string): string
    {
        if (preg_match($regexp, $string) === 1) {
            return $string;
        }
        throw new \InvalidArgumentException('ill-formated component "' . $string . '" expected "' . $regexp . '"');
    }

    public function getSchema(): string
    {
        return $this->schema;
    }

    /**
     * Get URI with modified schema
     */
    public function withSchema(string $schema): URI
    {
        $shema = $this->digestSchema($schema);
        $other = clone $this;
        $other->schema = $schema;
        return $other;
    }

    public function getAuthority(): string
    {
        $port = $this->getPort();
        if ($port === null) {
            return $this->getHost();
        }
        return $this->getHost() . ':' . $port;
    }

    /**
     * Get URI with modified authority
     */
    public function withAuthority(string $authority): URI
    {
        $parts = explode(':', $authority);
        if (count($parts) > 2) {
            throw new \InvalidArgumentException('ill-formated component ' . $authority);
        }
        $host = $this->digestHost($parts[0]);
        $port = null;
        if (array_key_exists(1, $parts)) {
            $port = (int) $this->checkCorrectFormatOrThrow(self::PORT, (string) $parts[1]);
        }
        $other = clone $this;
        $other->host = $host;
        $other->port = $port;
        return $other;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * Get URI with modified port
     */
    public function withPort(int $port = null): URI
    {
        $port = $this->digestPort($port);
        $other = clone $this;
        $other->port = $port;
        return $other;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Get URI with modified host
     */
    public function withHost(string $host): URI
    {
        $host = $this->digestHost($host);
        $other = clone $this;
        $other->host = $host;
        return $other;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Get URI with modified path
     */
    public function withPath(string $path = null): URI
    {
        $path = $this->digestPath($path);
        $other = clone $this;
        $other->path = $path;
        return $other;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * Get URI with modified query
     */
    public function withQuery(string $query = null): URI
    {
        $query = $this->digestQuery($query);
        $other = clone $this;
        $other->query = $query;
        return $other;
    }

    public function getFragment(): ?string
    {
        return $this->fragment;
    }

    /**
     * Get URI with modified fragment
     */
    public function withFragment(string $fragment = null): URI
    {
        $fragment = $this->digestFragment($fragment);
        $other = clone $this;
        $other->fragment = $fragment;
        return $other;
    }

    /**
     * Get a well-formed URI consisting only of
     * schema, authority and port.
     */
    public function getBaseURI(): string
    {
        $path = $this->getPath();
        if ($path === null) {
            return $this->getSchema() . '://' . $this->getAuthority();
        }
        return $this->getSchema() . '://' . $this->getAuthority() . '/' . $path;
    }

    public function __toString(): string
    {
        $uri = $this->getBaseURI();
        $query = $this->getQuery();
        if ($query) {
            $uri .= '?' . $query;
        }
        $fragment = $this->getFragment();
        if ($fragment) {
            $uri .= '#' . $fragment;
        }
        return $uri;
    }

    /**
     * Get all parameters as associative array
     */
    public function getParameters(): array
    {
        $params = [];
        $query = $this->getQuery();
        if (!is_null($query)) {
            parse_str($query, $params);
        }
        return $params;
    }

    /**
     * Get the value of the given parameter (or null)
     * @return  mixed|null
     */
    public function getParameter(string $param)
    {
        $params = $this->getParameters();

        return $params[$param] ?? null;
    }

    /**
     * Get URI with modified parameters
     */
    public function withParameters(array $parameters): URI
    {
        return $this->withQuery(
            http_build_query($parameters)
        );
    }

    /**
     * Get URI with modified parameters
     */
    public function withParameter(string $key, $value): URI
    {
        $params = $this->getParameters();
        $params[$key] = $value;
        return $this->withParameters($params);
    }
}
