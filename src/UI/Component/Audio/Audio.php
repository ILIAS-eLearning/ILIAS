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

namespace ILIAS\UI\Component\Audio;

use ILIAS\UI\Component\JavaScriptBindable;

/**
 * Interface for Audio elements
 *
 * @author Alexander Killing <killing@leifos.de>
 * @package ILIAS\UI\Component\Audio
 */
interface Audio extends \ILIAS\UI\Component\Component, JavaScriptBindable
{
    /**
     * Set the source (path) of the audio file. The complete path to the audio file has to be provided.
     */
    public function withSource(string $source) : \ILIAS\UI\Component\Audio\Audio;

    /**
     * Get the source (path) of the audio.
     */
    public function getSource() : string;

    /**
     * Set the transcription
     */
    public function withTranscription(string $transcription) : \ILIAS\UI\Component\Audio\Audio;

    /**
     * Get the transcription
     */
    public function getTranscription() : string;
}
