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

namespace ILIAS\UI;

use ILIAS\Data\URI;

/**
 * URLBuilder
 *
 * This provides an abstract representation of an URL and its parameters
 * with the option of changing/removing parameters only by providing a token.
 * These tokens are created when a new parameter is acquired and are therefore
 * controlled by the component that added the parameter. This gives us better control
 * over who gets to change which parameter. Besides that, parameters are always given
 * a namespace so that parameters with the same name can exist beside each other.
 * The in- and output of the URLBuilder are \ILIAS\Data\URI objects.
 *
 * Along with this class, an equivalent Javascript class is provided in UI/Core that
 * offers a similar functionality. The PHP object can be "transferred" to JS in any renderer
 * by using the provided render...() functions to create JS objects/maps.
 */
class URLBuilder
{
    /**
     * A maximum length of 2048 characters should be safe to use in
     * most browsers, even though longer URLs will be supported by some
     */
    public const URL_MAX_LENGTH = 2048;
    /**
     * Separator for parts of a parameter's namespace
     */
    public const SEPARATOR = '_';
    /**
     * Base URI for the URLBuilder
     */
    private URI $uri;
    /**
     * Stores the URL fragment/hash (#)
     * (always changeable due to its usage)
     */
    private ?string $fragment = null;
    /**
     * Stores all acquired parameters
     * These always take precedence over existing parameters in the base URI
     *
     * array<string, string>
     */
    private array $params = [];
    /**
     * Stores all generated tokens for acquired parameters
     *
     * array<string, URLBuilderToken>
     */
    private array $tokens = [];

    public function __construct(URI $uri)
    {
        $this->uri = $uri;
    }

    /**
     * Changes the base URI of the Builder
     */
    public function withURI(URI $uri): self
    {
        $clone = clone $this;
        $clone->uri = $uri;
        return $clone;
    }

    /**
     * Change the fragment/hash part of the URL
     */
    public function withFragment(?string $fragment): self
    {
        $clone = clone $this;
        $clone->fragment = $fragment;
        return $clone;
    }

    /**
     * Add a new parameter with a namespace
     * and get its token for subsequent changes.
     *
     * The namespace can consists of one or more levels
     * which are noted as an array. They will be joined
     * with the separator (see constant) and used as a
     * prefix for the name, e.g.
     * Namespace: ["ilOrgUnit","filter"]
     * Name: "name"
     * Resulting parameter: "ilOrgUnit_filter_name"
     *
     * The return value is an array containing both the
     * changed URLBuilder as well as the token for any
     * subsequent changes to the acquired parameter.
     *
     * @return array<URLBuilder,URLBuilderToken>
     * @throws \ilException
     * @throws \InvalidArgumentException
     */
    public function acquireParameter(array $namespace, string $name, ?string $initial_value = null): array
    {
        if ($name === '' || empty($namespace)) {
            throw new \InvalidArgumentException("Parameter name or namespace not set");
        }

        $parameter = implode(self::SEPARATOR, $namespace) . self::SEPARATOR . $name;
        if ($this->parameterExists($parameter)) {
            throw new \ilException("Parameter '" . $parameter . "' already reserved in URL");
        }

        $token = new URLBuilderToken($namespace, $name);
        $clone = clone $this;
        $clone->params[$parameter] = ($initial_value) ?? '';
        $clone->tokens[$parameter] = $token;

        return [$clone, $token];
    }

    public function acquireParameters(array $namespace, string ...$names): array
    {
        $tokens = [];
        $builder = $this;
        foreach ($names as $name) {
            list($builder, $token) = $builder->acquireParameter($namespace, $name);
            $tokens[] = $token;
        }
        array_unshift($tokens, $builder);
        return $tokens;
    }

    /**
     * Delete an acquired parameter if the supplied token is valid
     */
    public function deleteParameter(URLBuilderToken $token): self
    {
        $this->checkToken($token);
        $clone = clone $this;
        unset($clone->params[$token->getName()]);
        unset($clone->tokens[$token->getName()]);

        return $clone;
    }

    /**
     * Change an acquired parameter's value if the supplied token is valid
     */
    public function withParameter(URLBuilderToken $token, $value): self
    {
        if(! is_string($value) && ! is_array($value)) {
            throw new \InvalidArgumentException('Parameter must be of type string or array');
        }
        $this->checkToken($token);
        $clone = clone $this;
        $clone->params[$token->getName()] = $value;

        return $clone;
    }

    /**
     * Renders a Javascript Map of all given tokens
     *
     * Note: Only the tokens needed for changing parameters
     * on the JS side should be used here.
     *
     * @param array<URLBuilderToken> $tokens
     */
    public function renderTokens(array $tokens): string
    {
        $token_render = [];
        foreach ($tokens as $token) {
            $token_render[] = '["' . $token->getName() . '",' . $token->render() . ']';
        }
        $output = 'new Map([' . implode(',', $token_render) . '])';
        return $output;
    }

    /**
     * Renders a Javascript URLBuilder object with
     * changeable parameters for all given tokens.
     *
     * Note: By providing only the tokens that need to be
     * changed on the JS side, all other parameters will
     * be passed as unchangeable.
     *
     * @param array<URLBuilderToken> $tokens
     */
    public function renderObject(array $tokens): string
    {
        $output = 'new il.UI.core.URLBuilder(new URL("' . $this->buildURI() . '"), ' . $this->renderTokens($tokens) . ')';
        return $output;
    }

    /**
     * Get a URI representation of the full URL including query string and fragment/hash
     */
    public function buildURI(): URI
    {
        $uri = new URI($this->uri->getBaseURI() . $this->buildQuery() . $this->buildFragment());
        $this->checkLength($uri);
        return $uri;
    }

    /**
     * Create the query part of the URL from all parameters
     * Claimed parameters overwrite base parameters in array_merge(),
     * numeric indizes of array-parameters are being removed to ensure
     * continous numeration (p[1]=A&p[2]=B --> p[]=A&p[]=B).
     */
    private function buildQuery(): string
    {
        $params = array_merge($this->uri->getParameters(), $this->params);
        $query = (! empty($params)) ? '?' . http_build_query($params) : '';
        $query = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $query);
        return $query;
    }

    /**
     * Create the fragment/hash part of the URL
     */
    private function buildFragment(): string
    {
        if ($this->fragment !== null) {
            return ($this->fragment !== '') ? '#' . $this->fragment : '';
        }
        $fragment = ($this->uri->getFragment()) ? '#' . $this->uri->getFragment() : '';
        return $fragment;
    }

    /**
     * Check if parameter was already acquired
     */
    private function parameterExists(string $name): bool
    {
        return array_key_exists($name, $this->params);
    }

    /**
     * Check if a token is valid
     *
     * @throws \DomainException
     * @throws \ilException
     */
    private function checkToken(URLBuilderToken $token): void
    {
        if (! in_array($token, $this->tokens)
        || $this->tokens[$token->getName()]->getToken() !== $token->getToken()) {
            throw new \DomainException("Token for '" . $token->getName() . "' is not valid");
        }
        if (! $this->parameterExists($token->getName())) {
            throw new \ilException("Parameter '" . $token->getName() . "' does not exist in URL");
        }
    }

    /**
     * Check the full length of the URI against URL_MAX_LENGTH
     *
     * @throws \LengthException
     */
    private function checkLength(URI $uri): void
    {
        if (! (strlen((string) $uri) <= self::URL_MAX_LENGTH)) {
            throw new \LengthException("The final URL is longer than " . self::URL_MAX_LENGTH . " and will not be valid.");
        }
    }
}
