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

namespace ILIAS\Search\Presentation\Result\User;

use ILIAS\Data\URI;

interface Properties
{
    public function userID(): int;

    public function presentableName(): string;

    public function login(): string;

    public function linkToProfile(): ?URI;

    public function avatarPath(): string;

    /**
     * As they should appear as properties in the search result.
     * @return array<string, string>
     */
    public function otherFields(): array;
}
