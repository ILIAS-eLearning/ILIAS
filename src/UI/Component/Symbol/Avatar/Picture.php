<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Symbol\Avatar;

/**
 * This describes how a image avatar could be modified during construction of UI.
 */
interface Picture extends Avatar
{
    public function getPicturePath() : string;
}
