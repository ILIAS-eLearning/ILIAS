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

namespace ILIAS\UI\Implementation\Component\Audio;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * Class Audio
 * @package ILIAS\UI\Implementation\Component\Audio
 */
class Audio implements C\Audio\Audio
{
    use ComponentHelper;
    use JavaScriptBindable;
    use Triggerer;

    private string $src;
    private string $transcript;

    /**
     * @inheritdoc
     */
    public function __construct(string $source, string $transcript)
    {
        $this->checkStringArg("src", $source);
        $this->checkStringArg("transcript", $transcript);

        $this->src = $source;
        $this->transcript = $transcript;
    }

    /**
     * @inheritdoc
     */
    public function withSource(string $source) : \ILIAS\UI\Component\Audio\Audio
    {
        $this->checkStringArg("src", $source);

        $clone = clone $this;
        $clone->src = $source;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getSource() : string
    {
        return $this->src;
    }

    /**
     * @inheritdoc
     */
    public function withTranscription(string $transcript) : \ILIAS\UI\Component\Audio\Audio
    {
        $this->checkStringArg("transcript", $transcript);

        $clone = clone $this;
        $clone->transcript = $transcript;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getTranscription() : string
    {
        return $this->transcript;
    }
}
