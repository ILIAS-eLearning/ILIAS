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

namespace ILIAS\UI\Implementation\Render;

use ILIAS\Refinery\ConstraintViolationException;

/**
 * Information for the UI framework on how to include MathJax
 * It allows the MathJax service to configure
 * - if latex should be rendered generally
 * - how latex rendering can be enabled ior disabled for certain components
 * - which resources are included on the page
 */
interface MathJaxConfig
{
    /**
     * Global enabling of MathJax in ILIAS
     * If this returns false, then
     * - then no resources should be registered by a LatexAwareRenderer
     * - no enabling/disabling CSS classes should be added by a LatexAwareRenderer
     */
    public function isMathJaxEnabled(): bool;

    /**
     * CSS class for the root component to disable a latex rendering by default
     */
    public function getGlobalDisablingClass(): string;

    /**
     * CSS class for a component to enable a latex rendering inside
     */
    public function getPartialDisablingClass(): string;

    /**
     * CSS class for a component to enable a latex rendering inside
     */
    public function getPartialEnablingClass(): string;

    /**
     * Resources that need to be registered for MathJax
     * @return string[] relative paths from the public directory
     */
    public function getResources(): array;

    /**
     * Resources that need to be included in an export
     * @return string[] relative paths from the public directory
     */
    public function getResourcesToExport(): array;
}
