<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Symbol\Avatar;

interface Letter extends Avatar
{
    public function getAbbreviation() : string;

    public function getBackgroundColorVariant() : int;
}
