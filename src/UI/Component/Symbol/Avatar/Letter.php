<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Symbol\Avatar;

/**
 * This describes how a letter avatar could be modified during construction of UI.
 */
interface Letter extends Avatar
{
    public function getAbbreviation() : string;

    public function getBackgroundColorVariant() : int;
}
