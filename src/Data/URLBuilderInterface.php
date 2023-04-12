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
 * URLBuilder
 *
 * This provides an abstract representation of an URL and its parameters
 * with the option of changing/removing parameters only by providing a token.
 * These tokens are created when a new parameter is acquired ("claimed") and are therefore
 * controlled by the component that added the parameter. This gives us better control
 * over who gets to change which parameter. Besides that, parameters are always given
 * a namespace so that parameters with the same name can exist beside each other.
 *
 * Along with this class, an equivalent Javascript class is provided in UI/Core that
 * offers the same functionality. The PHP object can be "transferred" to JS in any renderer
 * by using the provided render...() functions to create JS objects/maps.
 */

interface URLBuilderInterface
{
    /**
     * Get the full URL including query string and fragment/hash
     */
    public function getUrl(): string;

    /**
     * Add a new parameter with a namespace
     * and get its token for subsequent changes.
     * If the parameter already exists as an "unclaimed"
     * base parameter, it is converted to a "claimed"
     * parameter and a token is created.
     *
     * The namespace can consists of one or more levels
     * which are noted as an array. They will be joined
     * with the separator (see constant) and used as a
     * prefix for the name, e.g.
     * Namespace: ["ilOrgUnit","filter"]
     * Name: "name"
     * Resulting parameter: "ilOrgUnit_filter_name"
     *
     * @return array<URLBuilder, URLBuilderToken>
     * @throws \ilException
     */
    public function acquireParameter(array $namespace, string $name, ?string $initial_value = null): array;

    /**
     * Delete a parameter if the supplied token is valid
     */
    public function deleteParameter(URLBuilderToken $token): self;

    /**
     * Change a parameter's value if the supplied token is valid
     */
    public function writeParameter(URLBuilderToken $token, string $value): self;

    /**
     * Change the fragment/hash part of the URL
     */
    public function withFragment(?string $fragment): self;

    /**
     * Renders a Javascript Map of all given tokens
     *
     * Note: Only the tokens needed for changing parameters
     * on the JS side should be used here.
     *
     * @param array<URLBuilderToken> $tokens
     */
    public function renderTokens(array $tokens): string;

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
    public function renderObject(array $tokens): string;
}
