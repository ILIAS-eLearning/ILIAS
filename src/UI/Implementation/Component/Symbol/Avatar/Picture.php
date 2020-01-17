<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Symbol\Avatar;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\Symbol\Icon\AbstractAvatar;

class Picture extends AbstractAvatar implements C\Symbol\Avatar\Picture
{

    /**
     * @var string
     */
    private $picture_path;

    public function __construct(string $path_to_picture, string $username)
    {
        $this->checkStringArg('string', $path_to_picture);
        $this->picture_path = $path_to_picture;
        parent::__construct($username);
    }

    public function getPicturePath() : string
    {
        return $this->picture_path;
    }

}
