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

use PHPUnit\Framework\TestCase;
use ILIAS\MathJax\MathJaxFactory;

/**
 * Base class for al tests
 */
abstract class ilMathJaxBaseTest extends TestCase
{
    /**
     * Get a config without active settings
     */
    protected function getEmptyConfig(): ilMathJaxConfig
    {
        return new ilMathJaxConfig(
            false
        );
    }

    /**
     * Get a factory mockup that will deliver other mockups
     */
    protected function getFactoryMock(?string $imagefile = null): MathJaxFactory
    {
        $factory = $this
            ->getMockBuilder(MathJaxFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['uiConfig'])
            ->getMock();
        $factory->method('uiConfig')->willReturn(
            new \ILIAS\MathJax\MathJaxUIConfig(
                true,
                'tex2jax_ignore_global',
                'tex2jax_ignore',
                'tex2jax_process',
                [
                    'components/ILIAS/MathJax/config.js',
                    'components/ILIAS/MathJax/script.js'
                ]
            )
        );
        return $factory;
    }

}
