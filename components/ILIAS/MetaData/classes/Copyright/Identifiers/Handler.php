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

namespace ILIAS\MetaData\Copyright\Identifiers;

class Handler implements HandlerInterface
{
    public function buildIdentifierFromEntryID(int $entry_id): string
    {
        return 'il_copyright_entry__' . $this->getInstID() . '__' . $entry_id;
    }

    public function isIdentifierValid(string $identifier): bool
    {
        if (!preg_match('/il_copyright_entry__([0-9]+)__([0-9]+)/', $identifier, $matches)) {
            return false;
        }
        if (($matches[1] ?? '') !== $this->getInstID()) {
            return false;
        }
        return true;
    }

    public function parseEntryIDFromIdentifier(string $identifier): int
    {
        if (!preg_match('/il_copyright_entry__([0-9]+)__([0-9]+)/', $identifier, $matches)) {
            return 0;
        }
        if (($matches[1] ?? '') !== $this->getInstID()) {
            return 0;
        }
        return (int) ($matches[2] ?? 0);
    }

    protected function getInstID(): string
    {
        return (string) IL_INST_ID;
    }
}
