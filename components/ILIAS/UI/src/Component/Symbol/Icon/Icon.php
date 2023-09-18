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

namespace ILIAS\UI\Component\Symbol\Icon;

use ILIAS\UI\Component\Symbol\Symbol;

/**
 * This describes how an icon could be modified during construction of UI.
 */
interface Icon extends Symbol
{
    // sizes of icons
    public const SMALL = 'small';
    public const MEDIUM = 'medium';
    public const LARGE = 'large';
    public const RESPONSIVE = 'responsive';

    /**
     * Get the name of the icon.
     * Name will be used as CSS-class, e.g.
     */
    public function getName(): string;

    /**
     * Set the abbreviation for this icon.
     */
    public function withAbbreviation(string $abbreviation): Icon;

    /**
     * Get the abbreviation of this icon.
     */
    public function getAbbreviation(): ?string;

    /**
     * Set the size for this icon.
     * Size can be 'small', 'medium' or 'large'.
     */
    public function withSize(string $size): Icon;

    /**
     * Get the size of this icon.
     */
    public function getSize(): string;

    /**
     * Is the Icon disabled?
     */
    public function isDisabled(): bool;

    /**
     * Get an icon like this, but marked as disabled.
     */
    public function withDisabled(bool $is_disabled): Icon;
}
