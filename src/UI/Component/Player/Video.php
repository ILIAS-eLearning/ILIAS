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
 
namespace ILIAS\UI\Component\Player;

use ILIAS\UI\Component\JavaScriptBindable;

/**
 * Interface for Video elements
 * @author Alexander Killing <killing@leifos.de>
 */
interface Video extends Player
{
    /**
     * Set a subtitle file path (vtt file). For WebVTT format, see https://en.wikipedia.org/wiki/WebVTT.
     * @param string $lang_key two letter lang key, e.g. "de", "en"
     * @param string $subtitle_file relative web root path of a vtt file
     */
    public function withAdditionalSubtitleFile(string $lang_key, string $subtitle_file) : \ILIAS\UI\Component\Player\Video;

    /**
     * Get subtitle files
     * @return array<string,string>
     */
    public function getSubtitleFiles() : array;

    /**
     * Set initially shown poster image
     * @param string $poster relative web root path of an image file, URL of an external image resource (png,jpg,svg,gif)
     */
    public function withPoster(string $poster) : \ILIAS\UI\Component\Player\Video;

    public function getPoster() : string;
}
