<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\UI\Component\Video;

use ILIAS\UI\Component\JavaScriptBindable;

/**
 * Interface for Video elements
 * @author Alexander Killing <killing@leifos.de>
 */
interface Video extends \ILIAS\UI\Component\Component, JavaScriptBindable
{
    /**
     * Set the source (path) of the video. The complete path to a mp4 video has to be provided.
     */
    public function withSource(string $source) : \ILIAS\UI\Component\Video\Video;

    /**
     * Get the source (path) of the video.
     */
    public function getSource() : string;

    /**
     * Set a subtitle file path. For SubRiP format, see https://en.wikipedia.org/wiki/SubRip
     */
    public function withSubtitleFile(string $srt_file) : \ILIAS\UI\Component\Video\Video;

    /**
     * Get subtitle file
     */
    public function getSubtitleFile() : string;
}
