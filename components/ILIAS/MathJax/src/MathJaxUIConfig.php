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

namespace ILIAS\MathJax;

use ILIAS\Refinery\ConstraintViolationException;

/**
 * Data class to provide information for the UI framework on how to include mathjax
 * This is the only dependency between the ui framework and the mathjax service
 * It allows the MathJax service to configure:
 * - if latex should be rendered generally
 * - how latex rendering can be enabled ir disabled for certain components
 * - which resources are included on the page
 */
class MathJaxUIConfig
{
    public function __Construct(
        private readonly bool $mathjax_enabled,
        private readonly string $global_disabling_class,
        private readonly string $partial_disabling_class,
        private readonly string $partial_enabling_class,
        private readonly array $resources
    ) {
        foreach ([$this->global_disabling_class, $this->partial_disabling_class, $this->partial_enabling_class] as $value) {
            $matches = null;
            if (!preg_match('/^[a-zA-Z0-9_]+$/', (string) $value, $matches)) {
                throw new ConstraintViolationException(
                    sprintf('The value "%s" is not an allowed class for mathjax.', $value),
                    'exception_no_mathjax_class',
                    array($value)
                );
            }
        }
    }

    /**
     * Global enabling of MathJax in ILIAS
     * If false, then no resources should be registered and no enabling/disabling classes should be added
     */
    public function isMathJaxEnabled(): bool
    {
        return $this->mathjax_enabled;
    }

    /**
     * CSS class for the root component to disable a latex rendering by default
     */
    public function getGlobalDisablingClass(): string
    {
        return $this->global_disabling_class;
    }

    /**
     * CSS class for a component to enable a latex rendering inside
     */
    public function getPartialDisablingClass(): string
    {
        return $this->partial_disabling_class;
    }

    /**
     * CSS class for a component to enable a latex rendering inside
     */
    public function getPartialEnablingClass(): string
    {
        return $this->partial_enabling_class;
    }

    /**
     * Resources that need to be registered for MathJax
     * @return string[] relative paths from the public directory
     */
    public function getResources(): array
    {
        return $this->resources;
    }

}
