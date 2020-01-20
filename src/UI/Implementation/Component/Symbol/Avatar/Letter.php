<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Symbol\Avatar;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\Symbol\Icon\AbstractAvatar;

class Letter extends AbstractAvatar implements C\Symbol\Avatar\Letter
{

    public function getAbbreviation() : string
    {
        return (substr($this->getUsername(), 0, 2));
    }

    public function getBackgroundColorVariant() : int
    {
        $plit = str_split($this->getAbbreviation());


        return (ord($plit[0]) + ord($plit[1])) % 26;
    }

}
