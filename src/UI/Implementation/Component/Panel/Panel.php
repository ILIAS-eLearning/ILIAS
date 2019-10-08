<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Panel
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Panel implements C\Panel\Panel
{
    use ComponentHelper;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var \ILIAS\UI\Component\Component[] | \ILIAS\UI\Component\Component
     */
    private $content;

    /**
     * @var \ILIAS\UI\Component\Dropdown\Standard | null
     */
    protected $actions = null;

    /**
     * @param string $title
     * @param \ILIAS\UI\Component\Component[] | \ILIAS\UI\Component\Component $content
     */
    public function __construct($title, $content)
    {
        $this->checkStringArg("title", $title);
        $content = $this->toArray($content);
        $types = [C\Component::class];
        $this->checkArgListElements("content", $content, $types);

        $this->title = $title;
        $this->content = $content;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function withActions(\ILIAS\UI\Component\Dropdown\Standard $actions)
    {
        $clone = clone $this;
        $clone->actions = $actions;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getActions()
    {
        return $this->actions;
    }
}
