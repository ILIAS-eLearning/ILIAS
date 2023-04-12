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

class URLBuilder implements URLBuilderInterface
{
    /**
     * A maximum length of 2048 characters should be safe to use in
     * most browsers, even though longer URLs will be supported by some
     */
    public const URL_MAX_LENGTH = 2048;
    /**
     * Separator for parts of a parameter's namespace
     * (see URLBuilderInterface::acquireParameter() for more details)
     */
    public const SEPARATOR = '_';
    /**
     * Internal \ILIAS\Data\URI representation of the input URL
     * to use the existing methods for accessing URL parts
     */
    private readonly URI $url;
    /**
     * Preserves the original parameters from the input URL
     * as "unclaimed" parameters. Unless they are "claimed"
     * via acquireParameter(), they remain unchanged and
     * will be added to the final URL.
     */
    private array $base_params;
    /**
     * Stores the URL fragment/hash (#)
     * (always changeable due to its usage)
     */
    private ?string $fragment = null;
    /**
     * Stores all new parameters
     *
     * array<string, string>
     */
    private array $params = [];
    /**
     * Stores all generated tokens
     *
     * array<string, URLBuilderToken>
     */
    private array $tokens = [];

    public function __construct(string $url)
    {
        $this->url = new URI($url);
        $this->base_params = $this->url->getParameters();
        $this->fragment = $this->url->getFragment();
    }

    /**
     * @inheritdoc
     */
    public function getUrl(): string
    {
        return $this->url->getBaseURI() . $this->buildQuery() . $this->buildFragment();
    }

    /**
     * @inheritdoc
     */
    public function getFragment(): ?string
    {
        return $this->fragment;
    }

    /**
     * @inheritdoc
     */
    public function withFragment(?string $fragment): self
    {
        $clone = clone $this;
        $clone->fragment = $fragment;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function acquireParameter(array $namespace, string $name, ?string $initial_value = null): array
    {
        if ($name === '' || sizeof($namespace) === 0) {
            throw new \ilException("Parameter name or namespace not set");
        }

        $parameter = implode(self::SEPARATOR, $namespace) . self::SEPARATOR . $name;
        if ($this->parameterExists($parameter) && ! $this->isBaseParameter($parameter)) {
            throw new \ilException("Parameter '" . $parameter . "' already reserved in URL");
        }

        $previous_value = null;
        if ($this->isBaseParameter($parameter)) {
            $previous_value = $this->base_params[$parameter];
            unset($this->base_params[$parameter]);
        }
        $token = new URLBuilderToken($namespace, $name);
        $clone = clone $this;
        $clone->params[$parameter] = ($initial_value) ?? ($previous_value) ?? '';
        $clone->tokens[$parameter] = $token;
        $clone->checkLength();

        return [
            'url' => $clone,
            'token' => $token
        ];
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function writeParameter(URLBuilderToken $token, string $value): self
    {
        $this->checkToken($token);
        $clone = clone $this;
        $clone->params[$token->getName()] = $value;
        $clone->checkLength();

        return $clone;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function renderObject(array $tokens): string
    {
        $output = 'new il.UI.core.URLBuilder("' . $this->getUrl() . '", ' . $this->renderTokens($tokens) . ')';
        return $output;
    }

    /**
     * Create the query part of the URL from all parameters
     */
    private function buildQuery(): string
    {
        $params = array_merge($this->base_params, $this->params);
        $query = (sizeof($params) !== 0) ? '?' . http_build_query($params) : '';
        return $query;
    }

    /**
     * Create the fragment/hash part of the URL
     */
    private function buildFragment(): string
    {
        $fragment = ($this->fragment) ? '#' . $this->fragment : '';
        return $fragment;
    }

    /**
     * Check if parameter already exists
     */
    private function parameterExists(string $name): bool
    {
        $params = array_merge($this->base_params, $this->params);
        return array_key_exists($name, $params);
    }

    /**
     * Check if parameter is a base parameter
     */
    private function isBaseParameter(string $name): bool
    {
        return array_key_exists($name, $this->base_params);
    }

    /**
     * Check if a token is valid
     *
     * @throws \ilException
     */
    private function checkToken(URLBuilderToken $token): void
    {
        if (! in_array($token, $this->tokens)
        || $this->tokens[$token->getName()]->getToken() !== $token->getToken()) {
            throw new \ilException("Token for '" . $token->getName() . "' is not valid");
        }
        if (! $this->parameterExists($token->getName())) {
            throw new \ilException("Parameter '" . $token->getName() . "' does not exist in URL");
        }
    }

    /**
     * Check the full length of the URL against URL_MAX_LENGTH
     *
     * @throws \ilException
     */
    private function checkLength(): void
    {
        if (! (strlen($this->getUrl()) <= self::URL_MAX_LENGTH)) {
            throw new \ilException("The final URL is longer than " . self::URL_MAX_LENGTH . " and will not be valid.");
        }
    }
}
