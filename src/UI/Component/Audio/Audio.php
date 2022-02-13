<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\UI\Component\Audio;

use ILIAS\UI\Component\JavaScriptBindable;

/**
 * Interface for Audio elements
 *
 * @author Alexander Killing <killing@leifos.de>
 * @package ILIAS\UI\Component\Image
 */
interface Audio extends \ILIAS\UI\Component\Component, JavaScriptBindable
{
    /**
     * Set the source (path) of the audio. The complete path to the audio has to be provided.
     * @param string $source
     * @return \ILIAS\UI\Component\Audio\Audio
     */
    public function withSource($source);

    /**
     * Get the source (path) of the audio.
     * @return string
     */
    public function getSource();

    /**
     * Set the source (path) of the audio. The complete path to the audio has to be provided.
     * @param string $transcription
     * @return \ILIAS\UI\Component\Audio\Audio
     */
    public function withTranscription($transcription);

    /**
     * Get the transcription
     * @return string
     */
    public function getTranscription();
}
