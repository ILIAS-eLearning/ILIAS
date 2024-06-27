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

/**
 * Global Mathjax configuration
 */
class ilMathJaxConfig
{
    protected bool $client_enabled;

    /**
     * Constructor
     */
    public function __construct(
        bool $client_enabled
    ) {
        $this->client_enabled = $client_enabled;
    }

    /**
     * Should latex code be rendered in the browser
     */
    public function isClientEnabled(): bool
    {
        return $this->client_enabled;
    }

    /**
     * Enable latex code bing rendered in the browser
     */
    public function withClientEnabled(bool $client_enabled): ilMathJaxConfig
    {
        $clone = clone $this;
        $clone->client_enabled = $client_enabled;
        return $clone;
    }
}
