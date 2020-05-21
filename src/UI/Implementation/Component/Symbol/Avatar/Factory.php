<?php

namespace ILIAS\UI\Implementation\Component\Symbol\Avatar;

use ILIAS\UI\Component\Symbol\Avatar as A;
use ILIAS\UI\Component\Symbol\Avatar\Picture;
use ILIAS\UI\Component\Symbol\Avatar\Letter;

class Factory implements A\Factory
{
    public function picture(string $path_to_user_picture, string $username) : Picture
    {
        return new \ILIAS\UI\Implementation\Component\Symbol\Avatar\Picture($path_to_user_picture, $username);
    }

    public function letter(string $username) : Letter
    {
        return new \ILIAS\UI\Implementation\Component\Symbol\Avatar\Letter($username);
    }
}
