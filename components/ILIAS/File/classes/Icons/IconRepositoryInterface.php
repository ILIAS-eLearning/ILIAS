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

namespace ILIAS\File\Icon;

/**
 * @author Lukas Zehnder <lukas@sr.solutions>
 */
interface IconRepositoryInterface
{
    public function createIcon(string $a_rid, bool $a_active, bool $a_is_default_icon, array $a_suffixes): Icon;

    public function getIcons(): array;

    public function getIconsForFilter(array $filter): array;

    public function getIconByRid(string $a_rid): Icon;

    public function getActiveIconForSuffix(string $a_suffix): Icon;

    public function getIconFilePathBySuffix(string $suffix): string;

    public function updateIcon(string $a_rid, bool $a_active, bool $a_is_default_icon, array $a_suffixes): Icon;

    public function deleteIconByRid(string $a_rid): bool;
}
