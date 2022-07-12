<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Implementation\Component\Symbol\Avatar;

use ILIAS\UI\Component as C;

class Picture extends Avatar implements C\Symbol\Avatar\Picture
{
    private string $picture_path;

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
