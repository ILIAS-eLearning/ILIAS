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
     *        A transcript text SHOULD be provided, if the audio content contains speech.
     * context:
     *   - Listing Items in Panels
     * ----
     * @param string $source
     * @param string $transcript
     * @return \ILIAS\UI\Component\Player\Audio
     */
    public function audio(string $source, string $transcript = "") : Audio;
}
