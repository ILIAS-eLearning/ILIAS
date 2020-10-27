<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Symbol\Avatar;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\Symbol\Avatar\Avatar;

class Picture extends Avatar implements C\Symbol\Avatar\Picture
{
    private $picture_path;

    public function __construct(string $path_to_picture, string $username)
    {
        $this->picture_path = $path_to_picture;
        parent::__construct($username);
    }

    public function getPicturePath() : string
    {
        return $this->picture_path;
    }
}
