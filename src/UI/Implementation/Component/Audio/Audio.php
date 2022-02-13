<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

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

    /**
     * @var	string
     */
    private $src;

    /**
     * @var	string
     */
    private $transcript;

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
    public function withSource($source)
    {
        $this->checkStringArg("src", $source);

        $clone = clone $this;
        $clone->src = $source;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getSource()
    {
        return $this->src;
    }

    /**
     * @inheritdoc
     */
    public function withTranscription($transcript)
    {
        $this->checkStringArg("transcript", $transcript);

        $clone = clone $this;
        $clone->transcript = $transcript;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getTranscription()
    {
        return $this->transcript;
    }
}
