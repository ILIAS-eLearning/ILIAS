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

namespace ILIAS\StaticURL\Legacy;

/**
 * The three facts about the current user that {@see \ILIAS\StaticURL\Context}
 * needs. Goes away once User is wired through the component bootstrap.
 *
 * @internal
 */
class UserProxy
{
    public function getId(): int
    {
        return $this->user()->getId();
    }

    public function getCurrentLanguage(): string
    {
        return $this->user()->getCurrentLanguage();
    }

    public function isAnonymous(): bool
    {
        return $this->user()->isAnonymous();
    }

    private function user(): \ilObjUser
    {
        global $DIC;
        return $DIC->user();
    }
}
