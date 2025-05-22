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

namespace ILIAS\GlobalScreen\Scope\Footer\Factory;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface isGroup extends isItem, hasTitle
{
    public function getEntries(): array;

    /**
     * @mutable
     */
    public function withEntries(array $entries): isGroup;

    /**
     * @mutable
     */
    public function addEntries(canHaveParent ...$entries): isGroup;

    /**
     * @mutable
     */
    public function addEntry(canHaveParent $entry): isGroup;
}
