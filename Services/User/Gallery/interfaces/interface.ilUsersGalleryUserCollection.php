<?php

declare(strict_types=1);

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
interface ilUsersGalleryUserCollection extends Iterator, Countable
{
    public function setHighlighted(bool $status): void;

    public function isHighlighted(): bool;

    public function getLabel(): string;

    public function setLabel(string $label): void;

    /**
     * @param ilUsersGalleryUser[] $items
     */
    public function setItems(array $items): void;

    /**
     * @return ilUsersGalleryUser[]
     */
    public function getItems(): array;
}
