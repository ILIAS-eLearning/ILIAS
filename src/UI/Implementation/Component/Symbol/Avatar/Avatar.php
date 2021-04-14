<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Symbol\Avatar;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

abstract class Avatar implements C\Symbol\Avatar\Avatar
{
    use ComponentHelper;
    use JavaScriptBindable;
    /**
     * @var    string
     */
    private $username;

    protected $alternative_text = "";

    public function __construct(string $username)
    {
        $this->username = $username;
    }

    /**
     * @inheritdoc
     */
    public function getUsername() : string
    {
        return $this->username;
    }

    public function withAlternativeText(string $text) : C\Symbol\Avatar\Avatar
    {
        $clone = clone $this;
        $clone->alternative_text = $text;
        return $clone;
    }

    public function getAlternativeText() : string
    {
        return $this->alternative_text;
    }
}
