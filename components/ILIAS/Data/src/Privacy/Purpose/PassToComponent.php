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

namespace ILIAS\Data\Privacy\Purpose;

/**
 * The value is handed over to another component.
 *
 * Note: prefer passing the unresolved {@see \ILIAS\Data\Privacy\PrivacyDataType}
 * itself — this purpose is only for boundaries that require the raw value.
 */
final readonly class PassToComponent implements Purpose
{
    /**
     * @param string $component e.g. "Mail", "Notifications"
     * @param string $reason    e.g. "signature", "send_notification"
     */
    public function __construct(
        private string $component,
        private string $reason,
    ) {
    }

    public function getComponent(): string
    {
        return $this->component;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function describe(): string
    {
        return "pass_to:{$this->component} ({$this->reason})";
    }
}
