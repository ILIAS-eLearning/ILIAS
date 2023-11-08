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

namespace ILIAS\LegalDocuments\ConsumerSlots\SelfRegistration;

use ILIAS\LegalDocuments\ConsumerSlots\SelfRegistration;
use ILIAS\LegalDocuments\ConsumerSlots;
use ilObjUser;
use ilPropertyFormGUI;

final class Bundle implements SelfRegistration
{
    /**
     * @param list<SelfRegistration> $self_registrations
     */
    public function __construct(
        private readonly array $self_registrations
    ) {
    }

    public function legacyInputGUIs(): array
    {
        return array_merge(...$this->callAll(__FUNCTION__));
    }

    public function saveLegacyForm(ilPropertyFormGUI $form): bool
    {
        $and = fn($a, $b) => $a && $b;
        return array_reduce($this->callAll(__FUNCTION__, $form), $and, true);
    }

    public function userCreation(ilObjUser $user): void
    {
        $this->callAll(__FUNCTION__, $user);
    }

    private function callAll(string $call_what, ...$args): array
    {
        return array_map(fn($reg) => $reg->$call_what(...$args), $this->self_registrations);
    }
}
