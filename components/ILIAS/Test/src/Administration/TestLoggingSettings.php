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

namespace ILIAS\Test\Administration;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\Refinery\Factory as Refinery;

class TestLoggingSettings
{
    public function __construct(
        private bool $logging_enabled = false,
        private bool $ip_logging_enabled = true
    ) {
    }

    /**
     *
     * @return array<ILIAS\UI\Component\Input\Input>
     */
    public function toForm(
        UIFactory $ui_factory,
        Refinery $refinery,
        \ilLanguage $lng
    ): array {
        $trafo = $refinery->custom()->transformation(
            static fn($vs): self => new self($vs['activation'], $vs['ip_logging'])
        );
        return [
            'logging' => $ui_factory->input()->field()->section(
                [
                    'activation' => $ui_factory->input()->field()->checkbox(
                        $lng->txt('activate_logging')
                    )->withValue($this->isLoggingEnabled()),
                    'ip_logging' => $ui_factory->input()->field()->checkbox(
                        $lng->txt('log_ip')
                    )->withByline($lng->txt('log_ip_info'))
                        ->withValue($this->isIPLoggingEnabled())
                ],
                $lng->txt('logging_settings')
            )->withAdditionalTransformation($trafo)
        ];
    }

    public function isLoggingEnabled(): bool
    {
        return $this->logging_enabled;
    }

    public function withLoggingEnabled(bool $logging_enabled): self
    {
        $clone = clone $this;
        $clone->logging_enabled = $logging_enabled;
        return $clone;
    }

    public function isIPLoggingEnabled(): bool
    {
        return $this->ip_logging_enabled;
    }

    public function withIPLoggingEnabled(bool $ip_logging_enabled): self
    {
        $clone = clone $this;
        $clone->ip_logging_enabled = $ip_logging_enabled;
        return $clone;
    }
}
