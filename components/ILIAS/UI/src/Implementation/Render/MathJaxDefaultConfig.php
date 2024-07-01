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
 * Default Mathjax configuration for the UI Framework
 */
class MathJaxDefaultConfig implements MathJaxConfig
{
    public function __construct(
        private readonly bool $mathjax_enabled,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function isMathJaxEnabled(): bool
    {
        return $this->mathjax_enabled;
    }

    /**
     * @inheritdoc
     */
    public function getGlobalDisablingClass(): string
    {
        return 'tex2jax_ignore_global';
    }

    /**
     * @inheritdoc
     */
    public function getPartialDisablingClass(): string
    {
        return 'tex2jax_ignore';
    }

    /**
     * @inheritdoc
     */
    public function getPartialEnablingClass(): string
    {
        return 'tex2jax_process';
    }

    /**
     * @inheritdoc
     */
    public function getResources(): array
    {
        return [
            'assets/js/mathjax.js'
        ];
    }

}
