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

use ILIAS\DI\Container;
use ilMathJaxConfigRespository;

/**
 * Factory for objects used by ilMathJax
 */
class MathJaxFactory
{
    public function __construct(
        private readonly ilMathJaxConfigRespository $repository) {
    }

    /**
     * Get the configuration for the UI framework
     * Currently only the general activation is read from the settings
     *
     * This may change if the script or config can be exchanged by a plugin
     */
    public function uiConfig(): MathJaxUIConfig
    {
        $config = $this->repository->getConfig();

        return new MathJaxUIConfig(
            $config->isClientEnabled(),
            'tex2jax_ignore_global',
            'tex2jax_ignore',
            'tex2jax_process',
            [
                'components/ILIAS/MathJax/config.js',
                'components/ILIAS/MathJax/script.js'
            ]
        );
    }
}
