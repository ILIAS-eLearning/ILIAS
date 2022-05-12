<?php declare(strict_types=1);

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

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
    public function getName() : string;

    /**
     * Get the label of this icon.
     */
    public function getLabel() : string;

    /**
     * Set the label of this icon. Note that this is normally achieved throught constructor and should only be used
     * if there are specific reasons to change the label for a specific context. An example might be to set the label
     * to an empty string in case the symbol is used only decorativly (a11y requirement).
     */
    public function withLabel(string $label) : Icon;

    /**
     * Set the abbreviation for this icon.
     */
    public function withAbbreviation(string $abbreviation) : Icon;

    /**
     * Get the abbreviation of this icon.
     */
    public function getAbbreviation() : ?string;

    /**
     * Set the size for this icon.
     * Size can be 'small', 'medium' or 'large'.
     */
    public function withSize(string $size) : Icon;

    /**
     * Get the size of this icon.
     */
    public function getSize() : string;

    /**
     * Is the Icon disabled?
     */
    public function isDisabled() : bool;

    /**
     * Get an icon like this, but marked as disabled.
     */
    public function withDisabled(bool $is_disabled) : Icon;
}
