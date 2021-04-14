<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Symbol\Avatar;

/**
 * This describes how a letter or a picture avatar could be modified during construction of UI.
 */
interface Avatar extends \ILIAS\UI\Component\Symbol\Symbol
{
    public function getUsername() : string;

    public function withAlternativeText(string $text) : Avatar;

    public function getAlternativeText() : string;
}
