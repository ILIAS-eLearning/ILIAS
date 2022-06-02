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

/**
 * @author Alexander Killing <killing@leifos.de>
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: The Audio component is used to play and control an mp3 audio source.
     *   composition: >
     *       The Audio component is composed by the default Player controls. Additionally it optionally
     *       provides a transcript Button that opens a Modal showing the transcription of the audio file.
     * rules:
     *   accessibility:
     *     1: >
     *        A transcript text SHOULD be provided, if the audio content contains speech. This mainly
     *        adresses the "Text Alternatives" accessibility guideline, for details visit:
     *        https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/accessibility.md#guideline-text-alternatives
     * context:
     *   - Listing Items in Panels
     * ----
     * @param string $source relative web root path of an mp3 file or a URL of an external mp3 resource
     * @param string $transcript
     * @return \ILIAS\UI\Component\Player\Audio
     */
    public function audio(string $source, string $transcript = "") : Audio;

    /**
     * ---
     * description:
     *   purpose: The Video component is used to play and control mp4 video files, youtube or vimeo videos.
     *   composition: >
     *       The Video component is composed by a video area, play/pause button, a playtime presentation,
     *       a volume button, a volume slider and a time slider. Additionally it optionally
     *       provides subtitles stored in WebVTT files, see https://en.wikipedia.org/wiki/WebVTT.
     * rules:
     *   accessibility:
     *     1: >
     *        A subtitle file SHOULD be provided, if the video content contains speech.
     *   style:
     *     1: >
     *        The widget will be presented with the full width of its container.
     * context:
     *   - Main Content
     *   - Modal Content
     * ----
     * @param string $source relative web root path of an mp4 file, URL of an external mp4 resource,
     *                       youtube or vimeo URL
     * @return \ILIAS\UI\Component\Player\Video
     */
    public function video(string $source) : Video;
}
