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
 
namespace ILIAS\UI\Implementation\Component\Player;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * @author Alexander Killing <killing@leifos.de>
 * @package ILIAS\UI\Implementation\Component\Player
 */
class Video extends Player implements C\Player\Video
{
    private string $src = "";
    private string $poster = "";
    private array $subtitle_files = [];

    public function withAdditionalSubtitleFile(string $lang_key, string $subtitle_file) : C\Player\Video
    {
        $clone = clone $this;
        $clone->subtitle_files[$lang_key] = $subtitle_file;
        return $clone;
    }

    public function getSubtitleFiles() : array
    {
        return $this->subtitle_files;
    }

    public function withPoster(string $poster) : C\Player\Video
    {
        $clone = clone $this;
        $clone->poster = $poster;
        return $clone;
    }

    public function getPoster() : string
    {
        return $this->poster;
    }
}
